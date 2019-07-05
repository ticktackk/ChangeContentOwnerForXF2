<?php

namespace TickTackk\ChangeContentOwner\XFMG\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Mvc\Entity\Entity;
use TickTackk\ChangeContentOwner\XFMG\Entity\MediaItem as ExtendedMediaItemEntity;

/**
 * Class MediaItem
 *
 * @package TickTackk\ChangeContentOwner\XFMG\ChangeOwner
 */
class MediaItem extends AbstractHandler
{
    /**
     * @param Entity|ExtendedMediaItemEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        return $content->getBreadcrumbs();
    }

    /**
     * @param Entity|ExtendedMediaItemEntity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'media';
    }

    /**
     * @param Entity|ExtendedMediaItemEntity $content
     *
     * @return int
     */
    public function getOldDate(Entity $content): int
    {
        return $content->media_date;
    }

    /**
     * @param Entity|ExtendedMediaItemEntity $content
     *
     * @return string|\XF\Phrase
     */
    public function getContentTitle(Entity $content)
    {
        return $content->title;
    }
}