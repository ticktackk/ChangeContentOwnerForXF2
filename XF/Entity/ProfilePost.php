<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait;
use XF\Entity\User as UserEntity;

/**
 * Class ProfilePost
 *
 * @package TickTackk\ChangeContentOwner
 */
class ProfilePost extends XFCP_ProfilePost implements ContentInterface
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
        $visitor = \XF::visitor();

        if (!$visitor->user_id)
        {
            return false;
        }

        if ($newOwner && $this->getExistingValue('user_id') === $newOwner->user_id)
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