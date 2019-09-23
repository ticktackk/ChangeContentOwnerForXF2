<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Post;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface;
use TickTackk\ChangeContentOwner\Service\Content\EditorTrait;
use XF\Mvc\Entity\Entity;

/**
 * Class Editor
 *
 * @package TickTackk\ChangeContentOwner\XF\Service\Post
 */
class Editor extends XFCP_Editor implements EditorInterface
{
    use EditorTrait;

    /**
     * @return ContentEntityInterface|Entity
     */
    protected function getContentForOwnerChangerSvc(): ContentEntityInterface
    {
        return $this->getPost();
    }

    /**
     * @return string
     */
    protected function getOwnerChangerServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:Post\OwnerChanger';
    }
}