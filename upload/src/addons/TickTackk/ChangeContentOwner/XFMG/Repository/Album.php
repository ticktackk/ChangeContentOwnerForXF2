<?php

namespace TickTackk\ChangeContentOwner\XFMG\Repository;

use TickTackk\ChangeContentOwner\Repository\ContentInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait;

/**
 * Class Album
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Repository
 */
class Album extends XFCP_Album implements ContentInterface
{
    use ContentTrait;
}