<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Album;

use \XFMG\Entity\Album;
use \XFMG\Entity\Category;
use XF\Entity\User;

class AuthorChanger extends \XF\Service\AbstractService
{
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

    public function __construct(\XF\App $app, Album $album, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);
        $this->category = $album->Category;
        $this->album = $album;
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
        $oldAuthor = $this->getOldAuthor();
        $newAuthor = $this->getNewAuthor();

        $album = $this->getAlbum();

        $db = $this->db();
        $db->beginTransaction();

        $album->user_id = $newAuthor->user_id;
        $album->username = $newAuthor->username;

        $album->rebuildCounters();
        if (!$album->preSave())
        {
            throw new \XF\PrintableException($album->getErrors());
        }
        $album->save();

        if ($album->isVisible())
        {
            $this->adjustUserAlbumCountIfNeeded($oldAuthor, -1);

            $this->adjustUserAlbumCountIfNeeded($newAuthor, -1);
        }

        $db->commit();
    }

    protected function adjustUserAlbumCountIfNeeded(User $user, $amount)
    {
        $this->db()->query("
            UPDATE xf_user
            SET xfmg_album_count = GREATEST(0, xfmg_album_count + ?)
            WHERE user_id = ?
        ", [$amount, $user->user_id]);
    }
}