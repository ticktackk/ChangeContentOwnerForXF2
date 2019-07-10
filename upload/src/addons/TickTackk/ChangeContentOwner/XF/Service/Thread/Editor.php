<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Thread;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Service\Content\EditorTrait;
use XF\Mvc\Entity\Entity;

/**
 * Class Editor
 *
 * @package TickTackk\ChangeContentOwner\XF\Service\Thread
 */
class Editor extends XFCP_Editor
{
    use EditorTrait;

    /**
     * @return ContentEntityInterface|Entity
     */
    protected function getContentForOwnerChangerSvc(): ContentEntityInterface
    {
        return $this->getThread();
    }

    /**
     * @return string
     */
    protected function getOwnerChangerServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:Thread\OwnerChanger';
    }
}