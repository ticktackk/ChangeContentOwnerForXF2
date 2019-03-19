<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\MediaItem;

use TickTackk\ChangeContentOwner\Service\ContentTrait;
use XF\Entity\User;
use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;
use XFMG\Entity\Album;
use XFMG\Entity\Category;
use XFMG\Entity\MediaItem;

/**
 * Class AuthorChanger
 *
 * @package TickTackk\ChangeContentOwner
 */
class AuthorChanger extends AbstractService
{
    use ValidateAndSavableTrait, ContentTrait;

    /**
     * @var MediaItem
     */
    protected $mediaItem;

    /**
     * @var Album
     */
    protected $album;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var User
     */
    protected $newAuthor;

    /**
     * @var User
     */
    protected $oldAuthor;

    /**
     * @var bool
     */
    protected $performValidations = true;

    /**
     * AuthorChanger constructor.
     *
     * @param \XF\App   $app
     * @param MediaItem $mediaItem
     * @param User      $newAuthor
     */
    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, MediaItem $mediaItem, User $newAuthor)
    {
        parent::__construct($app);
        $this->mediaItem = $mediaItem;
        $this->category = $mediaItem->Category;
        $this->album = $mediaItem->Album;
        $this->oldAuthor = $mediaItem->User;
        $this->newAuthor = $newAuthor;
    }

    /**
     * @return bool
     */
    public function getPerformValidations()
    {
        return $this->performValidations;
    }

    /**
     * @param $perform
     */
    public function setPerformValidations($perform)
    {
        $this->performValidations = (bool)$perform;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    public function changeAuthor()
    {
        $newAuthor = $this->getNewAuthor();
        $mediaItem = $this->getMediaItem();

        $mediaItem->user_id = $newAuthor->user_id;
        $mediaItem->username = $newAuthor->username;
    }

    /**
     * @return User
     */
    public function getNewAuthor()
    {
        return $this->newAuthor;
    }

    /**
     * @return MediaItem
     */
    public function getMediaItem()
    {
        return $this->mediaItem;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function _validate()
    {
        $this->finalSetup();

        $newAuthor = $this->getNewAuthor();
        $mediaItem = $this->getMediaItem();
        $errors = $mediaItem->getErrors();

        if ($this->performValidations)
        {
            $canTargetView = \XF::asVisitor($newAuthor, function () use ($mediaItem)
            {
                return $mediaItem->canView();
            });

            if (!$canTargetView)
            {
                $errors[] = \XF::phraseDeferred('changeContentOwner_new_author_must_be_able_to_view_this_xfmg_media');
            }
        }

        return $errors;
    }

    protected function finalSetup()
    {
    }

    /**
     * @return MediaItem
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function _save()
    {
        $oldAuthor = $this->getOldAuthor();
        $newAuthor = $this->getNewAuthor();
        $mediaItem = $this->getMediaItem();

        $db = $this->db();
        $db->beginTransaction();

        $mediaItem->save();

        if (\XF::$versionId >= 2010010)
        {
            /** @noinspection PhpUndefinedFieldInspection */
            if ($reactionContent = $mediaItem->Reactions[$newAuthor->user_id])
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $reactionContent->delete();
            }
        }
        else if ($likedContent = $mediaItem->Likes[$newAuthor->user_id])
        {
            $likedContent->delete();
        }

        if ($mediaItem->isVisible())
        {
            if ($oldAuthor)
            {
                $this->adjustUserMediaCountIfNeeded($oldAuthor, -1);
                $this->adjustUserMediaQuotaIfNeeded($mediaItem, $oldAuthor, true);
            }

            $this->adjustUserMediaCountIfNeeded($newAuthor, -1);
            $this->adjustUserMediaQuotaIfNeeded($mediaItem, $newAuthor);

            $this->updateNewsFeed($mediaItem, $oldAuthor, $newAuthor);
        }

        if ($mediaItem->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('xfmg_media', $mediaItem, 'author_change');
        }

        $db->commit();

        return $mediaItem;
    }

    /**
     * @return User
     */
    public function getOldAuthor()
    {
        return $this->oldAuthor;
    }

    /**
     * @param User $user
     * @param int $amount
     *
     * @throws \XF\Db\Exception
     */
    protected function adjustUserMediaCountIfNeeded(User $user, $amount)
    {
        $this->db()->query('
            UPDATE xf_user
            SET xfmg_media_count = GREATEST(0, xfmg_media_count + ?)
            WHERE user_id = ?
        ', [$amount, $user->user_id]);
    }

    /**
     * @param MediaItem $mediaItem
     * @param User      $user
     * @param bool      $delete
     *
     * @throws \Exception
     *
     * @throws \XF\PrintableException
     */
    protected function adjustUserMediaQuotaIfNeeded(MediaItem $mediaItem, User $user, $delete = false)
    {
        if ($mediaItem->Attachment && $user)
        {
            $fileSize = $mediaItem->Attachment->getFileSize();
            /** @noinspection PhpUndefinedFieldInspection */
            $existing = $user->xfmg_media_quota;

            if ($delete)
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $user->xfmg_media_quota = ($existing - $fileSize) / 1024;
            }
            else
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $user->xfmg_media_quota = ($existing + $fileSize) / 1024;
            }

            $user->save();
        }
    }
}