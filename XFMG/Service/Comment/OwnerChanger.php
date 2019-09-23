<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Comment;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XFMG\Entity\Comment as ExtendedCommentEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Service\Comment
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XFMG:Comment';
    }

    /**
     * @param Entity|ExtendedCommentEntity     $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     */
    protected function changeContentOwner(Entity $content, UserEntity $newOwner): Entity
    {
        $content->user_id = $newOwner->user_id;
        $content->username = $newOwner->user_id;

        $mediaItem = $content->Media;
        if ($mediaItem && $mediaItem->last_comment_id === $content->comment_id)
        {
            $mediaItem->last_comment_user_id = $newOwner->user_id;
            $mediaItem->last_comment_username = $newOwner->username;
        }

        $album = $content->Album;
        if ($album && $album->last_comment_id === $content->comment_id)
        {
            $album->last_comment_user_id = $newOwner->user_id;
            $album->last_comment_username = $newOwner->username;
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedCommentEntity $content
     * @param int    $newDate
     *
     * @return Entity
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        $content->comment_date = $newDate;

        $mediaItem = $content->Media;
        if ($mediaItem && $mediaItem->last_comment_id === $content->comment_id)
        {
            $mediaItem->last_comment_date = $newDate;
        }

        $album = $content->Album;
        if ($album && $album->last_comment_id === $content->comment_id)
        {
            $album->last_comment_date = $newDate;
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedCommentEntity $content
     * @throws \XF\PrintableException
     */
    protected function additionalEntitySave(Entity $content): void
    {
        $mediaItem = $content->Media;
        if ($mediaItem)
        {
            $mediaItem->save(true, false);
        }

        $album = $content->Album;
        if ($album)
        {
            $album->save(true, false);
        }
    }
}