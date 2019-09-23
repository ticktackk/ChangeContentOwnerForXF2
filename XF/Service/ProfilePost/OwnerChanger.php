<?php

namespace TickTackk\ChangeContentOwner\XF\Service\ProfilePost;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XF\Entity\ProfilePost as ExtendedProfilePostEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XF\Service\ProfilePost
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XF:ProfilePost';
    }

    /**
     * @param Entity|ExtendedProfilePostEntity     $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     */
    protected function changeContentOwner(Entity $content, UserEntity $newOwner): Entity
    {
        $content->user_id = $newOwner->user_id;
        $content->username = $newOwner->username;

        return $content;
    }

    /**
     * @param Entity|ExtendedProfilePostEntity $content
     * @param int    $newDate
     *
     * @return Entity
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        $content->post_date = $newDate;

        return $content;
    }

    /**
     * @param Entity|ExtendedProfilePostEntity $content
     */
    protected function additionalEntitySave(Entity $content): void
    {
    }
}