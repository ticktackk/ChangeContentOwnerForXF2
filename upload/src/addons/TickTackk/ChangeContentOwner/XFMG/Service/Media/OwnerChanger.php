<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Media;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XFMG\Entity\MediaItem as ExtendedMediaItemEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Service\Media
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XFMG:MediaItem';
    }

    /**
     * @return string
     */
    protected function getRepoIdentifier(): string
    {
        return 'XFMG:Media';
    }

    /**
     * @param Entity|ExtendedMediaItemEntity     $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     */
    protected function changeContentOwner(Entity $content, UserEntity $newOwner): Entity
    {
        $content->user_id = $newOwner->user_id;
        $content->username = $newOwner->user_id;

        $oldUser = $this->getOldOwner($content);
        if ($content->isVisible())
        {
            $this->increaseContentCount($newOwner, 'xfmg_media_count');
            $this->decreaseContentCount($oldUser, 'xfmg_media_count');

            $attachment = $content->Attachment;
            if ($attachment)
            {
                $fileSize = $attachment->file_size;
                $this->increaseContentCount($newOwner, 'xfmg_media_quota', $fileSize);
                $this->decreaseContentCount($newOwner, 'xfmg_media_quota', $fileSize);
            }
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedMediaItemEntity $content
     * @param int    $newDate
     *
     * @return Entity
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        $content->media_date = $newDate;

        return $content;
    }

    /**
     * @param Entity|ExtendedMediaItemEntity $content
     */
    protected function additionalEntitySave(Entity $content): void
    {
    }
}