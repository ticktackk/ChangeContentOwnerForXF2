<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Album;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Service\Content\EditorTrait;
use XF\Mvc\Entity\Entity;

/**
 * Class Editor
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Service\Album
 */
class Editor extends XFCP_Editor
{
    use EditorTrait;

    /**
     * @return ContentEntityInterface|Entity
     */
    protected function getContentForOwnerChangerSvc(): ContentEntityInterface
    {
        return $this->getAlbum();
    }

    /**
     * @return string
     */
    protected function getOwnerChangerServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XFMG:Album\OwnerChanger';
    }
}