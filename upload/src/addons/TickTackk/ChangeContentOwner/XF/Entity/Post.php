<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait;
use XF\Entity\User;

/**
 * Class Post
 *
 * @package TickTackk\ChangeContentOwner\XF\Entity
 *
 * RELATIONS
 * @property Thread Thread
 */
class Post extends XFCP_Post implements ContentInterface
{
    use ContentTrait;

    /**
     * @param User|null $newUser
     * @param null      $error
     *
     * @return bool
     */
    public function canChangeOwner(User $newUser = null, &$error = null): bool
    {
        $thread = $this->Thread;
        if (!$thread)
        {
            return false;
        }

        if ($thread->first_post_id === $this->post_id)
        {
            return false;
        }

        if ($newUser && $this->user_id === $newUser->user_id)
        {
            return false;
        }

        return $thread->canChangePostOwner($newUser, $error);
    }

    /**
     * @param int|null $newDate
     * @param null     $error
     *
     * @return bool
     */
    public function canChangeDate(int $newDate = null, &$error = null): bool
    {
        $thread = $this->Thread;
        if (!$thread)
        {
            return false;
        }

        if ($thread->first_post_id === $this->post_id)
        {
            return false;
        }

        if ($newDate && $thread->post_date >= $newDate)
        {
            $error = \XF::phraseDeferred('tckChangeContentOwner_new_date_must_be_older_than_thread_date');
            return false;
        }

        return $thread->canChangePostDate($error);
    }
}