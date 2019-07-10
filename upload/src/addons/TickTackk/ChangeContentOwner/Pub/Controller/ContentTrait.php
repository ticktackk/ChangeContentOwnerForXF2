<?php

namespace TickTackk\ChangeContentOwner\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Repository\ContentInterface as ContentRepoInterface;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Service\AbstractService;

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