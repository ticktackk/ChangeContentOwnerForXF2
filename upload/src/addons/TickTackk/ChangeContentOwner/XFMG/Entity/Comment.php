<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

class Comment extends XFCP_Comment
{
    public function canChangeAuthor(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        $content = $this->Content;

        return $content->hasPermission('changeCommentAuthor');
    }
}