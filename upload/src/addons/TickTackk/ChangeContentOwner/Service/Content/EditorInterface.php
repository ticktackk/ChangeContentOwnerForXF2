<?php

namespace TickTackk\ChangeContentOwner\Service\Content;

use XF\Entity\User as UserEntity;

/**
 * Interface EditorInterface
 *
 * @package TickTackk\ChangeContentOwner\Service\Content
 */
interface EditorInterface
{
    /**
     * @param UserEntity $newOwner
     */
    public function setNewOwner(UserEntity $newOwner) : void;

    /**
     * @param int $newDate
     */
    public function setNewDate(int $newDate) : void;

    public function setupOwnerChanger() : void;

    public function applyOwnerChanger() : void;
}