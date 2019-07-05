<?php

namespace TickTackk\ChangeContentOwner\XFMG\Repository;

use TickTackk\ChangeContentOwner\Repository\ContentInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait;

/**
 * Class Comment
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Repository
 */
class Comment extends XFCP_Comment implements ContentInterface
{
    use ContentTrait;
}