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

use CuyZ\Notiz\Core\Support\NotizConstants;
use CuyZ\Notiz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Wrapper around the TYPO3 localization utility to change the default behaviour
 * for the localization.
 *
 * A new syntax is introduced to shorten the localization path.
 *
 * Moreover, if the given key does not correspond to an existing localization
 * entry it is returned unmodified.
 *
 * ---
 *
 * The shorten syntax looks like:
 *
 * `[path to localization file]:[localization key]`
 *
 * - The path to the localization file is relative to the registered extensions'
 *   private resources folder, without the file extension needed (`.xlf`).
 *
 * - The localization key is the same as in a classical localization path.
 *
 * For example, giving...
 *
 * `Events/MyEvent:some_event.label`
 *
 * ...will actually fetch:
 *
 * `LLL:EXT:my_extension/Resources/Private/Language/Events/MyEvent/MyEvent.xlf:some_event.label`
 *
 * To register a new extension key to be used in the shorten version, put the
 * following code in your `ext_localconf.php` file:
 *
 * `\CuyZ\Notiz\Service\LocalizationService::get()->addExtensionKey('my_extension');`
 */
class LocalizationService implements SingletonInterface
{
    use SelfInstantiateTrait;

    /**
     * Extension keys that will be used for the shorten syntax version.
     *
     * @var array
     */
    protected $extensionKeys = [
        NotizConstants::EXTENSION_KEY,
    ];

    /**
     * Static proxy to the method `doLocalize()`.
     *
     * @param string|null $key [PHP 7.1]
     * @param array $arguments
     * @return string
     */
    public static function localize($key, array $arguments = []): string
    {
        return $key
            ? self::get()->doLocalize($key, $arguments)
            : '';
    }

    /**
     * See class description for more information.
     *
     * @param string $path
     * @param array $arguments
     * @return string
     */
    public function doLocalize(string $path, array $arguments = []): string
    {
        if (false === strpos($path, ':')) {
            $possiblePaths = $this->getPossiblePaths('locallang', $path);
        } elseif (strpos($path, 'LLL:') === 0) {
            $possiblePaths = [$path];
        } else {
            // Getting the file path and the localization key.
            list($file, $key) = explode(':', $path);

            // Deleting possible `/` at the beginning of the file path.
            if (strpos($file, '/') === 0) {
                $file = substr($file, 1, strlen($file));
            }

            $possiblePaths = $this->getPossiblePaths($file, $key);
        }

        /*
         * Looping on all the possible localization entries. The first found
         * entry is returned.
         */
        foreach ($possiblePaths as $possiblePath) {
            $value = $this->localizeInternal($possiblePath, $arguments);

            if ($value && $value !== $possiblePath) {
                return $value;
            }
        }

        // If no localization entry was found, the given is returned.
        return $path;
    }

    /**
     * Adds an extension key that will be used for the shorten version of the
     * localization path syntax.
     *
     * See class description for more information.
     *
     * @param string $extensionKey
     */
    public function addExtensionKey(string $extensionKey)
    {
        if (!in_array($extensionKey, $this->extensionKeys)) {
            $this->extensionKeys[] = $extensionKey;
        }
    }

    /**
     * @param string $file
     * @param string $key
     * @return array
     */
    protected function getPossiblePaths(string $file, string $key): array
    {
        return array_map(
            function ($extensionKey) use ($file, $key) {
                /**
                 * We consider that each translation directory must have a file
                 * named after the directory.
                 *
                 * For example, the path `Foo/Bar` will generate the path `Foo/Bar/Bar.xlf`
                 */
                $array = explode('/', $file);
                $file = $file . '/' . end($array);

                return 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/' . $file . '.xlf:' . $key;
            },
            $this->extensionKeys
        );
    }

    /**
     * Calls the TYPO3 localization service.
     *
     * @param string $key
     * @param array $arguments
     * @return string|null [PHP 7.1]
     */
    protected function localizeInternal(string $key, array $arguments)
    {
        if ($GLOBALS['LANG'] === null) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
        }

        return LocalizationUtility::translate(
            $key,
            NotizConstants::EXTENSION_KEY,
            $arguments
        );
    }
}
