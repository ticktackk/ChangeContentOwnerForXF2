<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class InlineMod extends XFCP_InlineMod
{
    public function actionShowActions()
    {
        $response = parent::actionShowActions();

        if ($response instanceof \XF\Mvc\Reply\View && \XF::$versionId <= 2000470)
        {
            $type = $this->filter('type', 'str');

            $handler = $this->getInlineModHandler($type);
            if (!$handler)
            {
                return $this->noPermission();
            }

            $ids = $handler->getCookieIds($this->request);
            $entities = $handler->getEntities($ids);

            $actions = $handler->getActions();
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