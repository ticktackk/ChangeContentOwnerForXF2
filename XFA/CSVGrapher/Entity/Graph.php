<?php

namespace TickTackk\ChangeContentOwner\XFA\CSVGrapher\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait;
use XF\Entity\User as UserEntity;

/**
 * Class Graph
 *
 * @package TickTackk\ChangeContentOwner\XFA\CSVGrapher\Entity
 */
class Graph extends XFCP_Graph implements ContentInterface
{
    use ContentTrait;

    /**
     * @param UserEntity|null $newOwner
     * @param null      $error
     *
     * @return bool
     */
    public function canChangeOwner(UserEntity $newOwner = null, &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id || !$this->user_id)
        {
            return false;
        }

        if ($newOwner && $this->getExistingValue('user_id') === $newOwner->user_id)
        {
            return false;
        }

        return $this->hasPermission('changeGraphOwner');
    }

    /**
     * @param int|null $newDate
     * @param null     $error
     *
     * @return bool
     */
    public function canChangeDate(int $newDate = null, &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id || !$this->user_id)
        {
            return false;
        }

        return $this->hasPermission('changeGraphDate');
    }
}