<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

/**
 * Class Thread
 *
 * @package TickTackk\ChangeContentOwner
 *
 * @property \TickTackk\ChangeContentOwner\XF\Entity\Forum Forum
 */
class Thread extends XFCP_Thread
{
    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canChangeAuthor(&$error = null)
    {
        $forum = $this->Forum;
        if (!$forum)
        {
            return false;
        }

        return $forum->canChangeThreadAuthor($error);
    }

    /**
     * @param null $error
     *
     * @return bool
     */
    public function canChangePostAuthor(&$error = null)
    {
        $forum = $this->Forum;
        if (!$forum)
        {
            return false;
        }

        return $forum->canChangePostAuthor($error);
    }
}