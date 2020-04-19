<?php

namespace TickTackk\ChangeContentOwner\Job\Upgrade;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler as AbstractChangeOwnerHandler;
use XF\Entity\Attachment as AttachmentEntity;
use XF\Job\AbstractRebuildJob;
use XF\App as BaseApp;
use XF\Mvc\Entity\Entity;
use XF\Phrase;
use XF\Db\AbstractAdapter as DbAdapter;

/**
 * Class RebuildAttachmentOwner
 *
 * @package TickTackk\ChangeContentOwner\Job\Upgrade
 */
class RebuildAttachmentOwner extends AbstractRebuildJob
{
    /**
     * @var array[]
     */
    protected $defaultData = [
        'content_type' => null
    ];

    /**
     * @param int $start
     * @param int $batch
     *
     * @return array
     */
    protected function getNextIds($start, $batch) : array
    {
        $contentType = $this->getContentType();
        if (!$contentType)
        {
            return [];
        }

        $app = $this->app();
        $entityName = $app->getContentTypeEntity($contentType);
        if (!$entityName)
        {
            return [];
        }
        $structure = $app->em()->getEntityStructure($entityName);
        if (!$structure)
        {
            return [];
        }

        $db = $this->db();
        return $db->fetchAllColumn($db->limit(
            "
				SELECT {$structure->primaryKey}
				FROM {$structure->table}
				WHERE {$structure->primaryKey} > ?
				ORDER BY {$structure->primaryKey}
			", $batch
        ), $start);
    }

    /**
     * @param int $id
     *
     * @throws \XF\Db\Exception
     */
    protected function rebuildById($id) : void
    {
        $contentType = $this->getContentType();
        if (!$contentType)
        {
            return;
        }

        /** @var Entity $content */
        $content = $this->app()->findByContentType($contentType, $id);
        if (!$content)
        {
            return;
        }

        if (!\is_callable([\get_class($content), 'getChangeOwnerHandler']))
        {
            return;
        }

        /** @var AbstractChangeOwnerHandler $changeOwnerHandler */
        $changeOwnerHandler = $content->getChangeOwnerHandler();
        if (!$changeOwnerHandler)
        {
            return;
        }

        // current owner
        $oldUser = $changeOwnerHandler->getOldOwner($content);
        if (!$oldUser)
        {
            return;
        }

        $this->db()->query("
            UPDATE xf_attachment_data AS attachment_data
            INNER JOIN xf_attachment AS attachment
                ON (attachment_data.data_id = attachment.data_id)
            SET attachment_data.user_id = ?
            WHERE attachment.content_type = ? AND content_id = ?
        ", [$oldUser->user_id, $content->getEntityContentType(), $content->getEntityId()]);
    }

    /**
     * @return Phrase
     */
    protected function getStatusType() : Phrase
    {
        return \XF::phrase('attachments');
    }

    /**
     * @return string|null
     */
    protected function getContentType() :? string
    {
        return $this->data['content_type'];
    }

    /**
     * @return BaseApp
     */
    protected function app() : BaseApp
    {
        return $this->app;
    }

    /**
     * @return DbAdapter
     */
    protected function db() : DbAdapter
    {
        return $this->app()->db();
    }
}