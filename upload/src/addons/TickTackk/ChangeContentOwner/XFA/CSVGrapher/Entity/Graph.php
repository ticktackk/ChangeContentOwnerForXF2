<?php

namespace TickTackk\ChangeContentOwner\XFA\CSVGrapher\Entity;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use XF\Entity\User;

/**
 * Class Graph
 *
 * @package TickTackk\ChangeContentOwner\XFA\CSVGrapher\Entity
 */
class Graph extends XFCP_Graph implements ContentInterface
{
    /**
     * @param User|null $newUser
     * @param null      $error
     *
     * @return bool
     */
    public function canChangeOwner(User $newUser = null, &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id || !$this->user_id)
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