<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use XF\Mvc\Reply\View;

/**
 * Class InlineMod
 *
 * @package TickTackk\ChangeContentOwner
 */
class InlineMod extends XFCP_InlineMod
{
    /**
     * @return \XF\Mvc\Reply\View
     */
    public function actionShowActions()
    {
        $response = parent::actionShowActions();

        if ($response instanceof View && \XF::$versionId <= 2000470)
        {
            $type = $this->filter('type', 'str');

            $handler = $this->getInlineModHandler($type);
            if (!$handler)
            {
                return $this->noPermission();
            }

            $ids = $handler->getCookieIds($this->request);
            $entities = $handler->getEntities($ids);

            if (method_exists($handler, 'getActions'))
            {
                $actions = $handler->getActions();
            }
            else
            {
                $actions = $handler->getPossibleActions();
            }
            $available = [];
            if ($entities->count())
            {
                foreach ($actions AS $actionId => $action)
                {
                    if ($action->canApply($entities))
                    {
                        $available[$actionId] = $action->getTitle();
                    }
                }
            }

            $response->setParams([
                'actions' => $available
            ]);
        }

        return $response;
    }
}