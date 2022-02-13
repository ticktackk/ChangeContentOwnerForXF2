<?php

namespace TickTackk\ChangeContentOwner;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\App as BaseApp;
use XF\Entity\Option as OptionEntity;
use XF\Job\Manager as JobManager;

/**
 * Class Setup
 *
 * @package TickTackk\ChangeContentOwner
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1() : void
    {
        // thread
        $this->applyGlobalPermission(
            'forum', 'changeThreadOwner',
            'forum', 'manageAnyThread'
        );
        $this->applyGlobalPermission(
            'forum', 'changeThreadDate',
            'forum', 'manageAnyThread'
        );

        // post
        $this->applyGlobalPermission(
            'forum', 'changePostOwner',
            'forum', 'manageAnyThread'
        );
        $this->applyGlobalPermission(
            'forum', 'changePostDate',
            'forum', 'manageAnyThread'
        );

        // profile post
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostOwner',
            'profilePost', 'editAny'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostDate',
            'profilePost', 'editAny'
        );

        // profile post comment
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentOwner',
            'profilePost', 'editAny'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentDate',
            'profilePost', 'editAny'
        );
    }

    public function installStep2() : void
    {
        $addOns = $this->app()->container('addon.cache');
        $xfmgSupport = $addOns['XFMG'] ?? 0 >= 1000070;
        if ($xfmgSupport)
        {
            // media
            $this->applyGlobalPermission(
                'xfmg', 'changeMediaOwner',
                'forum', 'manageAnyThread'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeMediaDate',
                'forum', 'manageAnyThread'
            );

            // album
            $this->applyGlobalPermission(
                'xfmg', 'changeAlbumOwner',
                'forum', 'manageAnyThread'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeAlbumDate',
                'forum', 'manageAnyThread'
            );

            // comments
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentOwner',
                'forum', 'editAny'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentDate',
                'forum', 'editAny'
            );
        }
    }

    public function upgrade2000011Step1() : void
    {
        // thread
        $this->applyGlobalPermission(
            'forum', 'changeThreadOwner',
            'forum', 'changeThreadAuthor'
        );
        $this->applyContentPermission(
            'forum', 'changeThreadOwner',
            'forum', 'changeThreadAuthor'
        );
        $this->applyGlobalPermission(
            'forum', 'changeThreadDate',
            'forum', 'changeThreadOwner'
        );
        $this->applyContentPermission(
            'forum', 'changeThreadDate',
            'forum', 'changeThreadOwner'
        );

        // post
        $this->applyGlobalPermission(
            'forum', 'changePostOwner',
            'forum', 'changePostAuthor'
        );
        $this->applyContentPermission(
            'forum', 'changePostOwner',
            'forum', 'changePostAuthor'
        );
        $this->applyGlobalPermission(
            'forum', 'changePostDate',
            'forum', 'changePostOwner'
        );
        $this->applyContentPermission(
            'forum', 'changePostDate',
            'forum', 'changePostOwner'
        );

        // profile post
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostOwner',
            'profilePost', 'changeProfilePostAuthor'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostDate',
            'profilePost', 'changeProfilePostOwner'
        );

        // profile post comment
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentOwner',
            'profilePost', 'changeProfilePostOwner'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentDate',
            'profilePost', 'changeProfilePostDate'
        );

        $addOns = $this->app()->container('addon.cache');
        $xfmgSupport = $addOns['XFMG'] ?? 0 >= 1000070;
        if ($xfmgSupport)
        {
            // media item
            $this->applyGlobalPermission(
                'xfmg', 'changeMediaDate',
                'xfmg', 'changeMediaOwner'
            );
            $this->applyContentPermission(
                'xfmg', 'changeMediaDate',
                'xfmg', 'changeMediaOwner'
            );

            // album
            $this->applyGlobalPermission(
                'xfmg', 'changeAlbumDate',
                'xfmg', 'changeAlbumOwner'
            );
            $this->applyContentPermission(
                'xfmg', 'changeAlbumDate',
                'xfmg', 'changeAlbumOwner'
            );

            // comment
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentOwner',
                'xfmg', 'changeCommentAuthor'
            );
            $this->applyContentPermission(
                'xfmg', 'changeCommentOwner',
                'xfmg', 'changeCommentAuthor'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentDate',
                'xfmg', 'changeCommentOwner'
            );
            $this->applyContentPermission(
                'xfmg', 'changeCommentDate',
                'xfmg', 'changeCommentOwner'
            );
        }
    }

    public function upgrade2000013Step1() : void
    {
        $this->upgrade2000011Step1();
    }

    /**
     * @throws \XF\PrintableException
     */
    public function upgrade2000013Step2() : void
    {
        /** @var OptionEntity $option */
        $option = $this->app()->find('XF:Option', 'tckChangeContentOwner_defaultNewDateTimeInterval');
        if ($option)
        {
            $optionValue = $option->option_value;
            $seconds = $this->db()->fetchOne('SELECT option_value FROM xf_option WHERE option_id = ?', 'tckChangeContentOwner_timeInterval');
            $optionValue['seconds'] = (int) $seconds;
            $option->option_value = $optionValue;
            $option->save();
        }
    }

    public function upgrade2000270Step1() : void
    {
        $this->jobManager()->enqueueUnique(
            'tckChangeContentOwner-' . __FUNCTION__,
            'TickTackk\ChangeContentOwner:Upgrade\RebuildModeratorLogAction',
            [],
            false
        );
    }

    public function upgrade2000470Step1() : void
    {
        $this->jobManager()->enqueueUnique(
            'tckChangeContentOwner-' . __FUNCTION__,
            'TickTackk\ChangeContentOwner:Upgrade\RebuildThreadUserPostCount',
            [],
            false
        );
    }

    public function upgrade2000670Step1() : void
    {
        $this->jobManager()->enqueueUnique(
            'tckChangeContentOwner-' . __FUNCTION__,
            'TickTackk\ChangeContentOwner:Upgrade\RebuildNewsFeedEntryEventDate',
            [],
            false
        );
    }

    public function upgrade2000770Step1() : void
    {
        $this->jobManager()->enqueueUnique(
            'tckChangeContentOwner-' . __FUNCTION__,
            'TickTackk\ChangeContentOwner:Upgrade\RebuildModeratorLogAction',
            [],
            false
        );
    }

    public function upgrade2001170Step1() : void
    {
        foreach (['thread', 'post', 'xfmg_media', 'xfmg_album', 'xfmg_comment', 'graph'] AS $contentType)
        {
            $this->jobManager()->enqueueUnique(
                'tckChangeContentOwner-' . __FUNCTION__ . '-' . $contentType,
                'TickTackk\ChangeContentOwner:Upgrade\RebuildAttachmentOwner',
                ['content_type' => $contentType],
                false
            );
        }
    }

    /**
     * @since 2.0.14
     *
     * @param array $stepParams
     *
     * @return array|bool
     */
    public function upgrade2001470Step1(array $stepParams)
    {
        $position = !empty($stepParams[0]) ? $stepParams[0] : 0;
        $perPage = 1000;

        $db = $this->db();
        $db->beginTransaction();

        if (!isset($stepData['max']))
        {
            $stepData['max'] = $db->fetchOne("SELECT MAX(thread_id) FROM xf_thread");
        }

        $threadIds = $db->fetchAllColumn($db->limit(
            "
                SELECT DISTINCT post.thread_id
                FROM xf_post AS post
                INNER JOIN xf_thread AS thread
                    ON (post.thread_id = thread.thread_id)
                WHERE post.message_state = 'visible'
                  AND thread.last_post_date < post.post_date
				  AND thread.thread_id > ?
                ORDER BY post.thread_id ASC
			", $perPage
        ), $position);
        if (!$threadIds)
        {
            $db->commit();
            return true;
        }

        $startTime = microtime(true);
        $maxRunTime = $this->app()->config('jobMaxRunTime');

        foreach ($threadIds AS $threadId)
        {
            $position = $threadId;

            $lastPost = $db->fetchRow("
                SELECT post_id, post_date, user_id, username
                FROM xf_post USE INDEX (thread_id_post_date)
                WHERE thread_id = ?
                    AND message_state = 'visible'
                ORDER BY post_date DESC
                LIMIT 1
            ", $threadId);
            if (!$lastPost) // This can be first post as well :)
            {
                continue;
            }

            $db->update('xf_thread', [
                'last_post_id' => (int) $lastPost['post_id'],
                'last_post_date' => (int) $lastPost['post_date'],
                'last_post_user_id' => (int) $lastPost['user_id'],
                'last_post_username' => $lastPost['username'] ?: '-'
            ], 'thread_id = ?', $threadId);

            if ($maxRunTime && microtime(true) - $startTime > $maxRunTime)
            {
                break;
            }
        }

        $db->commit();

        return [
            $position,
            "{$position} / {$stepData['max']}",
            $stepData
        ];
    }

    /**
     * @since 2.0.14
     *
     * @param array $stepParams
     *
     * @return array|bool
     */
    public function upgrade2001470Step2(array $stepParams)
    {
        $position = !empty($stepParams[0]) ? $stepParams[0] : 0;
        $perPage = 1000;

        $db = $this->db();
        $db->beginTransaction();

        if (!isset($stepData['max']))
        {
            $stepData['max'] = $db->fetchOne("SELECT MAX(node_id) FROM xf_forum");
        }

        $nodeIds = $db->fetchAllColumn($db->limit(
            "
                SELECT DISTINCT thread.node_id
                FROM xf_thread AS thread
                INNER JOIN xf_post AS post
                	ON (post.thread_id = thread.thread_id)
                WHERE thread.discussion_state = 'visible'
                  AND post.message_state = 'visible'
                  AND post.post_date > thread.last_post_date
                  AND thread.last_post_id <> post.post_id
                  AND thread.node_id > ?
                ORDER BY thread.node_id
			", $perPage
        ), $position);
        if (!$nodeIds)
        {
            $db->commit();
            return true;
        }

        $startTime = microtime(true);
        $maxRunTime = $this->app()->config('jobMaxRunTime');

        foreach ($nodeIds AS $nodeId)
        {
            $position = $nodeId;

            $lastThread = $db->fetchRow("
                SELECT *
                FROM xf_thread
                WHERE node_id = ?
                    AND discussion_state = 'visible'
                    AND discussion_type <> 'redirect'
                ORDER BY last_post_date DESC
                LIMIT 1
            ", $nodeId);
            if (!$lastThread)
            {
                $lastThread = [
                    'post_id' => 0,
                    'post_date' => 0,
                    'user_id' => 0,
                    'username' => '', // this should be empty string or '-' ?
                    'thread_id' => 0,
                    'title' => '',
                    'prefix_id' => 0
                ];
            }

            $db->update('xf_forum', [
                'last_post_id' => (int) $lastThread['post_id'],
                'last_post_date' => (int) $lastThread['post_date'],
                'last_post_user_id' => (int) $lastThread['user_id'],
                'last_post_username' => $lastThread['username'] ?: '-',
                'last_thread_id' => (int) $lastThread['thread_id'],
                'last_thread_title' => (string) $lastThread['title'],
                'last_thread_prefix_id' => (int) $lastThread['prefix_id']
            ], 'node_id = ?', $nodeId);

            if ($maxRunTime && microtime(true) - $startTime > $maxRunTime)
            {
                break;
            }
        }

        $db->commit();

        return [
            $position,
            "{$position} / {$stepData['max']}",
            $stepData
        ];
    }

    /**
     * @since 2.0.14
     *
     * @param array $stepParams
     *
     * @return array|bool
     */
    public function upgrade2001470Step3(array $stepParams)
    {
        $position = !empty($stepParams[0]) ? $stepParams[0] : 0;
        $perPage = 1000;

        $db = $this->db();
        $db->beginTransaction();

        if (!isset($stepData['max']))
        {
            $stepData['max'] = $db->fetchOne("SELECT MAX(node_id) FROM xf_forum");
        }

        $profilePostIds = $db->fetchAllColumn($db->limit(
            "
                SELECT DISTINCT comment.profile_post_id
                FROM xf_profile_post_comment AS comment
                INNER JOIN xf_profile_post AS profile_post
                	ON (comment.profile_post_id = profile_post.profile_post_id)
                WHERE comment.message_state = 'visible'
                  AND ((profile_post.first_comment_date < comment.comment_date) OR (profile_post.last_comment_date > comment.comment_date))
				  AND comment.profile_post_id > ?
                ORDER BY comment.profile_post_id ASC
			", $perPage
        ), $position);
        if (!$profilePostIds)
        {
            $db->commit();
            return true;
        }

        $startTime = microtime(true);
        $maxRunTime = $this->app()->config('jobMaxRunTime');

        foreach ($profilePostIds AS $profilePostId)
        {
            $position = $profilePostId;

            $firstComment = $db->fetchRow("
                SELECT profile_post_comment_id, comment_date, user_id, username
                FROM xf_profile_post_comment
                WHERE profile_post_id = ?
                    AND message_state = 'visible'
                ORDER BY comment_date 
                LIMIT 1
            ", $profilePostId);
            if ($firstComment)
            {
                $lastComment = $db->fetchRow("
                    SELECT profile_post_comment_id, comment_date, user_id, username
                    FROM xf_profile_post_comment
                    WHERE profile_post_id = ?
                        AND message_state = 'visible'
                    ORDER BY comment_date DESC
                    LIMIT 1
                ", $profilePostId); // this can be the first comment as well ;)
            }
            else
            {
                $firstComment = [
                    'profile_post_comment_id' => 0,
                    'comment_date' => 0,
                    'user_id' => 0,
                    'username' => ''
                ];
                $lastComment = $firstComment;
            }

            $latestCommentIds = [];
            if ($firstComment['profile_post_comment_id'])
            {
                $comments = $db->fetchAllKeyed($db->limit(
                    "
                        SELECT profile_post_id, profile_post_comment_id, message_state, user_id
                        FROM xf_profile_post_comment
                        WHERE profile_post_id = ?
                        ORDER BY comment_date DESC
                    ", 20
                ), 'profile_post_comment_id', $profilePostId);

                $visCount = 0;
                $latestComments = [];

                foreach ($comments AS $commentId => $comment)
                {
                    if ($comment['message_state'] === 'visible')
                    {
                        $visCount++;
                    }

                    $latestComments[$commentId] = [$comment['message_state'], $comment['user_id']];

                    if ($visCount === 3)
                    {
                        break;
                    }
                }

                $latestCommentIds = array_reverse($latestComments, true);
            }

            $db->update('xf_profile_post', [
                'first_comment_date' => (int) $firstComment['profile_post_comment_id'],
                'last_comment_date' => (int) $lastComment['profile_post_comment_id'],
                'latest_comment_ids' => json_encode($latestCommentIds, JSON_PARTIAL_OUTPUT_ON_ERROR)
            ], 'profile_post_id = ?', $profilePostId);

            if ($maxRunTime && microtime(true) - $startTime > $maxRunTime)
            {
                break;
            }
        }

        $db->commit();

        return [
            $position,
            "{$position} / {$stepData['max']}",
            $stepData
        ];
    }

    /**
     * @since 2.0.14
     *
     * @param array $stepParams
     *
     * @return array|bool
     */
    public function upgrade2001470Step4(array $stepParams)
    {
        return $this->rebuildXFMGContentLastComment(
            $stepParams,
            'xf_mg_media_item',
            'media_id',
            'xfmg_media'
        );
    }

    /**
     * @since 2.0.14
     *
     * @param array $stepParams
     *
     * @return array|bool
     */
    public function upgrade2001470Step5(array $stepParams)
    {
        return $this->rebuildXFMGContentLastComment(
            $stepParams,
            'xf_mg_album',
            'album_id',
            'xfmg_album'
        );
    }

    /**
     * @since 2.0.14
     *
     * @param array $stepParams
     * @param string $tableName
     * @param string $primaryKey
     * @param string $contentType
     * @param int $perPage
     *
     * @return array|bool
     */
    protected function rebuildXFMGContentLastComment(
        array $stepParams,
        string $tableName,
        string $primaryKey,
        string $contentType,
        int $perPage = 1000
    )
    {
        $position = !empty($stepParams[0]) ? $stepParams[0] : 0;

        $db = $this->db();
        $db->beginTransaction();

        if (!isset($stepData['max']))
        {
            $stepData['max'] = $db->fetchOne("SELECT MAX({$primaryKey}) FROM {$tableName}");
        }

        $quotedContentType = $db->quote($contentType);

        $contentIds = $db->fetchAllColumn($db->limit(
            "
                SELECT DISTINCT comment.content_id
                FROM xf_mg_comment AS comment
                INNER JOIN {$tableName} AS content
                   ON (content.{$primaryKey} = comment.content_id AND comment.content_type = {$quotedContentType})
                WHERE comment.content_type = {$quotedContentType}
                   AND comment.content_id > ?
                   AND comment.comment_state = 'visible'
                   AND content.last_comment_date < comment.comment_date
                ORDER BY comment.content_id ASC
			", $perPage
        ), $position);
        if (!$contentIds)
        {
            $db->commit();
            return true;
        }

        $startTime = microtime(true);
        $maxRunTime = $this->app()->config('jobMaxRunTime');

        foreach ($contentIds AS $contentId)
        {
            $position = $contentId;
            $lastComment = $db->fetchRow("
                SELECT comment_id AS last_comment_id,
                       comment_date AS last_comment_date,
                       user_id AS last_comment_user_id,
                       username AS last_comment_username
                FROM xf_mg_comment
                WHERE content_id = ?
                  AND content_type = ?
                  AND comment_state = 'visible'
                ORDER BY comment_date DESC
                LIMIT 1
            ", [$contentId, $contentType]);
            if (!$lastComment)
            {
                $lastComment = [
                    'comment_id' => 0,
                    'comment_date' => 0,
                    'user_id' => 0,
                    'username' => ''
                ];
            }

            $db->update($tableName, $lastComment, "{$primaryKey} = ?", $contentId);

            if ($maxRunTime && microtime(true) - $startTime > $maxRunTime)
            {
                break;
            }
        }

        $db->commit();

        return [
            $position,
            "{$position} / {$stepData['max']}",
            $stepData
        ];
    }

    /**
     * @param array $errors
     * @param array $warnings
     */
    public function checkRequirements(&$errors = [], &$warnings = []) : void
    {
        $xfaCSVGrapher = $this->app()->addOnManager()->getById('XFA/CSVGrapher');
        if ($xfaCSVGrapher)
        {
            $installed = $xfaCSVGrapher->getInstalledAddOn();
            if ($installed && $installed->version_id < 904000019)
            {
                $warnings[] = 'You must upgrade to [XFA] Datalogger 4.0.0 Alpha 9 or later.';
            }
        }
    }

    /**
     * @return JobManager
     */
    protected function jobManager() : JobManager
    {
        return $this->app()->jobManager();
    }

    /**
     * @return BaseApp
     */
    protected function app() : BaseApp
    {
        if (!\is_callable('parent::app'))
        {
            return $this->app;
        }

        return parent::app();
    }
}