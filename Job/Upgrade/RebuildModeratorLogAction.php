<?php

namespace TickTackk\ChangeContentOwner\Job\Upgrade;

use XF\Entity\ModeratorLog as ModeratorLogEntity;
use XF\Job\AbstractRebuildJob;
use XF\App as BaseApp;
use XF\Phrase;

/**
 * Class RebuildModeratorLogAction
 *
 * @package TickTackk\ChangeContentOwner\Job\Upgrade
 */
class RebuildModeratorLogAction extends AbstractRebuildJob
{
    /**
     * @param int $start
     * @param int $batch
     * @return array
     */
    protected function getNextIds($start, $batch)
    {
        $actionRenameMap = $this->getActionRenameMap();
        $actions = \array_keys($actionRenameMap);

        return \array_column(
            $this->app()->finder('XF:ModeratorLog')
                ->where('content_type', ['thread', 'post', 'xfmg_media', 'xfmg_album', 'xfmg_comment', 'graph'])
                ->where('action', $actions)
                ->fetchColumns('moderator_log_id'),
            'moderator_log_id'
        );
    }

    /**
     * @return Phrase
     */
    protected function getStatusType() : Phrase
    {
        return \XF::phrase('moderator_log');
    }

    /**
     * @param int $id
     *
     * @throws \XF\PrintableException
     */
    protected function rebuildById($id) : void
    {
        /** @var ModeratorLogEntity $moderatorLog */
        $moderatorLog = $this->app()->find('XF:ModeratorLog', $id);
        if (!$moderatorLog)
        {
            return;
        }

        $actionRenameMap = $this->getActionRenameMap();
        $renamedAction = $actionRenameMap[$moderatorLog->action] ?? false;
        if ($renamedAction === false)
        {
            return;
        }

        if ($renamedAction === null)
        {
            if (!\count($moderatorLog->action_params))
            {
                $moderatorLog->delete();
            }

            return;
        }

        $moderatorLog->action = $renamedAction;
        $moderatorLog->save();
    }

    /**
     * @return array
     */
    protected function getActionRenameMap() : array
    {
        return [
            'change_' => null,
            'change_bump' => 'change_bmp',
            'change_date_bump' => 'change_date_bmp',
            'change_date_time_bump' => 'change_date_time_bmp',
            'change_owner' => 'change_ownr',
            'change_owner_bump' => 'change_ownr_bmp',
            'change_owner_date' => 'change_ownr_date',
            'change_owner_date_bump' => 'change_ownr_date_bmp',
            'change_owner_date_time' => 'change_ownr_date_time',
            'change_owner_date_time_bump' => 'change_ownr_date_time_bmp',
            'change_owner_time_bump' => 'change_ownr_time_bmp',
            'change_time_bump' => 'change_time_bmp'
        ];
    }

    /**
     * @return BaseApp
     */
    protected function app() : BaseApp
    {
        return $this->app;
    }
}