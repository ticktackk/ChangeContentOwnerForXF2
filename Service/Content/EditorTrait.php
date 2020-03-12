<?php

namespace TickTackk\ChangeContentOwner\Service\Content;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use XF\Db\AbstractAdapter;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;

/**
 * Trait EditorTrait
 *
 * @package TickTackk\ChangeContentOwner\Service\Content
 */
trait EditorTrait
{
    /**
     * @var AbstractOwnerChanger
     */
    protected $ownerChanger;

    /**
     * @param UserEntity $newOwner
     */
    public function setNewOwner(UserEntity $newOwner) : void
    {
        $this->assertOwnerChangerHasBeenSetup();
        $this->ownerChanger->setNewOwner($newOwner);
    }

    /**
     * @param array $newDate
     */
    public function setNewDate(array $newDate) : void
    {
        $this->assertOwnerChangerHasBeenSetup();
        $this->ownerChanger->setNewDate($newDate);
    }

    /**
     * @param array $newTime
     */
    public function setNewTime(array $newTime) : void
    {
        $this->assertOwnerChangerHasBeenSetup();;
        $this->ownerChanger->setNewTime($newTime);
    }

    /**
     * @param array $timeInterval
     */
    public function setTimeInterval(array $timeInterval) : void
    {
        $this->assertOwnerChangerHasBeenSetup();;
        $this->ownerChanger->setTimeInterval($timeInterval);
    }

    /**
     * @return string
     */
    abstract protected function getOwnerChangerServiceName() : string;

    /**
     * @return Entity|ContentEntityInterface
     */
    abstract protected function getContentForOwnerChangerSvc() : ContentEntityInterface;

    public function setupOwnerChanger() : void
    {
        $this->ownerChanger = $this->service($this->getOwnerChangerServiceName(), $this->getContentForOwnerChangerSvc());
    }

    /**
     * @return AbstractOwnerChanger
     */
    public function getOwnerChangerSvc() : AbstractOwnerChanger
    {
        $this->assertOwnerChangerHasBeenSetup();
        return $this->ownerChanger;
    }

    /**
     * @throws \LogicException
     */
    protected function assertOwnerChangerHasBeenSetup() : void
    {
        if (!$this->ownerChanger)
        {
            throw new \LogicException('Please setup owner changer service before setting new owner or date.');
        }
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    /**
     * @return array
     */
    protected function _validate()
    {
        /** @noinspection PhpUndefinedClassInspection */
        $errors = parent::_validate();

        if ($this->ownerChanger)
        {
            $this->ownerChanger->validate($ownerChangerErrors);

            $errors = array_merge($errors, $ownerChangerErrors);
        }

        return $errors;
    }

    /**
     * @return Entity
     * @throws \XF\PrintableException
     */
    protected function _save()
    {
        /** @var AbstractAdapter $db */
        $db = $this->db();
        $db->beginTransaction();

        /** @noinspection PhpUndefinedClassInspection */
        $content = parent::_save();

        if ($this->ownerChanger)
        {
            $this->ownerChanger->save();
        }

        $db->commit();

        return $content;
    }
}