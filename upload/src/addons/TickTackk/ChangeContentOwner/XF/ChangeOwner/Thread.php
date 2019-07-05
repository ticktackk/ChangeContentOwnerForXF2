<?php

namespace TickTackk\ChangeContentOwner\XF\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Mvc\Entity\Entity;
use TickTackk\ChangeContentOwner\XF\Entity\Thread as ExtendedThreadEntity;

/**
 * Class Thread
 *
 * @package TickTackk\ChangeContentOwner\XF\ChangeOwner
 */
class Thread extends AbstractHandler
{
    /**
     * @param Entity|ExtendedThreadEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        return $content->getBreadcrumbs();
    }

    /**
     * @param Entity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'threads';
    }

    /**
     * @param Entity|ExtendedThreadEntity $content
     *
     * @return string|\XF\Phrase
     */
    public function getContentTitle(Entity $content)
    {
        return $content->title;
    }
}