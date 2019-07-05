<?php

namespace TickTackk\ChangeContentOwner\XF\Repository;

use TickTackk\ChangeContentOwner\Repository\ContentInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait;

/**
 * Class Post
 *
 * @package TickTackk\ChangeContentOwner\XF\Repository
 */
class Post extends XFCP_Post implements ContentInterface
{
    use ContentTrait;
}