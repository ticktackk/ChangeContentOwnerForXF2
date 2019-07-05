<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use XF\Entity\User;

/**
 * Class Comment
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Entity
 */
class Comment extends XFCP_Comment implements ContentInterface
{
    /**
     * @param User|null $newUser
     * @param null      $error
     *
     * @return bool
     */
    public function canChangeOwner(User $newUser = null, &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        $content = $this->Content;
        if (!$content)
        {
            return false;
        }

        return $content->hasPermission('changeCommentOwner');
    }

    /**
     * @param null $error
     *
     * @return bool
     */
    public function canChangeDate(&$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        $content = $this->Content;
        if (!$content)
        {
            return false;
        }

        return $content->hasPermission('changeCommentDate');
    }
}