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
     * @param array|int[] $newDate
     */
    public function setNewDate(array $newDate) : void;

    /**
     * @param array|int[] $newTime
     */
    public function setNewTime(array $newTime) : void;

    /**
     * @param array|int[] $timeInterval
     */
    public function setTimeInterval(array $timeInterval) : void;

    public function setupOwnerChanger() : void;

    public function applyOwnerChanger() : void;
}