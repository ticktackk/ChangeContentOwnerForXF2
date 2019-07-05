<?php

namespace TickTackk\ChangeContentOwner\Repository;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Mvc\Entity\Entity;

/**
 * Interface ContentInterface
 *
 * @package TickTackk\ChangeContentOwner\Repository
 */
interface ContentInterface
{
    /**
     * @param Entity $content
     * @param bool $throw
     *
     * @return AbstractHandler
     */
    public function getChangeOwnerHandler(Entity $content, bool $throw = false) : AbstractHandler;
}