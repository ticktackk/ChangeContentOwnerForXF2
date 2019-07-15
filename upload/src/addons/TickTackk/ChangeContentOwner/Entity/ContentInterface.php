<?php

namespace TickTackk\ChangeContentOwner\Entity;

use XF\Entity\User;

/**
 * Interface ContentInterface
 *
 * @package TickTackk\ChangeContentOwner\Entity
 */
interface ContentInterface
{
    /**
     * @param User|null $newUser
     * @param null      $error
     *
     * @return bool
     */
    public function canChangeOwner(User $newUser = null, &$error = null) : bool;

    /**
     * @param int|null $newDate
     * @param null     $error
     *
     * @return bool
     */
    public function canChangeDate(int $newDate = null, &$error = null) : bool;
}