<?php

namespace TickTackk\ChangeContentOwner\XFA\CSVGrapher\Pub\Controller;

use TickTackk\ChangeContentOwner\Pub\Controller\ContentTrait;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XFA\CSVGrapher\Entity\Graph as GraphEntity;
use XFA\CSVGrapher\Service\Graph\Editor as GraphEditor;

/**
 * Class Graph
 *
 * @package TickTackk\ChangeContentOwner\XFA\CSVGrapher\Pub\Controller
 */
class Graph extends XFCP_Graph
{
    use ContentTrait;

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

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $graph,
            'TickTackk\ChangeContentOwner\XFA\CSVGrapher:Graph\OwnerChanger',
            'XFA\CSVGrapher:Graph'
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return ErrorReply|RedirectReply|ViewReply
     * @throws ExceptionReply
     * @throws \Exception
     */
    public function actionEdit(ParameterBag $params)
    {
        $reply = parent::actionEdit($params);

        $this->getChangeContentOwnerPlugin()->extendContentEditAction(
            $reply,
            'graph'
        );

        return $reply;
    }

    /**
     * @param GraphEntity $graph
     *
     * @return EditorSvcInterface|GraphEditor
     * @throws ExceptionReply
     */
    protected function setupGraphEditor(GraphEntity $graph) : GraphEditor
    {
        /** @var GraphEditor|EditorSvcInterface $editor */
        $editor = parent::setupGraphEditor($graph);

        $this->getChangeContentOwnerPlugin()->extendEditorService($graph, $editor);

        return $editor;
    }
}