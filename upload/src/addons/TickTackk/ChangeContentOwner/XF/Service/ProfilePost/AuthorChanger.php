<?php

namespace TickTackk\ChangeContentOwner\XF\Service\ProfilePost;

use XF\Service\AbstractService;
use XF\Entity\ProfilePost;
use XF\Entity\User;

class AuthorChanger extends AbstractService
{
    /**
     * @var ProfilePost
     */
    protected $profilePost;

    /**
     * @var User
     */
    protected $profileUser;

    /**
     * @var User
     */
    protected $newAuthor;

    /**
     * @var User
     */
    protected $oldAuthor;

    protected $performValidations = true;

    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, ProfilePost $profilePost, User $profileUser, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);
        $this->profilePost = $profilePost;
        $this->profileUser = $profileUser;
        $this->oldAuthor = $oldAuthor;
        $this->newAuthor = $newAuthor;
    }

    public function getProfilePost()
    {
        return $this->profilePost;
    }

    public function getProfileUser()
    {
        return $this->profileUser;
    }

    public function getNewAuthor()
    {
        return $this->newAuthor;
    }

    public function getOldAuthor()
    {
        return $this->oldAuthor;
    }

    public function changeAuthor()
    {
        $newAuthor = $this->getNewAuthor();
        $profilePost = $this->getProfilePost();

        $db = $this->db();
        $db->beginTransaction();

        $profilePost->user_id = $newAuthor->user_id;
        $profilePost->username = $newAuthor->username;

        if (!$profilePost->preSave())
        {
            throw new \XF\PrintableException($profilePost->getErrors());
        }
        $profilePost->save();

        if ($profilePost->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('profile_post', $profilePost, 'author_change');
        }

        $db->commit();
    }
}