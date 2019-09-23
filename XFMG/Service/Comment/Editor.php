<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Comment;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface;
use TickTackk\ChangeContentOwner\Service\Content\EditorTrait;
use XF\Mvc\Entity\Entity;

/**
 * Class Editor
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Service\Comment
 */
class Editor extends XFCP_Editor implements EditorInterface
{
    use EditorTrait;

    /**
     * @return ContentEntityInterface|Entity
     */
    protected function getContentForOwnerChangerSvc(): ContentEntityInterface
    {
        return $this->getComment();
    }

    /**
     * @return string
     */
    protected function getOwnerChangerServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XFMG:Comment\OwnerChanger';
    }
}