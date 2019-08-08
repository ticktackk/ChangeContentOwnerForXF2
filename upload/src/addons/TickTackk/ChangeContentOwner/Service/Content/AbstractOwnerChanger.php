<?php

namespace TickTackk\ChangeContentOwner\Service\Content;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait as ContentEntityTrait;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
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
    protected $oldTimestamps;

    /**
     * @var array|int[]
     */
    protected $oldDates;

    /**
     * @var array|int[]
     */
    protected $oldTimes;

    /**
     * @var array|int[]
     */
    protected $newDate;

    /**
     * @var array|int[]
     */
    protected $newTime;

    /**
     * @var array|int[]
     */
    protected $timeIntervals;

    /**
     * @var array
     */
    protected $contentCounts;

    /**
     * @var bool
     */
    protected $logModerator;

    /**
     * @var null|array
     */
    protected $contentNewDateMapping;

    /**
     * AbstractOwnerChanger constructor.
     *
     * @param \XF\App   $app
     * @param array|Entity[]|ArrayCollection|ContentEntityInterface[]|Entity|ContentEntityInterface $contents
     * @param bool|null $logModerator
     *
     * @throws \Exception
     */
    public function __construct(\XF\App $app, $contents, bool $logModerator = null)
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
        else
        {
            $this->setContents($contents);
        }

        if ($logModerator === null)
        {
            $logModerator = true;
        }

        $this->setLogModerator($logModerator);
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

        /** @var ContentEntityInterface|ContentEntityTrait $firstContent */
        $firstContent = $contents->first();
        $this->handler = $firstContent->getChangeOwnerHandler(true);
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
     * @param array|int[] $newDate
     */
    public function setNewDate(array $newDate) : void
    {
        if (\count($newDate) !== 3 ||
            !isset($newDate['year'], $newDate['month'], $newDate['day']) ||
            !is_int($newDate['year']) || !is_int($newDate['month']) || !is_int($newDate['day'])
        )
        {
            throw new \InvalidArgumentException('Invalid new date set.');
        }

        if ($newDate['year'] < 1970)
        {
            throw new \InvalidArgumentException('Invalid year provided.');
        }

        if ($newDate['month'] > 12 || $newDate['month'] < 1)
        {
            throw new \InvalidArgumentException('Invalid month provided.');
        }

        $maxDay = cal_days_in_month(CAL_GREGORIAN, $newDate['month'], $newDate['year']);
        if ($newDate['day'] > $maxDay || $newDate['day'] < 1)
        {
            throw new \InvalidArgumentException('Invalid day provided.');
        }

        $this->newDate = $newDate;
    }

    /**
     * @return array|null
     */
    public function getNewDate() :? array
    {
        return $this->newDate;
    }

    /**
     * @param array $newTime
     */
    public function setNewTime(array $newTime) : void
    {
        if (\count($newTime) !== 3 ||
            !isset($newTime['hour'], $newTime['minute'], $newTime['second']) ||
            !is_int($newTime['hour']) || !is_int($newTime['minute']) || !is_int($newTime['second'])
        )
        {
            throw new \InvalidArgumentException('Invalid new time set.');
        }

        if ($newTime['hour'] < 0 || $newTime['hour'] > 23)
        {
            throw new \InvalidArgumentException('Invalid hour provided.');
        }

        if ($newTime['minute'] < 0 || $newTime['hour'] > 59)
        {
            throw new \InvalidArgumentException('Invalid minute provided.');
        }

        if ($newTime['second'] < 0 || $newTime['second'] > 59)
        {
            throw new \InvalidArgumentException('Invalid second provided.');
        }

        $this->newTime = $newTime;
    }

    /**
     * @return array|null
     */
    public function getNewTime() :? array
    {
        return $this->newTime;
    }

    /**
     * @param array|int[] $timeInterval
     */
    public function setTimeInterval(array $timeInterval) : void
    {
        $this->timeIntervals = [
            'hour' => (int) ($timeInterval['hour'] ?? 0),
            'minute' => (int) ($timeInterval['minute'] ?? 0),
            'second' => (int) ($timeInterval['second'] ?? 0)
        ];
    }

    /**
     * @return array|null
     */
    public function getTimeIntervals() : ? array
    {
        return $this->timeIntervals;
    }

    /**
     * @param array|null $units
     *
     * @return int
     */
    protected function convertUnitsToMilliseconds(array $units = null) : int
    {
        $interval = 0;

        if ($units)
        {
            if ($units['hours'])
            {
                $interval += $units['hours'] * 3600;
            }

            if ($units['minutes'])
            {
                $interval += $units['minutes'] * 60;
            }

            if ($units['seconds'])
            {
                $interval += $units['seconds'];
            }
        }

        return $interval;
    }

    /**
     * @return int
     */
    public function getTimeIntervalInMilliseconds() : int
    {
        return $this->convertUnitsToMilliseconds($this->getTimeIntervals());
    }

    /**
     * @param Entity $content
     *
     * @return int|null
     * @throws \Exception
     */
    public function getNewTimestamp(Entity $content) :? int
    {
        $this->contentNewDateMapping = $this->contentNewDateMapping ?? [];

        $uniqueKey = $this->getContentUniqueKey($content);
        if (isset($this->contentNewDateMapping[$uniqueKey]))
        {
            return $this->contentNewDateMapping[$uniqueKey];
        }

        $dateTime = $this->getHandler()->getOldDateTime($content, true);
        $newDate = $this->newDate;
        if ($newDate)
        {
            $dateTime->setDate($newDate['year'], $newDate['month'], $newDate['day']);
        }

        $newTime = $this->getNewTime();
        if ($newTime)
        {
            $dateTime->setTime($newTime['hour'], $newTime['minute'], $newTime['second']);
        }

        $timeIntervals = $this->getTimeIntervals();
        if ($timeIntervals)
        {
            foreach ($timeIntervals AS $unit => $value)
            {
                $multiplyIntervalBy = count($this->contentNewDateMapping);
                if ($multiplyIntervalBy && $value)
                {
                    $dateTime->modify($value * $multiplyIntervalBy. ' ' . $unit);
                }
            }
        }

        $this->contentNewDateMapping[$uniqueKey] = $dateTime->getTimestamp();
        return $this->contentNewDateMapping[$uniqueKey];
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
     * @return array
     * @throws \Exception
     */
    public function getOldDate(Entity $content) : array
    {
        $uniqueKey = $this->getContentUniqueKey($content);

        if (isset($this->oldDates[$uniqueKey]))
        {
            return $this->oldDates[$uniqueKey];
        }

        $this->oldDates[$uniqueKey] = $this->getHandler()->getOldDate($content, true, true);

        return $this->oldDates[$uniqueKey];
    }

    /**
     * @param Entity $content
     *
     * @return array
     * @throws \Exception
     */
    public function getOldTime(Entity $content) : array
    {
        $uniqueKey = $this->getContentUniqueKey($content);

        if (isset($this->oldTimes[$uniqueKey]))
        {
            return $this->oldTimes[$uniqueKey];
        }

        $this->oldTimes[$uniqueKey] = $this->getHandler()->getOldTime($content, true, true);

        return $this->oldTimes[$uniqueKey];
    }

    /**
     * @param Entity $content
     *
     * @return int
     */
    public function getOldTimestamp(Entity $content) : int
    {
        $uniqueKey = $this->getContentUniqueKey($content);

        if (isset($this->oldTimestamps[$uniqueKey]))
        {
            return $this->oldTimestamps[$uniqueKey];
        }

        $this->oldTimestamps[$uniqueKey] = $this->getHandler()->getOldTimestamp($content);

        return $this->oldTimestamps[$uniqueKey];
    }

    /**
     * @param Entity $content
     *
     * @return array
     * @throws \Exception
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

        $oldTimestamp = $this->getOldTimestamp($content);
        $newTimestamp = $this->getNewTimestamp($content);
        if ($newTimestamp !== $oldTimestamp)
        {
            $extraData['old_timestamp'] = $oldTimestamp;

            $newDate = $this->getNewDate();
            if ($newDate)
            {
                $actions[] = 'date';

                $extraData['new_date_provided'] = $newDate;
            }

            $newTime = $this->getNewTime();
            if ($newTime)
            {
                $actions[] = 'time';

                $extraData['new_time_provided'] = $newTime;
            }

            $timeIntervals = $this->getTimeIntervals();
            if ($timeIntervals)
            {
                $isBump = false;
                foreach ([] AS $possibleAction)
                {
                    if (in_array($possibleAction, $actions, true))
                    {
                        $isBump = false;
                        break;
                    }
                }

                if ($isBump)
                {
                    $actions[] = 'bump';
                }

                $extraData['time_intervals'] = $extraData;
            }
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
    public function getLogModerator() :? bool
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
        if (!isset($this->contentCounts[$user->user_id][$column]))
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
     * @return array
     * @throws \Exception
     */
    protected function _validate() : array
    {
        $newOwner = $this->getNewOwner();
        $handler = $this->getHandler();
        $errors = [];

        foreach ($this->contents AS $id => $content)
        {
            $oldTimestamp = $this->getOldTimestamp($content);
            $newTimestamp = $this->getNewTimestamp($content);

            if ($oldTimestamp !== $newTimestamp && !$content->canChangeDate($newTimestamp, $error))
            {
                $fallbackError = count($this->contents) > 1
                    ? 'tckChangeContentOwner_you_do_not_have_permission_to_change_selected_contents_date'
                    : 'tckChangeContentOwner_you_do_not_have_permission_to_change_this_content_date';

                $errors[] = $error ?: \XF::phrase($fallbackError);
            }
            unset($error);

            $oldOwner = $this->getOldOwner($content);
            if ($newOwner && $newOwner->user_id !== $oldOwner->user_id)
            {
                if (!$content->canChangeOwner($newOwner, $error))
                {
                    $fallbackError = count($this->contents) > 1
                        ? 'tckChangeContentOwner_you_do_not_have_permission_to_change_selected_contents_owner'
                        : 'tckChangeContentOwner_you_do_not_have_permission_to_change_this_content_owner';

                    $errors[] = $error ?: \XF::phrase($fallbackError);
                }

                if (!$handler->canNewOwnerViewContent($content, $newOwner, $error))
                {
                    $errors[] = $error ?: \XF::phrase('tckChangeContentOwner_new_owner_must_be_able_to_view_this_content');
                }
            }
        }

        return array_unique($errors);
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
        $contentCounts = $this->contentCounts;
        if ($contentCounts)
        {
            $db = $this->db();
            foreach ($contentCounts AS $userId => $columnAndValue)
            {
                foreach ($columnAndValue AS $column => $value)
                {
                    $db->query("
                    UPDATE xf_user
                    SET {$column} = GREATEST(0, {$column} + ?)
                    WHERE user_id = ?
                ", [$value, $userId]);
                }
            }
        }
    }

    /**
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     * @throws \Exception
     */
    protected function _save() : void
    {
        $db = $this->db();
        $db->beginTransaction();

        $newOwner = $this->getNewOwner();
        foreach ($this->contents AS $id => $content)
        {
            $oldOwner = $this->getOldOwner($content);
            if ($newOwner && $newOwner->user_id !== $oldOwner->user_id)
            {
                $this->contents[$id] = $this->changeContentOwner($content, $newOwner);
            }

            $oldTimestamp = $this->getOldTimestamp($content);
            $newTimestamp = $this->getNewTimestamp($content);
            if ($newTimestamp !== $oldTimestamp)
            {
                $this->contents[$id] = $this->changeContentDate($content, $newTimestamp);
            }

            if ($newOwner || $newTimestamp)
            {
                $this->applyAdditionalChanges($content);
            }
        }

        $logger = $this->app->logger();
        $logModerator = $this->getLogModerator();

        foreach ($this->contents AS $id => $content)
        {
            $this->additionalEntitySave($content);

            $content->save(true, false);

            $this->postContentSave($content);

            if ($logModerator)
            {
                ['action' => $action, 'extraData' => $extraData] = $this->getLogData($content);
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
     * @throws \Exception
     */
    protected function applyAdditionalChanges(Entity $content) : void
    {
        $db = $this->db();
        $structure = $content->structure();

        $oldOwner = $this->getOldOwner($content);
        $newOwner = $this->getNewOwner();
        $newTimestamp = $this->getNewTimestamp($content);

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
            ', [$newOwner->user_id, $newOwner->username, 'insert', $newTimestamp, $content->getEntityContentType(), $content->getEntityId(), $oldOwner->user_id, $oldOwner->username]);
            }
            else
            {
                $db->query('
                    UPDATE xf_news_feed
                    SET event_date = IF(action <> ?, ?, event_date)
                    WHERE content_type = ? AND content_id = ? AND user_id = ? AND username = ?
            ', ['insert', $newTimestamp, $content->getEntityContentType(), $content->getEntityId(), $oldOwner->user_id, $oldOwner->username]);
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
     * @return string
     */
    abstract protected function getEntityIdentifier() : string;
}