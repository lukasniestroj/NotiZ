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

namespace CuyZ\Notiz\Service;

use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Container implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait {
        get as getInstance;
    }

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $className
     * @param mixed ...$arguments
     * @return object
     */
    public static function get(string $className, ...$arguments)
    {
        return static::getInstance()->objectManager->get($className, ...$arguments);
    }

    /**
     * @return BackendUserAuthentication
     */
    public static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return PageRepository
     */
    public static function getPageRepository(): PageRepository
    {
        $instance = self::getInstance();

        if (null === $instance->pageRepository) {
            $instance->pageRepository = $instance->get(PageRepository::class);
        }

        return $instance->pageRepository;
    }
}
