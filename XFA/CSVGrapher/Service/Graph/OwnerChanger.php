<?php

namespace TickTackk\ChangeContentOwner\XFA\CSVGrapher\Service\Graph;

use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger;
use TickTackk\ChangeContentOwner\XFA\CSVGrapher\Entity\Graph as ExtendedGraphEntity;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class OwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\XFA\CSVGrapher\Service\Graph
 */
class OwnerChanger extends AbstractOwnerChanger
{
    /**
     * OwnerChanger constructor.
     *
     * @param \XF\App   $app
     * @param           $contents
     * @param bool|null $logModerator
     *
     * @throws \Exception
     */
    public function __construct(/** @noinspection PhpUnusedParameterInspection */ \XF\App $app, $contents, bool $logModerator = null)
    {
        parent::__construct($app, $contents, false);
    }

    /**
     * @return string
     */
    protected function getEntityIdentifier(): string
    {
        return 'XFA\CSVGrapher:Graph';
    }

    /**
     * @param Entity|ExtendedGraphEntity     $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     */
    protected function changeContentOwner(Entity $content, UserEntity $newOwner): Entity
    {
        $content->user_id = $newOwner->user_id;
        $content->username = $newOwner->username;

        $oldUser = $this->getOldOwner($content);
        $this->increaseContentCount($newOwner, 'xfa_csvg_datalogs_count');
        $this->decreaseContentCount($oldUser, 'xfa_csvg_datalogs_count');

        return $content;
    }

    /**
     * @param Entity|ExtendedGraphEntity $content
     * @param int    $newDate
     *
     * @return Entity
     */
    protected function changeContentDate(Entity $content, int $newDate): Entity
    {
        $content->graph_date = $newDate;

        return $content;
    }

    /**
     * @param Entity $content
     */
    protected function additionalEntitySave(Entity $content): void
    {
    }
}