<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod\Comment;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;

class ChangeAuthor extends AbstractAction
{
    public function getTitle()
    {
        return \XF::phrase('changeContentOwner_change_xfmg_comment_author...');
    }

    protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\Comment $entity */
        return $entity->canChangeAuthor($error);
    }

    protected function applyToEntity(Entity $entity, array $options)
    {
        $newAuthor = $this->app()->em()->findOne('XF:User', ['username' => $options['new_author_username']]);
        if (!$newAuthor)
        {
            return;
        }

        /** @var \TickTackk\ChangeContentOwner\XFMG\Service\Comment\AuthorChanger $authorChanger */
        $authorChanger = $this->app()->service('TickTackk\ChangeContentOwner\XFMG:Comment\AuthorChanger', $entity, $entity->User, $newAuthor);
        $authorChanger->setPerformValidations(false);
        $authorChanger->changeAuthor();
        if ($authorChanger->validate($errors))
        {
            $authorChanger->save();
        }
    }

    public function getBaseOptions()
    {
        return [
            'new_author_username' => null
        ];
    }

    public function renderForm(AbstractCollection $entities, \XF\Mvc\Controller $controller)
    {
        $viewParams = [
            'comments' => $entities,
            'total' => count($entities)
        ];
        return $controller->view('XFMG:Public:InlineMod\Comment\ChangeAuthor', 'inline_mod_xfmg_comment_change_author', $viewParams);
    }

    public function getFormOptions(AbstractCollection $entities, Request $request)
    {
        return [
            'new_author_username' => $request->filter('new_author_username', 'str')
        ];
    }
}