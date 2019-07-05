<?php

namespace TickTackk\ChangeContentOwner\Repository;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;

/**
 * Trait ContentTrait
 *
 * @package TickTackk\ChangeContentOwner\Repository
 */
trait ContentTrait
{
    /**
     * @return string
     */
    protected function getIdentifierForChangeOwner() : string
    {
        return $this->identifier;
    }

    /**
     * @param bool $throw
     *
     * @return AbstractHandler
     * @throws \Exception
     */
    public function getChangeOwnerHandler(bool $throw = false) : AbstractHandler
    {
        /** @var \XF\App $app */
        $app = $this->app();

        $entityStructure = $app->em()->getEntityStructure($this->getIdentifierForChangeOwner());
        $contentType = $entityStructure->contentType;

        $handlerClass = \XF::app()->getContentTypeFieldValue($contentType, 'change_owner_handler_class');
        if (!$handlerClass)
        {
            if ($throw)
            {
                throw new \InvalidArgumentException("No Change Owner handler for '{$contentType}'");
            }
            return null;
        }

        if (!class_exists($handlerClass))
        {
            if ($throw)
            {
                throw new \InvalidArgumentException("Change Owner handler for '{$contentType}' does not exist: $handlerClass");
            }
            return null;
        }

        $handlerClass = \XF::extendClass($handlerClass);
        return new $handlerClass($contentType);
    }
}