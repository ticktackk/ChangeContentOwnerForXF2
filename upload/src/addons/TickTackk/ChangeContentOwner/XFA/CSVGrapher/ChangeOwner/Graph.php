<?php

namespace TickTackk\ChangeContentOwner\XFA\CSVGrapher\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;
use XFA\CSVGrapher\Entity\Graph as GraphEntity;

/**
 * Class Graph
 *
 * @package TickTackk\ChangeContentOwner\XFA\CSVGrapher\ChangeOwner
 */
class Graph extends AbstractHandler
{
    /**
     * @param Entity|GraphEntity     $content
     * @param UserEntity $newOwner
     * @param \XF\Phrase $error
     *
     * @return bool
     * @throws \Exception
     */
    public function canNewOwnerViewContent(Entity $content, UserEntity $newOwner, &$error): bool
    {
        $content->setBypassPrivacy();
        return parent::canNewOwnerViewContent($content, $newOwner, $error);
    }

    /**
     * @param Entity|GraphEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        return $content->getBreadcrumbs();
    }

    /**
     * @param Entity|GraphEntity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'graphs';
    }

    /**
     * @param Entity|GraphEntity $content
     *
     * @return int
     */
    public function getOldTimestamp(Entity $content): int
    {
        return $content->graph_date;
    }

    /**
     * @param Entity|GraphEntity $content
     *
     * @return string|\XF\Phrase
     */
    public function getContentTitle(Entity $content)
    {
        return $content->title;
    }
}