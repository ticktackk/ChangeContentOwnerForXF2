<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod\Album;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;

class ChangeOwner extends AbstractAction
{
    public function getTitle()
    {
        return \XF::phrase('changeContentOwner_change_xfmg_album_owner...');
    }

    protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\Album $entity */
        return $entity->canChangeOwner($error);
    }

    protected function applyToEntity(Entity $entity, array $options)
    {
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

    public function getBaseOptions()
    {
        return [
            'new_owner_username' => null
        ];
    }

    public function renderForm(AbstractCollection $entities, \XF\Mvc\Controller $controller)
    {
        $viewParams = [
            'albums' => $entities,
            'total' => count($entities)
        ];
        return $controller->view('XFMG:Public:InlineMod\Album\ChangeOwner', 'inline_mod_xfmg_album_change_owner', $viewParams);
    }

    public function getFormOptions(AbstractCollection $entities, Request $request)
    {
        return [
            'new_owner_username' => $request->filter('new_owner_username', 'str')
        ];
    }
}