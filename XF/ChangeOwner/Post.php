<?php

namespace TickTackk\ChangeContentOwner\XF\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Mvc\Entity\Entity;
use TickTackk\ChangeContentOwner\XF\Entity\Post as ExtendedPostEntity;

/**
 * Class Post
 *
 * @package TickTackk\ChangeContentOwner\XF\ChangeOwner
 */
class Post extends AbstractHandler
{
    /**
     * @param Entity|ExtendedPostEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        $breadcrumbs = $content->Thread->Forum->getBreadcrumbs();
        $breadcrumbs[] = [
            'value' => $this->getContentTitle($content),
            'href' => $this->getContentLink($content)
        ];

        return $breadcrumbs;
    }

    /**
     * @param Entity|ExtendedPostEntity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'posts';
    }

    /**
     * @param Entity|ExtendedPostEntity $content
     *
     * @return string|\XF\Phrase
     *
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function getContentTitle(Entity $content)
    {
        return \XF::phrase('post_in_thread_x', [
            'title' => $content->Thread->title
        ]);
    }

    /**
     * @param Entity|ExtendedPostEntity $content
     *
     * @return int
     */
    public function getOldTimestamp(Entity $content): int
    {
        return $content->post_date;
    }
}