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
     * @param null $error
     *
     * @return bool
     */
    public function canChangeDate(&$error = null) : bool;
}