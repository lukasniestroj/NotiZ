<?php
declare(strict_types=1);

/*
 * Copyright (C)
 * Nathan Boiron <nathan.boiron@gmail.com>
 * Romain Canon <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 NotiZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CuyZ\Notiz\Service\Extension;

use CuyZ\Notiz\Backend\Module\ManagerModuleHandler;
use CuyZ\Notiz\Backend\Report\NotificationStatus;
use CuyZ\Notiz\Core\Support\NotizConstants;
use CuyZ\Notiz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * This class replaces the old-school procedural way of handling configuration
 * in `ext_tables.php` file.
 *
 * @internal
 */
class TablesConfigurationService implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * @var string
     */
    protected $extensionKey = NotizConstants::EXTENSION_KEY;

    /**
     * Main processing methods that will call every method of this class.
     */
    public static function process()
    {
        self::registerBackendModule();
        self::registerEntityNotificationControllers();
        self::registerReportStatus();
    }

    /**
     * Registers the main backend module used to display notifications,
     * definition and more.
     */
    protected static function registerBackendModule()
    {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
            'notiz',
            '',
            '',
            null,
            [
                'iconIdentifier' => 'tx-notiz-icon-main-module',
                'labels' => "LLL:EXT:" . NotizConstants::EXTENSION_KEY . "/Resources/Private/Language/Backend/Module/Main/Main.xlf",
            ]
        );

        ExtensionUtility::registerModule(
            'Notiz',
            'notiz',
            'notiz_manager',
            '',
            [
                \CuyZ\Notiz\Controller\Backend\Manager\ListNotificationTypesController::class => 'process',
                \CuyZ\Notiz\Controller\Backend\Manager\ListNotificationsController::class => 'process',
                \CuyZ\Notiz\Controller\Backend\Manager\NotificationActivationController::class => 'process',
                \CuyZ\Notiz\Controller\Backend\Manager\ListEventsController::class => 'process',
                \CuyZ\Notiz\Controller\Backend\Manager\ShowEventController::class => 'process',
            ],
            [
                'access' => 'user,group',
                'icon' => NotizConstants::EXTENSION_ICON_PATH_MODULE_MANAGER,
                'labels' => "LLL:EXT:" . NotizConstants::EXTENSION_KEY . "/Resources/Private/Language/Backend/Module/Manager/Manager.xlf",
            ]
        );

        ExtensionUtility::registerModule(
            'Notiz',
            'notiz',
            'notiz_administration',
            '',
            [
                \CuyZ\Notiz\Controller\Backend\Administration\IndexController::class => 'process',
                \CuyZ\Notiz\Controller\Backend\Administration\ShowDefinitionController::class => 'process',
                \CuyZ\Notiz\Controller\Backend\Administration\ShowExceptionController::class => 'process',
            ],
            [
                'access' => 'admin',
                'icon' => NotizConstants::EXTENSION_ICON_PATH_MODULE_ADMINISTRATION,
                'labels' => "LLL:EXT:" . NotizConstants::EXTENSION_KEY . "/Resources/Private/Language/Backend/Module/Administration/Administration.xlf",
            ]
        );
    }

    /**
     * Dynamically registers the controllers for existing entity notifications.
     */
    protected static function registerEntityNotificationControllers()
    {
        ManagerModuleHandler::get()->registerEntityNotificationControllers();
    }

    /**
     * @see \CuyZ\Notiz\Backend\Report\NotificationStatus
     */
    protected static function registerReportStatus()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['NotiZ'][] = NotificationStatus::class;
    }
}
