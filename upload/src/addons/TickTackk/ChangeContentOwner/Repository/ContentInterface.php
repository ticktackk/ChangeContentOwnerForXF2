<?php

namespace TickTackk\ChangeContentOwner\Repository;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;

/**
 * Interface ContentInterface
 *
 * @package TickTackk\ChangeContentOwner\Repository
 */
interface ContentInterface
{
    /**
     * @param bool $throw
     *
     * @return AbstractHandler
     */
    public function getChangeOwnerHandler(bool $throw = false) : AbstractHandler;
}