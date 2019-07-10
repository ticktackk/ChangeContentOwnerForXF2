<?php

namespace TickTackk\ChangeContentOwner\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use XF\ControllerPlugin\AbstractPlugin;

/**
 * Trait ContentTrait
 *
 * @package TickTackk\ChangeContentOwner\Pub\Controller
 */
trait ContentTrait
{
    /**
     * @return AbstractPlugin|ContentPlugin
     */
    protected function getChangeContentOwnerPlugin() : ContentPlugin
    {
        return $this->plugin('TickTackk\ChangeContentOwner:Content');
    }
}