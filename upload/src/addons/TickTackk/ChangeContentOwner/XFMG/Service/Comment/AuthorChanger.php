<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Comment;

use \XFMG\Entity\Comment;
use \XFMG\Entity\MediaItem;
use \XFMG\Entity\Album;
use \XFMG\Entity\Category;
use XF\Entity\User;

class AuthorChanger extends \XF\Service\AbstractService
{
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

    protected $performValidations = true;

    public function __construct(\XF\App $app, Comment $comment, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);

        if ($comment->Media)
        {
            $this->mediaItem = $comment->Media;
            $this->category = $comment->Media->Category;
            $this->album = $comment->Media->Album;
        }

        $this->comment = $comment;
        $this->oldAuthor = $oldAuthor;
        $this->newAuthor = $newAuthor;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getAlbum()
    {
        return $this->album;
    }

    public function getMediaItem()
    {
        return $this->mediaItem;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getNewAuthor()
    {
        return $this->newAuthor;
    }

    public function getOldAuthor()
    {
        return $this->oldAuthor;
    }

    public function changeAuthor()
    {
        $newAuthor = $this->getNewAuthor();
        $comment = $this->getComment();
        $mediaItem = $this->getMediaItem();
        $album = $this->getAlbum();

        $db = $this->db();
        $db->beginTransaction();

        $comment->user_id = $newAuthor->user_id;
        $comment->username = $newAuthor->username;

        if (!$comment->preSave())
        {
            throw new \XF\PrintableException($comment->getErrors());
        }
        $comment->save();

        if ($mediaItem)
        {
            $mediaItem->rebuildCounters();
            if (!$mediaItem->preSave())
            {
                throw new \XF\PrintableException($mediaItem->getErrors());
            }
            $mediaItem->save();
        }

        if ($album)
        {
            $album->rebuildLastCommentInfo();
            if (!$album->preSave())
            {
                throw new \XF\PrintableException($album->getErrors());
            }
            $album->save();
        }

        $db->commit();
    }
}