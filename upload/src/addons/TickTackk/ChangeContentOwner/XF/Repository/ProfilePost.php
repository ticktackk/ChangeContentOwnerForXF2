<?php

namespace TickTackk\ChangeContentOwner\XF\Repository;

use TickTackk\ChangeContentOwner\Repository\ContentInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait;

/**
 * Class ProfilePost
 *
 * @package TickTackk\ChangeContentOwner\XF\Repository
 */
class ProfilePost extends XFCP_ProfilePost implements ContentInterface
{
    use ContentTrait;
}