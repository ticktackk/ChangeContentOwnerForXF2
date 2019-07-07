<?php

namespace TickTackk\ChangeContentOwner\XFA\CSVGrapher\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;

/**
 * Class Graph
 *
 * @package TickTackk\ChangeContentOwner\XFA\CSVGrapher\Pub\Controller
 */
class Graph extends XFCP_Graph
{
    /**
     * @param ParameterBag $parameterBag
     *
     * @return RedirectReply|ViewReply
     * @throws ExceptionReply
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    public function actionChangeOwner(ParameterBag $parameterBag)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $graph = $this->assertViewableGraph($parameterBag->graph_id);

        /** @var ContentPlugin $contentPlugin */
        $contentPlugin = $this->plugin('TickTackk\ChangeContentOwner:Content');
        return $contentPlugin->actionChangeOwner(
            $graph,
            'TickTackk\ChangeContentOwner\XFA\CSVGrapher:Graph\OwnerChanger',
            'XFA\CSVGrapher:Graph',
            'TickTackk\ChangeContentOwner\XFA\CSVGrapher:Graph\ChangeOwner'
        );
    }
}