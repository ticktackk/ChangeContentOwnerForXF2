<?php

namespace TickTackk\ChangeContentOwner\XFMG\Repository;

use TickTackk\ChangeContentOwner\Repository\ContentInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait;

/**
 * Class Media
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Repository
 */
class Media extends XFCP_Media implements ContentInterface
{
    use ContentTrait;
}