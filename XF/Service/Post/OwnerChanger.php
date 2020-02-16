<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Post;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XF\Entity\Post as ExtendedPostEntity;
use TickTackk\ChangeContentOwner\XF\Service\RebuildThreadUserPostCounterTrait;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Repository\Thread as ThreadRepo;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XF\Service\Post
 */
class OwnerChanger extends AbstractOwnerChanger
{
    use RebuildThreadUserPostCounterTrait;

    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XF:Post';
    }

    /**
     * @param Entity|ExtendedPostEntity     $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     */
    protected function changeContentOwner(Entity $content, UserEntity $newOwner): Entity
    {
        $content->user_id = $newOwner->user_id;
        $content->username = $newOwner->username;

        $thread = $content->Thread;
        if ($thread)
        {
            if ($thread->last_post_id === $content->post_id)
            {
                $thread->last_post_user_id = $newOwner->user_id;
                $thread->last_post_username = $newOwner->username;
            }

            $forum = $thread->Forum;
            if ($forum && $forum->last_post_id === $content->post_id)
            {
                $forum->last_post_user_id = $newOwner->user_id;
                $forum->last_post_username = $newOwner->username;
            }
        }

        $oldUser = $this->getOldOwner($content);
        if ($content->isVisible())
        {
            $this->increaseContentCount($newOwner, 'message_count');
            $this->decreaseContentCount($oldUser, 'message_count');
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedPostEntity $content
     * @param int    $newDate
     *
     * @return Entity
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        $content->post_date = $newDate;

        $thread = $content->Thread;
        if ($thread)
        {
            if ($thread->last_post_id === $content->post_id)
            {
                $thread->last_post_date = $newDate;
            }

            $forum = $thread->Forum;
            if ($forum && $forum->last_post_id === $content->post_id)
            {
                $forum->last_post_date = $newDate;
            }
        }

        return $content;
    }

    /**
     * @param Entity|ExtendedPostEntity $content
     *
     * @throws \XF\PrintableException
     */
    protected function additionalEntitySave(Entity $content): void
    {
        $thread = $content->Thread;
        if ($thread)
        {
            $thread->save(true, false);

            $forum = $thread->Forum;
            if ($forum)
            {
                $forum->save(true, false);
            }
        }
    }

    /**
     * @param Entity|ExtendedPostEntity $content
     *
     * @throws \Exception
     */
    protected function postContentSave(Entity $content): void
    {
        $oldTimestamp = $this->getOldTimestamp($content);
        $newTimestamp = $this->getNewTimestamp($content);
        if ($oldTimestamp !== $newTimestamp)
        {
            $threadRepo = $this->getThreadRepo();
            $threadRepo->rebuildThreadPostPositions($content->thread_id);
        }
    }

    /**
     * @return Repository|ThreadRepo
     */
    protected function getThreadRepo() : ThreadRepo
    {
        return $this->repository('XF:Thread');
    }
}