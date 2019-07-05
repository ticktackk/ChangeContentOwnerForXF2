<?php

namespace TickTackk\ChangeContentOwner\Service\Content;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Repository\ContentTrait as ContentRepoTrait;
use TickTackk\ChangeContentOwner\Repository\ContentInterface as ContentRepoInterface;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Repository\User as UserRepo;
use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;

/**
 * Class AbstractOwnerChanger
 *
 * @package TickTackk\ChangeContentOwner\Service\Content
 */
abstract class AbstractOwnerChanger extends AbstractService
{
    use ValidateAndSavableTrait;

    public const CONTENT_COUNT_CHANGE_TYPE_INCREMENT = 0;

    public const CONTENT_COUNT_CHANGE_TYPE_DECREMENT = 1;

    /**
     * @var array|Entity[]|ArrayCollection|ContentEntityInterface[]
     */
    protected $contents;

    /**
     * @var AbstractHandler
     */
    protected $handler;

    /**
     * @var UserEntity
     */
    protected $newOwner;

    /**
     * @var array|ArrayCollection|UserEntity[]
     */
    protected $oldOwners;

    /**
     * @var array|int[]
     */
    protected $oldDates;

    /**
     * @var int
     */
    protected $newDate;

    /**
     * @var array
     */
    protected $contentCounts;

    /**
     * @var bool
     */
    protected $logModerator;

    /**
     * AbstractChanger constructor.
     *
     * @param \XF\App $app
     * @param array|Entity[]|ArrayCollection|ContentEntityInterface[]|Entity|ContentEntityInterface $contents
     *
     * @throws \Exception
     */
    public function __construct(\XF\App $app, $contents)
    {
        parent::__construct($app);

        if ($contents instanceof Entity)
        {
            $this->setContents(new ArrayCollection([$contents->getEntityId() => $contents]));
        }
        else if (is_array($contents) && !$contents instanceof ArrayCollection)
        {
            $this->setContents(new ArrayCollection($contents));
        }
    }

    /**
     * @param ArrayCollection|ContentEntityInterface $contents
     *
     * @throws \Exception
     */
    protected function setContents(ArrayCollection $contents) : void
    {
        if (!$contents->count())
        {
            throw new \InvalidArgumentException('No contents provided.');
        }

        $emptyEntity = $this->em()->create($this->getEntityIdentifier());
        $emptyEntity->setReadOnly(true);

        /** @var Entity|ContentEntityInterface $content */
        foreach ($contents AS $content)
        {
            if (!$content instanceof $emptyEntity)
            {
                throw new \InvalidArgumentException('Content provided for wrong service.');
            }
        }

        $this->contents = $contents;

        $contentRepo = $this->getContentRepo();
        $this->handler = $contentRepo->getChangeOwnerHandler(true);
    }

    /**
     * @return AbstractHandler
     */
    protected function getHandler() : AbstractHandler
    {
        return $this->handler;
    }

    /**
     * @param UserEntity $newUser
     */
    public function setNewOwner(UserEntity $newUser) : void
    {
        $this->newOwner = $newUser;
    }

    /**
     * @return null|UserEntity
     */
    public function getNewOwner() :? UserEntity
    {
        return $this->newOwner;
    }

    /**
     * @param int $newDate
     */
    public function setNewDate(int $newDate) : void
    {
        $this->newDate = $newDate;
    }

    /**
     * @return null|int
     */
    public function getNewDate() :? int
    {
        return $this->newDate;
    }

    /**
     * @param Entity $content
     *
     * @return string
     */
    protected function getContentUniqueKey(Entity $content) : string
    {
        $contentType = $content->getEntityContentType();
        $contentId = $content->getEntityId();

        return "{$contentType}-{$contentId}";
    }

    /**
     * @param Entity $content
     *
     * @return UserEntity
     */
    public function getOldOwner(Entity $content) : UserEntity
    {
        $uniqueKey = $this->getContentUniqueKey($content);

        if (isset($this->oldOwners[$uniqueKey]))
        {
            return $this->oldOwners[$uniqueKey];
        }

        $this->oldOwners[$uniqueKey] = $this->handler->getOldOwner($content);
        return $this->oldOwners[$uniqueKey];
    }

    /**
     * @param Entity $content
     *
     * @return int
     */
    public function getOldDate(Entity $content) : int
    {
        $uniqueKey = $this->getContentUniqueKey($content);

        if (isset($this->oldDates[$uniqueKey]))
        {
            return $this->oldDates[$uniqueKey];
        }

        $this->oldDates[$uniqueKey] = $this->handler->getOldDate($content);

        return $this->oldDates[$uniqueKey];
    }

    /**
     * @param Entity $content
     *
     * @return array
     */
    public function getLogData(Entity $content) : array
    {
        $actions = [];
        $extraData = [];
        $oldOwner = $this->getOldOwner($content);
        $newOwner = $this->getNewOwner();

        if ($newOwner && $oldOwner->user_id !== $newOwner->user_id)
        {
            $actions[] = 'owner';

            $extraData['old_user_id'] = $oldOwner->user_id;
            $extraData['old_username'] = $oldOwner->username;
            $extraData['new_user_id'] = $newOwner->user_id;
            $extraData['new_username'] = $newOwner->username;
        }

        $oldDate = $this->getOldDate($content);
        $newDate = $this->getNewDate();
        if ($newDate && $oldDate !== $newDate)
        {
            $actions[] = 'date';

            $extraData['old_date'] = $oldDate;
            $extraData['new_date'] = $newDate;
        }

        return [
            'action' => 'change_' . implode('_', $actions),
            'extraData' => $extraData
        ];
    }

    /**
     * @param bool $logModerator
     */
    public function setLogModerator(bool $logModerator = true) : void
    {
        $this->logModerator = $logModerator;
    }

    /**
     * @return bool
     */
    public function getLogModerator() : bool
    {
        return $this->logModerator;
    }

    /**
     * @param Entity|ContentEntityInterface $content
     * @param UserEntity $newOwner
     *
     * @return Entity
     */
    abstract protected function changeContentOwner(Entity $content, UserEntity $newOwner) : Entity;

    /**
     * @param Entity|ContentEntityInterface $content
     * @param int    $newDate
     *
     * @return Entity
     */
    abstract protected function changeContentDate(Entity $content, int $newDate) : Entity;

    /**
     * @param UserEntity $user
     * @param string     $column
     * @param int        $value
     * @param int        $default
     */
    protected function increaseContentCount(UserEntity $user, string $column, int $value = 1, int $default = 0) : void
    {
        $this->changeContentCount(static::CONTENT_COUNT_CHANGE_TYPE_INCREMENT, $user, $column, $value, $default);

    }

    /**
     * @param UserEntity $user
     * @param string     $column
     * @param int        $value
     * @param int        $default
     */
    protected function decreaseContentCount(UserEntity $user, string $column, int $value = 1, int $default = 0) : void
    {
        $this->changeContentCount(static::CONTENT_COUNT_CHANGE_TYPE_DECREMENT, $user, $column, $value, $default);
    }

    /**
     * @param int        $changeType
     * @param UserEntity $user
     * @param string     $column
     * @param int        $value
     * @param int        $default
     */
    protected function changeContentCount(int $changeType, UserEntity $user, string $column, int $value, int $default = 0) : void
    {
        if (!isset($this->userCounts[$user->user_id]))
        {
            $this->contentCounts[$user->user_id][$column] = $default;
        }

        if ($changeType === static::CONTENT_COUNT_CHANGE_TYPE_INCREMENT)
        {
            $this->contentCounts[$user->user_id][$column] += $value;
        }
        else if ($changeType === static::CONTENT_COUNT_CHANGE_TYPE_DECREMENT)
        {
            $this->contentCounts[$user->user_id][$column] -= $value;
        }
        else
        {
            throw new \InvalidArgumentException('Unsupported user count change type provided.');
        }
    }

    /**
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    public function apply() : void
    {
        $db = $this->db();
        $db->beginTransaction();

        $newOwner = $this->getNewOwner();
        $newDate = $this->getNewDate();

        foreach ($this->contents AS $id => $content)
        {
            $oldOwner = $this->getOldOwner($content);
            if ($newOwner && $newOwner->user_id !== $oldOwner->user_id)
            {
                $this->contents[$id] = $this->changeContentOwner($content, $newOwner);
            }

            $oldDate = $this->getOldDate($content);
            if ($newDate && $newDate !== $oldDate)
            {
                $this->contents[$id] = $this->changeContentDate($content, $newDate);
            }

            if ($newOwner || $newDate)
            {
                $this->applyAdditionalChanges($content);
            }
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function _validate() : array
    {
        $newOwner = $this->getNewOwner();
        $handler = $this->getHandler();
        $errors = [];

        if ($newOwner)
        {
            foreach ($this->contents AS $id => $content)
            {
                if (!$handler->canNewOwnerViewContent($content, $newOwner, $error))
                {
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * @param Entity $content
     */
    abstract protected function additionalEntitySave(Entity $content) : void;

    /**
     * @param Entity $content
     */
    protected function postContentSave(Entity $content) : void
    {
    }

    /**
     * @throws \XF\Db\Exception
     */
    protected function applyContentCount() : void
    {
        $db = $this->db();
        foreach ($this->contentCounts AS $userId => $columnAndValue)
        {
            [$column, $value] = $columnAndValue;

            $db->query("
                UPDATE xf_user
                SET {$column} = GREATEST(0, {$column} + ?)
                WHERE user_id = ?
            ", [$value, $userId]);
        }
    }

    /**
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function _save() : void
    {
        $db = $this->db();
        $logger = $this->app->logger();
        $logModerator = $this->getLogModerator();

        foreach ($this->contents AS $id => $content)
        {
            $this->additionalEntitySave($content);

            $content->save(true, false);

            $this->postContentSave($content);

            if ($logModerator)
            {
                [$action, $extraData] = $this->getLogData($content);
                $logger->logModeratorAction($content->getEntityContentType(), $content, $action, $extraData);
            }
        }

        $this->applyContentCount();

        $db->commit();
    }

    /**
     * @param Entity|ContentEntityInterface $content
     *
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function applyAdditionalChanges(Entity $content) : void
    {
        $db = $this->db();
        $structure = $content->structure();

        $oldOwner = $this->getOldOwner($content);
        $newOwner = $this->getNewOwner();
        $newDate = $this->getNewDate();

        if (isset($structure->behaviors['XF:NewsFeedPublishable']))
        {
            if ($newOwner)
            {
                $db->query('
                    UPDATE xf_news_feed
                    SET user_id = ?,
                        username = ?,
                        event_date = IF(action <> ?, ?, event_date)
                    WHERE content_type = ? AND content_id = ? AND user_id = ? AND username = ?
            ', [$newOwner->user_id, $newOwner->username, 'insert', $newDate, $content->getEntityContentType(), $content->getEntityId(), $oldOwner->user_id, $oldOwner->username]);
            }
            else
            {
                $db->query('
                    UPDATE xf_news_feed
                    SET event_date = IF(action <> ?, ?, event_date)
                    WHERE content_type = ? AND content_id = ? AND user_id = ? AND username = ?
            ', ['insert', $newDate, $content->getEntityContentType(), $content->getEntityId(), $oldOwner->user_id, $oldOwner->username]);
            }
        }

        if ($newOwner && isset($structure->behaviors['XF:Reactable']))
        {
            $likedOrReactedContent = $this->getLikedOrReactedContent($content);
            if ($likedOrReactedContent)
            {
                $likedOrReactedContent->delete(true, false);
            }
        }
    }

    /**
     * @param Entity $content
     * @param string $likesRelation
     * @param string $reactionsRelation
     *
     * @return null|Entity
     */
    protected function getLikedOrReactedContent(Entity $content, string $likesRelation = 'Likes', string $reactionsRelation = 'Reactions') :? Entity
    {
        $newOwner = $this->getNewOwner();
        $reactedOrLikedContent = null;
        if ($newOwner)
        {
            $relationName = \XF::$versionId >= 2010010 ? $reactionsRelation : $likesRelation;
            $reactedOrLikedContent = $content->$relationName[$newOwner->user_id] ?? null;
        }

        return $reactedOrLikedContent;
    }

    /**
     * @param Entity $content
     */
    protected function assertContentExtended(Entity $content) : void
    {
        if (!$content instanceof ContentEntityInterface)
        {
            throw new \LogicException('Content entity must implement ContentInterface.');
        }
    }

    /**
     * @return string
     */
    abstract protected function getEntityIdentifier() : string;

    /**
     * @return string
     */
    protected function getRepoIdentifier() : string
    {
        return $this->getEntityIdentifier();
    }

    /**
     * @return Repository|UserRepo
     */
    protected function getUserRepo() : UserRepo
    {
        return \XF::app()->repository('XF:User');
    }

    /**
     * @return Repository|ContentRepoTrait|ContentRepoInterface
     */
    protected function getContentRepo() : Repository
    {
        return $this->repository($this->getRepoIdentifier());
    }
}