<?php

namespace TickTackk\ChangeContentOwner\XF\Repository;

use TickTackk\ChangeContentOwner\Repository\ContentInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait;

/**
 * Class Thread
 *
 * @package TickTackk\ChangeContentOwner\XF\Repository
 */
class Thread extends XFCP_Thread implements ContentInterface
{
    use ContentTrait;
}