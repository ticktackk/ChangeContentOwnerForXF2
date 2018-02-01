<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Post extends XFCP_Post
{
    public function actionChangeAuthor(ParameterBag $params)
    {
        /** @var \TickTackk\ChangeContentOwner\XF\Entity\Post $post */
        $post = $this->assertViewablePost($params->post_id, ['Thread.Prefix']);
        if (!$post->canChangeAuthor($error))
        {
            return $this->noPermission($error);
        }

        $thread = $post->Thread;

        if ($this->isPost())
        {
            $newAuthor = $this->em()->findOne('XF:User', ['username' => $this->filter('new_author_username', 'str')]);
            if (!$newAuthor)
            {
                return $this->error(\XF::phrase('requested_user_not_found'));
            }

            $canTargetView = \XF::asVisitor($newAuthor, function() use ($post)
            {
                return $post->canView();
            });
            if (!$canTargetView)
            {
                return $this->error(\XF::phrase('changeContentOwner_new_author_must_be_able_to_view_this_post'));
            }

            /** @var \TickTackk\ChangeContentOwner\XF\Service\Post\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XF:Post\AuthorChanger', $thread, $post, $thread->Forum, $post->User, $newAuthor);
            $authorChangerService->changeAuthor();

            return $this->redirect($this->buildLink('threads', $thread));
        }
        else
        {
            /** @var \XF\Entity\Forum $forum */
            $forum = $post->Thread->Forum;

            $viewParams = [
                'post' => $post,
                'thread' => $thread,
                'forum' => $forum
            ];
            return $this->view('TickTackk\ChangeContentOwner\XF:Post\ChangeAuthor', 'changeContentOwner_post_change_author', $viewParams);
        }
    }
}