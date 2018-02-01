<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\MediaItem;

use \XFMG\Entity\MediaItem;
use \XFMG\Entity\Album;
use \XFMG\Entity\Category;
use XF\Entity\User;

class AuthorChanger extends \XF\Service\AbstractService
{
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

    public function __construct(\XF\App $app, Album $album, MediaItem $mediaItem, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);
        $this->category = $mediaItem->Category;
        $this->album = $album;
        $this->mediaItem = $mediaItem;
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
        $mediaItem = $this->getMediaItem();

        $db = $this->db();
        $db->beginTransaction();

        $mediaItem->user_id = $newAuthor->user_id;
        $mediaItem->username = $newAuthor->username;

        $mediaItem->rebuildCounters();
        if (!$mediaItem->preSave())
        {
            throw new \XF\PrintableException($mediaItem->getErrors());
        }
        $mediaItem->save();

        if ($mediaItem->isVisible())
        {
            $this->adjustUserMediaCountIfNeeded($oldAuthor, -1);
            $this->adjustUserMediaQuotaIfNeeded($mediaItem, $oldAuthor, true);

            $this->adjustUserMediaCountIfNeeded($newAuthor, -1);
            $this->adjustUserMediaQuotaIfNeeded($mediaItem, $newAuthor);
        }

        $db->commit();
    }

    protected function adjustUserMediaCountIfNeeded(User $user, $amount)
    {
        $this->db()->query("
            UPDATE xf_user
            SET xfmg_media_count = GREATEST(0, xfmg_media_count + ?)
            WHERE user_id = ?
        ", [$amount, $user->user_id]);
    }

    protected function adjustUserMediaQuotaIfNeeded(MediaItem $mediaItem, User $user, $delete = false)
    {
        if ($mediaItem->Attachment && $user)
        {
            $fileSize = $mediaItem->Attachment->getFileSize();
            $existing = $user->xfmg_media_quota;

            if ($delete)
            {
                $user->xfmg_media_quota = ($existing - $fileSize) / 1024;
            }
            else
            {
                $user->xfmg_media_quota = ($existing + $fileSize) / 1024;
            }

            $user->save();
        }
    }
}