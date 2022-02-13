<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait;
use XF\Entity\User as UserEntity;

/**
 * Class Album
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Entity
 */
class Album extends XFCP_Album implements ContentInterface
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