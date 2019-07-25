<?php

namespace TickTackk\ChangeContentOwner\XFMG\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use TickTackk\ChangeContentOwner\XFMG\Entity\Album as ExtendedAlbumEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class Album
 *
 * @package TickTackk\ChangeContentOwner\XFMG\ChangeOwner
 */
class Album extends AbstractHandler
{
    /**
     * @param Entity|ExtendedAlbumEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        return $content->getBreadcrumbs();
    }

    /**
     * @param Entity|ExtendedAlbumEntity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'media/albums';
    }

    /**
     * @param Entity|ExtendedAlbumEntity $content
     *
     * @return int
     */
    public function getOldTimestamp(Entity $content): int
    {
        return $content->create_date;
    }

    /**
     * @param Entity|ExtendedAlbumEntity $content
     *
     * @return string|\XF\Phrase
     */
    public function getContentTitle(Entity $content)
    {
        return $content->title;
    }
}