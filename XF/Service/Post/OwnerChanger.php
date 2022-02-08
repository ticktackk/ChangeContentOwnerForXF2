<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Post;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XF\Entity\Post as ExtendedPostEntity;
use XF\Entity\ThreadUserPost as ThreadUserPostEntity;
use TickTackk\ChangeContentOwner\XF\Entity\Thread as ExtendedThreadEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Repository\Thread as ThreadRepo;

/**
 * @version 2.0.14
 *
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XF\Service\Post
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XF:Post';
    }

    /**
     * @param Entity|ExtendedPostEntity $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     *
     * @throws \XF\PrintableException
     */
    protected function changeContentOwner(Entity $content, UserEntity $newOwner): Entity
    {
        $content->user_id = $newOwner->user_id;
        $content->username = $newOwner->username;

        /** @var ExtendedThreadEntity $thread */
        $thread = $content->Thread;
        if ($thread)
        {
            if ($thread->last_post_id === $content->post_id)
            {
                $thread->last_post_user_id = $newOwner->user_id;
                $thread->last_post_username = $newOwner->username;
            }

            $oldUser = $this->getOldOwner($content);

            $oldUserThreadUserPost = $thread->UserPosts[$oldUser->user_id] ?? null;
            if ($oldUserThreadUserPost)
            {
                if ($oldUserThreadUserPost->post_count <= 1)
                {
                    $oldUserThreadUserPost->delete();
                }
                else
                {
                    $oldUserThreadUserPost->post_count--;
                    $oldUserThreadUserPost->save();
                }
            }

            /** @var ThreadUserPostEntity $newUserThreadUserPost */
            $newUserThreadUserPost = $this->finder('XF:ThreadUserPost')
                ->where('thread_id', $thread->thread_id)
                ->where('user_id', $newOwner->user_id)
                ->fetchOne();
            if (!$newUserThreadUserPost)
            {
                /** @var ThreadUserPostEntity $newUserThreadUserPost */
                $newUserThreadUserPost = $this->em()->create('XF:ThreadUserPost');
                $newUserThreadUserPost->user_id = $newOwner->user_id;
                $newUserThreadUserPost->thread_id = $thread->thread_id;
            }
            $newUserThreadUserPost->post_count++;
            $newUserThreadUserPost->save();

            $forum = $thread->Forum;
            if ($forum)
            {
                if ($forum->last_post_id === $content->post_id)
                {
                    $forum->last_post_user_id = $newOwner->user_id;
                    $forum->last_post_username = $newOwner->username;
                }

                if ($forum->count_messages && $content->isVisible())
                {
                    $this->increaseContentCount($newOwner, 'message_count');
                    $this->decreaseContentCount($oldUser, 'message_count');
                }
            }
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
     * @version 2.0.14
     *
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

        $thread = $content->Thread;
        if ($thread)
        {
            $thread->rebuildCounters();
            $thread->saveIfChanged();
        }
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function _validate(): array
    {
        $errors = parent::_validate();

        if ($this->getPerformValidations())
        {
            /** @var ExtendedPostEntity $content */
            foreach ($this->contents AS $content)
            {
                /** @var ExtendedThreadEntity $thread */
                $thread = $content->Thread;

                $oldTimestamp = $this->getOldTimestamp($content);
                $newTimestamp = $this->getNewTimestamp($content);

                if ($oldTimestamp !== $newTimestamp && $newTimestamp <= $thread->post_date)
                {
                    $errors[] = \XF::phraseDeferred('tckChangeContentOwner_new_date_must_be_older_than_thread_date');
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * @return Repository|ThreadRepo
     */
    protected function getThreadRepo() : ThreadRepo
    {
        return $this->repository('XF:Thread');
    }
}