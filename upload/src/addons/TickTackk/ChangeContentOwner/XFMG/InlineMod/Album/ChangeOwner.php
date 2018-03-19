<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod\Album;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Controller;

class ChangeOwner extends AbstractAction
{
    /**
     * @return \XF\Phrase
     */
    public function getTitle()
    {
        return \XF::phrase('changeContentOwner_change_xfmg_album_owner...');
    }

    /**
     * @param Entity $entity
     * @param array $options
     * @param null $error
     *
     * @return bool
     */
    protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\Album $entity */
        return $entity->canChangeOwner($error);
    }

    /**
     * @param Entity $entity
     * @param array $options
     */
    protected function applyToEntity(Entity $entity, array $options)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\Album $entity */
        $newOwner = $this->app()->em()->findOne('XF:User', ['username' => $options['new_owner_username']]);
        if (!$newOwner)
        {
            return;
        }

        /** @var \TickTackk\ChangeContentOwner\XFMG\Service\Album\OwnerChanger $ownerChanger */
        $ownerChanger = $this->app()->service('TickTackk\ChangeContentOwner\XFMG:Album\OwnerChanger', $entity, $entity->User, $newOwner);
        $ownerChanger->setPerformValidations(false);
        $ownerChanger->changeOwner();
        if ($ownerChanger->validate($errors))
        {
            $ownerChanger->save();
        }
    }

    /**
     * @return array
     */
    public function getBaseOptions()
    {
        return [
            'new_owner_username' => null
        ];
    }

    /**
     * @param AbstractCollection $entities
     * @param Controller $controller
     *
     * @return \XF\Mvc\Reply\View
     */
    public function renderForm(AbstractCollection $entities, Controller $controller)
    {
        $viewParams = [
            'albums' => $entities,
            'total' => count($entities)
        ];
        return $controller->view('XFMG:Public:InlineMod\Album\ChangeOwner', 'inline_mod_xfmg_album_change_owner', $viewParams);
    }

    /**
     * @param AbstractCollection $entities
     * @param Request $request
     *
     * @return array
     */
    public function getFormOptions(AbstractCollection $entities, Request $request)
    {
        return [
            'new_owner_username' => $request->filter('new_owner_username', 'str')
        ];
    }
}