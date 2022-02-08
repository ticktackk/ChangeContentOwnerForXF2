<?php

namespace TickTackk\ChangeContentOwner\XF\Service\ProfilePostComment;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XF\Entity\ProfilePostComment as ExtendedProfilePostCommentEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * @version 2.0.14
 *
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XF\Service\ProfilePostComment
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XF:ProfilePostComment';
    }

    /**
     * @param Entity|ExtendedProfilePostCommentEntity     $content
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
     * @param Entity|ExtendedProfilePostCommentEntity $content
     * @param int    $newDate
     *
     * @return Entity
     * @throws \Exception
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        $oldTimestamp = $this->getOldTimestamp($content);
        $content->comment_date = $newDate;

        $profilePost = $content->ProfilePost;
        if ($profilePost)
        {
            if ($profilePost->first_comment_date === $oldTimestamp)
            {
                $profilePost->first_comment_date = $newDate;
            }

            if ($profilePost->last_comment_date === $oldTimestamp)
            {
                $profilePost->last_comment_date = $newDate;
            }
        }

        return $content;
    }

    /**
     * @since 2.0.14
     *
     * @param Entity|ExtendedProfilePostCommentEntity $content
     *
     * @return void
     */
    protected function postContentSave(Entity $content): void
    {
        $profilePost = $content->ProfilePost;
        if ($profilePost)
        {
            $profilePost->rebuildCounters();
            $profilePost->saveIfChanged();
        }
    }

    /**
     * @param Entity|ExtendedProfilePostCommentEntity $content
     */
    protected function additionalEntitySave(Entity $content): void
    {
    }
}