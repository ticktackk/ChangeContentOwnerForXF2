<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use XF\Entity\User;

/**
 * Class ProfilePostComment
 *
 * @package TickTackk\ChangeContentOwner\XF\Entity
 */
class ProfilePostComment extends XFCP_ProfilePostComment implements ContentInterface
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

        return $visitor->hasPermission('profilePost', 'changeCommentOwner');
    }

    /**
     * @param int|null $newDate
     * @param null     $error
     *
     * @return bool
     */
    public function canChangeDate(int $newDate = null, &$error = null): bool
    {
        $visitor = \XF::visitor();

        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasPermission('profilePost', 'changeCommentDate');
    }
}