<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use XF\Entity\User;

/**
 * Class Album
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Entity
 */
class Album extends XFCP_Album implements ContentInterface
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

        return $this->hasPermission('changeAlbumOwner');
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

        return $this->hasPermission('changeAlbumDate');
    }
}