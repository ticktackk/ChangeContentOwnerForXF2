<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait;
use XF\Entity\User as UserEntity;

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
     * @param UserEntity|null $newOwner
     * @param null      $error
     *
     * @return bool
     */
    public function canChangeOwner(UserEntity $newOwner = null, &$error = null): bool
    {
        $thread = $this->Thread;
        if (!$thread)
        {
            return false;
        }

        if ($this->isFirstPost())
        {
            return false;
        }

        if ($newOwner && $this->getExistingValue('user_id') === $newOwner->user_id)
        {
            return false;
        }

        return $thread->canChangePostOwner($newOwner, $error);
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

        if ($this->isFirstPost())
        {
            return false;
        }

        if ($newDate && $thread->getExistingValue('post_date') > $newDate)
        {
            $error = \XF::phraseDeferred('tckChangeContentOwner_new_date_must_be_older_than_thread_date');
            return false;
        }

        return $thread->canChangePostDate($error);
    }
}