<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod\Post;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;

class ChangeAuthor extends AbstractAction
{
    public function getTitle()
    {
        return \XF::phrase('changeContentOwner_change_post_author...');
    }

    protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
    {
        /** @var \TickTackk\ChangeContentOwner\XF\Entity\Post $entity */
        return $entity->canChangeAuthor($error);
    }

    protected function applyToEntity(Entity $entity, array $options)
    {
        $newAuthor = $this->app()->em()->findOne('XF:User', ['username' => $options['new_author_username']]);
        if (!$newAuthor)
        {
            return;
        }

        /** @var \TickTackk\ChangeContentOwner\XF\Service\Post\AuthorChanger $authorChanger */
        $authorChanger = $this->app()->service('TickTackk\ChangeContentOwner\XF:Post\AuthorChanger', $entity, $entity->User, $newAuthor);
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
            'posts' => $entities,
            'total' => count($entities)
        ];
        return $controller->view('XF:Public:InlineMod\Post\ChangeAuthor', 'inline_mod_post_change_author', $viewParams);
    }

    public function getFormOptions(AbstractCollection $entities, Request $request)
    {
        return [
            'new_author_username' => $request->filter('new_author_username', 'str')
        ];
    }
}