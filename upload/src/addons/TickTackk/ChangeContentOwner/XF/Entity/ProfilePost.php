<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

class ProfilePost extends XFCP_ProfilePost
{
    public function canChangeAuthor(&$error = null)
    {
        $visitor = \XF::visitor();

        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasPermission('profilePost', 'changeProfilePostAuthor');
    }
}