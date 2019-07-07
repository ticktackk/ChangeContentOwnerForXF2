<?php

namespace TickTackk\ChangeContentOwner\XFA\CSVGrapher\Repository;

use TickTackk\ChangeContentOwner\Repository\ContentInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait;

/**
 * Class Graph
 *
 * @package TickTackk\ChangeContentOwner\XFA\CSVGrapher\Repository
 */
class Graph extends XFCP_Graph implements ContentInterface
{
    use ContentTrait;
}