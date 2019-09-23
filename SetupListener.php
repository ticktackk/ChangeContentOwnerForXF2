<?php

namespace TickTackk\ChangeContentOwner;

use XF\AddOn\AddOn;
use XF\Entity\AddOn as AddOnEntity;

/**
 * Class SetupListener
 *
 * @package TickTackk\ChangeContentOwner
 */
class SetupListener
{
    /**
     * @param AddOn       $addOn
     * @param AddOnEntity $installedAddOn
     * @param array       $json
     * @param array       $stateChanges
     */
    public static function addOnPostInstall(/** @noinspection PhpUnusedParameterInspection */AddOn $addOn, AddOnEntity $installedAddOn, array $json, array &$stateChanges) : void
    {
        /** @noinspection DegradedSwitchInspection */
        switch ($addOn->getAddOnId())
        {
            case 'XFMG':
                $setup = new Setup($addOn, $installedAddOn->app());
                $setup->installStep2();
                break;
        }
    }
}