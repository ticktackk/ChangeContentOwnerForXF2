<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Thread;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XF\Entity\Forum as ExtendedForumEntity;
use TickTackk\ThreadCount\XF\Entity\Forum as ExtendedForumEntityFromThreadCount;
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
     * @param Entity|ExtendedThreadEntity      $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     * @throws \XF\PrintableException
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

        /** @var ExtendedForumEntity $forum */
        $forum = $content->Forum;
        if ($forum)
        {
            if ($forum->last_post_id === $firstPost->post_id)
            {
                $forum->last_post_user_id = $newOwner->user_id;
                $forum->last_post_username = $newOwner->username;
            }

            if ($content->isVisible())
            {
                $oldUser = $this->getOldOwner($content);
                
                if ($forum->count_messages)
                {
                    $this->increaseContentCount($newOwner, 'message_count');
                    $this->decreaseContentCount($oldUser, 'message_count');
                }

                $addOns = $this->app->container('addon.cache');
                if ($addOns['TickTackk/ThreadCount'] ?? 0 >= 1000092)
                {
                    /** @var ExtendedForumEntityFromThreadCount $forum */
                    if ($forum->count_threads)
                    {
                        $this->increaseContentCount($newOwner, 'thread_count');
                        $this->decreaseContentCount($oldUser, 'thread_count');
                    }
                }
            }
        }

        $likedOrReactedContent = $this->getLikedOrReactedContent($firstPost);
        if ($likedOrReactedContent)
        {
            $likedOrReactedContent->delete(true, false);
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
        $content->post_date = $newDate;
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