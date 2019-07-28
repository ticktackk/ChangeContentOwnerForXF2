<?php

namespace TickTackk\ChangeContentOwner\Entity;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Entity\User as UserEntity;

/**
 * Interface ContentInterface
 *
 * @package TickTackk\ChangeContentOwner\Entity
 */
interface ContentInterface
{
    /**
     * @param UserEntity|null $newOwner
     * @param null            $error
     *
     * @return bool
     */
    public function canChangeOwner(UserEntity $newOwner = null, &$error = null) : bool;

    /**
     * @param int|null $newDate
     * @param null     $error
     *
     * @return bool
     */
    public function canChangeDate(int $newDate = null, &$error = null) : bool;

    /**
     * @param bool $throw
     *
     * @return AbstractHandler
     */
    public function getChangeOwnerHandler(bool $throw = false) : AbstractHandler;
}