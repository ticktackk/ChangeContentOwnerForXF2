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
     * @param int $newDate
     */
    public function setNewDate(int $newDate) : void
    {
        $this->assertOwnerChangerHasBeenSetup();
        $this->ownerChanger->setNewDate($newDate);
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
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    public function applyOwnerChanger() : void
    {
        if ($this->ownerChanger)
        {
            $this->ownerChanger->apply();
        }
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
            $this->ownerChanger->validate($errors);
        }

        return $errors;
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    /**
     * @return Entity
     * @throws \XF\PrintableException
     */
    protected function _save()
    {
        /** @var AbstractAdapter $db */
        $db = $this->db();
        $db->beginTransaction();

        if ($this->ownerChanger)
        {
            $this->ownerChanger->save();
        }

        /** @noinspection PhpUndefinedClassInspection */
        $content = parent::_save();

        $db->commit();

        return $content;
    }
}