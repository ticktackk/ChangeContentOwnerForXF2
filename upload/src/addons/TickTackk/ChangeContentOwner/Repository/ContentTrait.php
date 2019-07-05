<?php

namespace TickTackk\ChangeContentOwner\Repository;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use XF\Mvc\Entity\Entity;

/**
 * Trait ContentTrait
 *
 * @package TickTackk\ChangeContentOwner\Repository
 */
trait ContentTrait
{
    /**
     * @param Entity $content
     * @param bool   $throw
     *
     * @return AbstractHandler
     * @throws \Exception
     */
    public function getChangeOwnerHandler(Entity $content, bool $throw = false) : AbstractHandler
    {
        $contentType = $content->structure()->contentType;
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