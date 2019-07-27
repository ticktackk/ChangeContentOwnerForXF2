<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait;
use XF\Entity\User;

/**
 * Class ProfilePost
 *
 * @package TickTackk\ChangeContentOwner
 */
class ProfilePost extends XFCP_ProfilePost implements ContentInterface
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
        $visitor = \XF::visitor();

        if (!$visitor->user_id)
        {
            return false;
        }

        if ($newUser && $this->getExistingValue('user_id') === $newUser->user_id)
        {
            return false;
        }

        return $visitor->hasPermission('profilePost', 'changeProfilePostOwner');
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

        return $visitor->hasPermission('profilePost', 'changeProfilePostDate');
    }
}