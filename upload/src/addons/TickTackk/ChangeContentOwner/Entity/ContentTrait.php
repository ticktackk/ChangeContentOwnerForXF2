<?php

namespace TickTackk\ChangeContentOwner\Entity;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;

trait ContentTrait
{
    /**
     * @param bool $throw
     *
     * @return AbstractHandler
     * @throws \Exception
     */
    public function getChangeOwnerHandler(bool $throw = false) : AbstractHandler
    {
        $contentType = $this->getEntityContentType();
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