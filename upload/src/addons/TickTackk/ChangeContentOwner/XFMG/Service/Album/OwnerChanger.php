<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Album;

use TickTackk\ChangeContentOwner\Service\ContentTrait;
use XF\Entity\User;
use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;
use XFMG\Entity\Album;
use XFMG\Entity\Category;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner
 */
class OwnerChanger extends AbstractService
{
    use ValidateAndSavableTrait, ContentTrait;

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
     * @var array
     */
    protected $oldAuthorAlt;

    /**
     * @var bool
     */
    protected $performValidations = true;

    /**
     * OwnerChanger constructor.
     *
     * @param \XF\App $app
     * @param Album   $album
     * @param User    $newAuthor
     */
    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, Album $album, User $newAuthor)
    {
        parent::__construct($app);
        $this->category = $album->Category;
        $this->album = $album;
        $this->oldAuthor = $album->User;
        $this->oldAuthorAlt = $this->oldAuthor ? $this->oldAuthor : [
            'user_id' => $album->user_id,
            'username' => $album->username
        ];
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

    public function changeOwner()
    {
        $newAuthor = $this->getNewAuthor();
        $album = $this->getAlbum();

        $album->user_id = $newAuthor->user_id;
        $album->username = $newAuthor->username;
    }

    /**
     * @return User
     */
    public function getNewAuthor()
    {
        return $this->newAuthor;
    }

    /**
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function _validate()
    {
        $this->finalSetup();

        $newAuthor = $this->getNewAuthor();
        $album = $this->getAlbum();
        $album->preSave();
        $errors = $album->getErrors();

        if ($this->performValidations)
        {
            $canTargetView = \XF::asVisitor($newAuthor, function () use ($album)
            {
                return $album->canView();
            });

            if (!$canTargetView)
            {
                $errors[] = \XF::phraseDeferred('changeContentOwner_new_author_must_be_able_to_view_this_xfmg_album');
            }
        }

        return $errors;
    }

    protected function finalSetup()
    {
    }

    /**
     * @return Album
     * @throws \XF\PrintableException
     */
    protected function _save()
    {
        $oldAuthor = $this->getOldAuthor();
        $newAuthor = $this->getNewAuthor();

        $album = $this->getAlbum();

        $db = $this->db();
        $db->beginTransaction();

        $album->save();

        if (\XF::$versionId >= 2010010)
        {
            /** @noinspection PhpUndefinedFieldInspection */
            if ($reactionContent = $album->Reactions[$newAuthor->user_id])
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $reactionContent->delete();
            }
        }
        else if ($likedContent = $album->Likes[$newAuthor->user_id])
        {
            $likedContent->delete();
        }

        if ($album->isVisible())
        {
            if ($oldAuthor)
            {
                $this->adjustUserAlbumCountIfNeeded($oldAuthor, -1);
            }

            $this->adjustUserAlbumCountIfNeeded($newAuthor, -1);
        }

        if ($album->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('xfmg_album', $album, 'owner_change');
        }

        $db->commit();

        return $album;
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
    protected function adjustUserAlbumCountIfNeeded(User $user, $amount)
    {
        $this->db()->query('
            UPDATE xf_user
            SET xfmg_album_count = GREATEST(0, xfmg_album_count + ?)
            WHERE user_id = ?
        ', [$amount, $user->user_id]);
    }
}