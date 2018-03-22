<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Comment;

use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;
use XFMG\Entity\Comment;
use XFMG\Entity\MediaItem;
use XFMG\Entity\Album;
use XFMG\Entity\Category;
use XF\Entity\User;

class AuthorChanger extends AbstractService
{
    use ValidateAndSavableTrait;

    /**
     * @var Comment
     */
    protected $comment;

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
     * @param \XF\App $app
     * @param Comment $comment
     * @param User $newAuthor
     */
    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, Comment $comment, User $newAuthor)
    {
        parent::__construct($app);

        if ($comment->Media)
        {
            $this->mediaItem = $comment->Media;
            $this->category = $comment->Media->Category;
            $this->album = $comment->Media->Album;
        }

        $this->comment = $comment;
        $this->oldAuthor = $comment->User;
        $this->newAuthor = $newAuthor;
    }

    public function setPerformValidations($perform)
    {
        $this->performValidations = (bool)$perform;
    }

    /**
     * @return bool
     */
    public function getPerformValidations()
    {
        return $this->performValidations;
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

    /**
     * @return MediaItem
     */
    public function getMediaItem()
    {
        return $this->mediaItem;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return User
     */
    public function getNewAuthor()
    {
        return $this->newAuthor;
    }

    /**
     * @return User
     */
    public function getOldAuthor()
    {
        return $this->oldAuthor;
    }

    public function changeAuthor()
    {
        $newAuthor = $this->getNewAuthor();
        $comment = $this->getComment();

        $comment->user_id = $newAuthor->user_id;
        $comment->username = $newAuthor->username;

        if ($mediaItem = $this->getMediaItem())
        {
            if ($mediaItem->last_comment_id === $comment->comment_id)
            {
                $mediaItem->last_comment_user_id = $comment->user_id;
                $mediaItem->last_comment_username = $comment->username ?: '-';
            }
        }

        if ($album = $this->getAlbum())
        {
            if ($album->last_comment_id === $comment->comment_id)
            {
                $album->last_comment_username = $comment->user_id;
                $album->last_comment_username = $comment->username ?: '-';
            }
        }
    }

    protected function finalSetup()
    {
    }

    protected function _validate()
    {
        $this->finalSetup();

        $newAuthor = $this->getNewAuthor();
        $comment = $this->getComment();
        $commentErrors = $comment->getErrors();
        $albumErrors = [];
        $mediaItemErrors = [];

        if ($mediaItem = $this->getMediaItem())
        {
            $mediaItem->preSave();
            $mediaItemErrors = $mediaItem->getErrors();
        }

        if ($album = $this->getAlbum())
        {
            $album->preSave();
            $albumErrors = $album->getErrors();
        }

        $errors = array_merge($albumErrors, $mediaItemErrors, $commentErrors);

        if ($this->performValidations)
        {
            $canTargetView = \XF::asVisitor($newAuthor, function() use ($comment)
            {
                return $comment->canView();
            });

            if (!$canTargetView)
            {
                $errors[] = \XF::phraseDeferred('changeContentOwner_new_author_must_be_able_to_view_this_xfmg_comment');
            }
        }

        return $errors;
    }

    protected function _save()
    {
        $comment = $this->getComment();
        $mediaItem = $this->getMediaItem();
        $album = $this->getAlbum();

        $db = $this->db();
        $db->beginTransaction();

        $comment->save();

        if ($mediaItem)
        {
            $mediaItem->save();
        }

        if ($album)
        {
            $album->save();
        }

        if ($comment->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('xfmg_comment', $comment, 'author_change');
        }

        $db->commit();

        return $comment;
    }
}