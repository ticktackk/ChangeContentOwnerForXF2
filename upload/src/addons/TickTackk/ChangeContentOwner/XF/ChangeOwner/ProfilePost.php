<?php

namespace TickTackk\ChangeContentOwner\XF\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Mvc\Entity\Entity;
use TickTackk\ChangeContentOwner\XF\Entity\ProfilePost as ExtendedProfilePostEntity;

/**
 * Class ProfilePost
 *
 * @package TickTackk\ChangeContentOwner\XF\ChangeOwner
 */
class ProfilePost extends AbstractHandler
{
    /**
     * @param Entity|ExtendedProfilePostEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        $breadcrumbs = [];

        $breadcrumbs[] = [
            'value' => $this->getContentTitle($content),
            'href' => $this->getContentLink($content)
        ];

        return $breadcrumbs;
    }

    /**
     * @param Entity|ExtendedProfilePostEntity $content
     *
     * @return int
     */
    public function getOldTimestamp(Entity $content): int
    {
        return $content->post_date;
    }

    /**
     * @param Entity|ExtendedProfilePostEntity $content
     *
     * @return string|\XF\Phrase
     */
    public function getContentTitle(Entity $content)
    {
        return \XF::phrase('profile_post_by_x', ['name' => $content->username]);
    }

    /**
     * @param Entity|ExtendedProfilePostEntity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'profile-posts';
    }
}