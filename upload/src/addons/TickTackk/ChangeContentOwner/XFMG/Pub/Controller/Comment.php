<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use XF\Mvc\ParameterBag;

class Comment extends XFCP_Comment
{
    public function actionChangeAuthor(ParameterBag $params)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\Comment $comment */
        $comment = $this->assertViewableComment($params->comment_id);
        if (!$comment->canChangeAuthor($error))
        {
            return $this->noPermission($error);
        }

        if ($this->isPost())
        {
            $newAuthor = $this->em()->findOne('XF:User', ['username' => $this->filter('new_author_username', 'str')]);
            if (!$newAuthor)
            {
                return $this->error(\XF::phrase('requested_user_not_found'));
            }

            $canTargetView = \XF::asVisitor($newAuthor, function() use ($comment)
            {
                return $comment->canView();
            });
            if (!$canTargetView)
            {
                return $this->error(\XF::phrase('changeContentOwner_new_author_must_be_able_to_view_this_xfmg_comment'));
            }

            /** @var \TickTackk\ChangeContentOwner\XFMG\Service\Comment\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XFMG:Comment\AuthorChanger', $comment, $comment->User, $newAuthor);
            $authorChangerService->changeAuthor();

            return $this->redirect($this->buildLink('media/comments', $comment));
        }
        else
        {
            $content = $comment->Content;

            $viewParams = [
                'comment' => $comment,
                'content' => $content
            ];
            return $this->view('TickTackk\ChangeContentOwner\XFMG:Comment\ChangeAuthor', 'changeContentOwner_xfmg_comment_change_author', $viewParams);
        }
    }
}