<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Thread;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XF\Entity\Thread as ExtendedThreadEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XF\Service\Thread
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XF:Thread';
    }

    /**
     * @param Entity|ExtendedThreadEntity     $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     */
    protected function changeContentOwner(Entity $content, UserEntity $newOwner): Entity
    {
        $firstPost = $content->FirstPost;

        $firstPost->user_id = $newOwner->user_id;
        $firstPost->username = $newOwner->username;

        $content->user_id = $newOwner->user_id;
        $content->username = $newOwner->username;

        if ($content->last_post_id === $firstPost->post_id)
        {
            $content->last_post_user_id = $newOwner->user_id;
            $content->last_post_username = $newOwner->username;
        }

        $forum = $content->Forum;
        if ($forum->last_post_id === $firstPost->post_id)
        {
            $forum->last_post_user_id = $newOwner->user_id;
            $forum->last_post_username = $newOwner->username;
        }

        $addOns = $this->app->container('addon.cache');
        $threadCountSupport = $addOns['TickTackk/ThreadCount'] ?? 0 >= 1000092;
        if ($threadCountSupport &&  $content->isVisible())
        {
            $oldUser = $this->getOldOwner($content);
            $this->increaseContentCount($newOwner, 'thread_count');
            $this->decreaseContentCount($oldUser, 'thread_count');
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedThreadEntity $content
     * @param int    $newDate
     *
     * @return Entity
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        $firstPost = $content->FirstPost;

        $firstPost->post_date = $newDate;

        if ($content->last_post_id === $firstPost->post_id)
        {
            $content->last_post_date = $newDate;
        }

        $forum = $content->Forum;
        if ($forum->last_post_id === $firstPost->post_id)
        {
            $forum->last_post_date = $newDate;
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedThreadEntity $content
     *
     * @throws \XF\PrintableException
     */
    protected function additionalEntitySave(Entity $content): void
    {
        $firstPost = $content->FirstPost;
        if ($firstPost)
        {
            $firstPost->save(true, false);
        }

        $forum = $content->Forum;
        if ($forum)
        {
            $forum->save(true, false);
        }
    }
}