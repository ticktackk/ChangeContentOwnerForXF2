<?php

namespace TickTackk\ChangeContentOwner\XF\Service\ProfilePost;

use XF\Service\AbstractService;
use XF\Entity\ProfilePost;
use XF\Entity\User;

class AuthorChanger extends AbstractService
{
    use \XF\Service\ValidateAndSavableTrait;

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

    /**
     * AuthorChanger constructor.
     * @param \XF\App $app
     * @param ProfilePost $profilePost
     * @param User $profileUser
     * @param User $oldAuthor
     * @param User $newAuthor
     */
    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, ProfilePost $profilePost, User $profileUser, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);
        $this->profilePost = $profilePost;
        $this->profileUser = $profileUser;
        $this->oldAuthor = $oldAuthor;
        $this->newAuthor = $newAuthor;
    }

    public function setPerformValidations($perform)
    {
        $this->performValidations = (bool)$perform;
    }

    /**
     * @return bool
     */
    public function getPerformValidations()
    {
        return $this->performValidations;
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

        $profilePost->user_id = $newAuthor->user_id;
        $profilePost->username = $newAuthor->username;
    }

    protected function finalSetup()
    {
    }

    protected function _validate()
    {
        $this->finalSetup();

        $newAuthor = $this->getNewAuthor();
        $profilePost = $this->getProfilePost();

        $profilePost->preSave();
        $errors = $profilePost->getErrors();

        if ($this->performValidations)
        {
            $canTargetView = \XF::asVisitor($newAuthor, function() use ($profilePost)
            {
                return $profilePost->canView();
            });

            if (!$canTargetView)
            {
                $errors[] = \XF::phraseDeferred('changeContentOwner_new_author_must_be_able_to_view_this_profile_post');
            }
        }

        return $errors;
    }

    protected function _save()
    {
        $profilePost = $this->getProfilePost();

        $db = $this->db();
        $db->beginTransaction();

        $profilePost->save();

        if ($profilePost->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('profile_post', $profilePost, 'author_change');
        }

        $db->commit();

        return $profilePost;
    }
}