<?php

namespace TickTackk\ChangeContentOwner\Job\Upgrade;

use XF\Entity\NewsFeed as NewsFeedEntity;
use XF\Entity\ReactionContent as ReactionContentEntity;
use XF\Entity\User as UserEntity;
use XF\Job\AbstractRebuildJob;
use XF\App as BaseApp;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Finder;
use XF\Phrase;
use XF\Util\Php as PhpUtil;

/**
 * Class RebuildNewsFeedEntryEventDate
 *
 * @package TickTackk\ChangeContentOwner\Job\Upgrade
 */
class RebuildNewsFeedEntryEventDate extends AbstractRebuildJob
{
    /**
     * @param int $start
     * @param int $batch
     *
     * @return array
     */
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        $contentTypesQuoted = $db->quote(\array_keys($this->getChangeOwnerHandlers()));
        $actionsQuoted = $db->quote(['insert', 'like', 'reaction']);

        return $db->fetchAllColumn($db->limit(
            "
				SELECT news_feed_id
				FROM xf_news_feed
				WHERE news_feed_id > ?
				  AND content_type IN ({$contentTypesQuoted})
				  AND action IN ({$actionsQuoted})
				ORDER BY news_feed_id
			", $batch
        ), $start);
    }

    /**
     * @return Phrase
     */
    protected function getStatusType() : Phrase
    {
        return \XF::phrase('news_feed');
    }

    /**
     * @param int $id
     */
    protected function rebuildById($id) : void
    {
        /** @var NewsFeedEntity $newsFeed */
        $newsFeed = $this->app()->find('XF:NewsFeed', $id);
        if (!$newsFeed)
        {
            return;
        }

        $callbackMethod = 'rebuildEventDate' . PhpUtil::camelCase($newsFeed->action);
        if (!\method_exists($this, $callbackMethod))
        {
            return;
        }

        \call_user_func_array([$this, $callbackMethod], [$newsFeed]);

        $newsFeed->saveIfChanged();
    }

    /**
     * @param NewsFeedEntity $newsFeed
     */
    public function rebuildEventDateInsert(NewsFeedEntity $newsFeed) : void
    {
        $content = $this->getContentFromNewsFeed($newsFeed);
        if (!$content)
        {
            return;
        }

        $contentEntityStructure = $content->structure();
        $behaviorConfig = $contentEntityStructure->behaviors['XF:NewsFeedPublishable'] ?? null;
        if (!$behaviorConfig)
        {
            return;
        }

        $dateField = $behaviorConfig['dateField'];
        if (!$dateField)
        {
            return;
        }

        if ($dateField instanceof \Closure)
        {
            $date = $dateField($content);
        }
        else
        {
            $date = $content->getValue($dateField);
        }

        if (!$date) // just to be sure
        {
            return;
        }

        $newsFeed->event_date = $date;
    }

    /**
     * @param NewsFeedEntity $newsFeed
     */
    protected function rebuildReactionOrLike(NewsFeedEntity $newsFeed) : void
    {
        $supportsReaction = $this->supportsReaction();
        $finder = $supportsReaction ? $this->reactionContentFinder() : $this->likeContentFinder();

        $content = $this->getContentFromNewsFeed($newsFeed);
        if (!$content)
        {
            return;
        }

        $this->applyReactionOrLikeContentConditions($finder, $content, $newsFeed->User ?: $newsFeed->user_id);

        /** @var ReactionContentEntity $likeContent */
        $likeContent = $finder->fetchOne();
        if (!$likeContent)
        {
            return;
        }

        $newsFeed->event_date = $likeContent->getValue($supportsReaction ? 'reaction_date' : 'like_date');
    }

    /**
     * @param NewsFeedEntity $newsFeed
     */
    public function rebuildEventDateLike(NewsFeedEntity $newsFeed) : void
    {
        $this->rebuildReactionOrLike($newsFeed);
    }

    /**
     * @param NewsFeedEntity $newsFeed
     */
    public function rebuildEventDateReaction(NewsFeedEntity $newsFeed) : void
    {
        $this->rebuildReactionOrLike($newsFeed);
    }

    /**
     * @return BaseApp
     */
    protected function app() : BaseApp
    {
        return $this->app;
    }

    /**
     * @param string $identifier
     *
     * @return Finder
     */
    protected function finder(string $identifier) : Finder
    {
        return $this->app()->finder($identifier);
    }

    /**
     * @return Finder
     */
    protected function reactionContentFinder() : Finder
    {
        return $this->app()->finder('XF:ReactionContent');
    }

    /**
     * @return Finder
     */
    protected function likeContentFinder() : Finder
    {
        return $this->finder('XF:LikedContent');
    }

    /**
     * @param Finder $finder
     * @param Entity $content
     * @param int|UserEntity $userId
     */
    protected function applyReactionOrLikeContentConditions(Finder $finder, Entity $content, $userId) : void
    {
        if ($userId instanceof UserEntity)
        {
            $this->applyReactionOrLikeContentConditions($finder, $content, $userId->user_id);
            return;
        }

        if (!$userId)
        {
            $finder->whereImpossible();
            return;
        }

        $finder->where('content_type', $content->getEntityContentType())
            ->where('content_id', $content->getEntityId())
            ->where($this->getUserIdColumnForFinder(), $userId)
            ->fetchOne();
    }

    /**
     * @return string
     */
    protected function getUserIdColumnForFinder() : string
    {
        if (!$this->supportsReaction())
        {
            return 'like_user_id';
        }

        return 'reaction_user_id';
    }

    /**
     * @param NewsFeedEntity $newsFeed
     *
     * @return Entity|null
     */
    protected function getContentFromNewsFeed(NewsFeedEntity $newsFeed) :? Entity
    {
        return $this->app()->findByContentType($newsFeed->content_type, $newsFeed->content_id);
    }

    /**
     * @return array
     */
    protected function getChangeOwnerHandlers() : array
    {
        return $this->app()->getContentTypeField('change_owner_handler_class');
    }

    /**
     * @return bool
     */
    protected function supportsReaction() : bool
    {
        return \XF::$versionId >= 2010010;
    }
}