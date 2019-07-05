<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Album;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XFMG\Entity\Album as ExtendedAlbumEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Service\Album
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XFMG:Album';
    }

    /**
     * @param Entity|ExtendedAlbumEntity     $content
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
            $this->increaseContentCount($newOwner, 'xfmg_album_count');
            $this->decreaseContentCount($oldUser, 'xfmg_album_count');
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedAlbumEntity $content
     * @param int    $newDate
     *
     * @return Entity
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        return $content;
    }

    /**
     * @param Entity $content
     */
    protected function additionalEntitySave(Entity $content): void
    {
    }
}