<?php

namespace TickTackk\ChangeContentOwner;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Entity\Option as OptionEntity;

/**
 * Class Setup
 *
 * @package TickTackk\ChangeContentOwner
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1() : void
    {
        // thread
        $this->applyGlobalPermission(
            'forum', 'changeThreadOwner',
            'forum', 'manageAnyThread'
        );
        $this->applyGlobalPermission(
            'forum', 'changeThreadDate',
            'forum', 'manageAnyThread'
        );

        // post
        $this->applyGlobalPermission(
            'forum', 'changePostOwner',
            'forum', 'manageAnyThread'
        );
        $this->applyGlobalPermission(
            'forum', 'changePostDate',
            'forum', 'manageAnyThread'
        );

        // profile post
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostOwner',
            'profilePost', 'editAny'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostDate',
            'profilePost', 'editAny'
        );

        // profile post comment
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentOwner',
            'profilePost', 'editAny'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentDate',
            'profilePost', 'editAny'
        );
    }

    public function installStep2() : void
    {
        $addOns = $this->app->container('addon.cache');
        $xfmgSupport = $addOns['XFMG'] ?? 0 >= 1000070;
        if ($xfmgSupport)
        {
            // media
            $this->applyGlobalPermission(
                'xfmg', 'changeMediaOwner',
                'forum', 'manageAnyThread'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeMediaDate',
                'forum', 'manageAnyThread'
            );

            // album
            $this->applyGlobalPermission(
                'xfmg', 'changeAlbumOwner',
                'forum', 'manageAnyThread'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeAlbumDate',
                'forum', 'manageAnyThread'
            );

            // comments
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentOwner',
                'forum', 'editAny'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentDate',
                'forum', 'editAny'
            );
        }
    }

    public function upgrade2000011Step1() : void
    {
        // thread
        $this->applyGlobalPermission(
            'forum', 'changeThreadOwner',
            'forum', 'changeThreadAuthor'
        );
        $this->applyContentPermission(
            'forum', 'changeThreadOwner',
            'forum', 'changeThreadAuthor'
        );
        $this->applyGlobalPermission(
            'forum', 'changeThreadDate',
            'forum', 'changeThreadOwner'
        );
        $this->applyContentPermission(
            'forum', 'changeThreadDate',
            'forum', 'changeThreadOwner'
        );

        // post
        $this->applyGlobalPermission(
            'forum', 'changePostOwner',
            'forum', 'changePostAuthor'
        );
        $this->applyContentPermission(
            'forum', 'changePostOwner',
            'forum', 'changePostAuthor'
        );
        $this->applyGlobalPermission(
            'forum', 'changePostDate',
            'forum', 'changePostOwner'
        );
        $this->applyContentPermission(
            'forum', 'changePostDate',
            'forum', 'changePostOwner'
        );

        // profile post
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostOwner',
            'profilePost', 'changeProfilePostAuthor'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeProfilePostDate',
            'profilePost', 'changeProfilePostOwner'
        );

        // profile post comment
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentOwner',
            'profilePost', 'changeProfilePostOwner'
        );
        $this->applyGlobalPermission(
            'profilePost', 'changeCommentDate',
            'profilePost', 'changeProfilePostDate'
        );

        $addOns = $this->app->container('addon.cache');
        $xfmgSupport = $addOns['XFMG'] ?? 0 >= 1000070;
        if ($xfmgSupport)
        {
            // media item
            $this->applyGlobalPermission(
                'xfmg', 'changeMediaDate',
                'xfmg', 'changeMediaOwner'
            );
            $this->applyContentPermission(
                'xfmg', 'changeMediaDate',
                'xfmg', 'changeMediaOwner'
            );

            // album
            $this->applyGlobalPermission(
                'xfmg', 'changeAlbumDate',
                'xfmg', 'changeAlbumOwner'
            );
            $this->applyContentPermission(
                'xfmg', 'changeAlbumDate',
                'xfmg', 'changeAlbumOwner'
            );

            // comment
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentOwner',
                'xfmg', 'changeCommentAuthor'
            );
            $this->applyContentPermission(
                'xfmg', 'changeCommentOwner',
                'xfmg', 'changeCommentAuthor'
            );
            $this->applyGlobalPermission(
                'xfmg', 'changeCommentDate',
                'xfmg', 'changeCommentOwner'
            );
            $this->applyContentPermission(
                'xfmg', 'changeCommentDate',
                'xfmg', 'changeCommentOwner'
            );
        }
    }

    public function upgrade2000013Step1() : void
    {
        $this->upgrade2000011Step1();
    }

    /**
     * @throws \XF\PrintableException
     */
    public function upgrade2000013Step2() : void
    {
        /** @var OptionEntity $option */
        $option = $this->app->find('XF:Option', 'tckChangeContentOwner_defaultNewDateTimeInterval');
        if ($option)
        {
            $optionValue = $option->option_value;
            $seconds = $this->db()->fetchOne('SELECT option_value FROM xf_option WHERE option_id = ?', 'tckChangeContentOwner_timeInterval');
            $optionValue['seconds'] = (int) $seconds;
            $option->option_value = $optionValue;
            $option->save();
        }
    }

    /**
     * @param array $errors
     * @param array $warnings
     */
    public function checkRequirements(&$errors = [], &$warnings = [])
    {
        $xfaCSVGrapher = $this->app()->addOnManager()->getById('XFA/CSVGrapher');
        if ($xfaCSVGrapher)
        {
            $installed = $xfaCSVGrapher->getInstalledAddOn();
            if ($installed && $installed->version_id < 904000019)
            {
                $warnings[] = 'You must upgrade to [XFA] Datalogger 4.0.0 Alpha 9 or later.';
            }
        }
    }
}