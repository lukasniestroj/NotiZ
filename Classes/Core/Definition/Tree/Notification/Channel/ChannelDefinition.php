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

namespace CuyZ\Notiz\Core\Definition\Tree\Notification\Channel;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use CuyZ\Notiz\Core\Channel\Channel;
use CuyZ\Notiz\Core\Channel\Settings\ChannelSettings;
use CuyZ\Notiz\Core\Definition\Tree\AbstractDefinitionComponent;
use CuyZ\Notiz\Core\Exception\ClassNotFoundException;
use CuyZ\Notiz\Core\Exception\InvalidClassException;
use CuyZ\Notiz\Core\Exception\NotizException;
use CuyZ\Notiz\Service\LocalizationService;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use TYPO3\CMS\Extbase\Error\Error;

class ChannelDefinition extends AbstractDefinitionComponent implements DataPreProcessorInterface
{
    /**
     * @var string
     *
     * @Extbase\Validate("NotEmpty")
     */
    protected $identifier;

    /**
     * @var string
     *
     * @Extbase\Validate("NotEmpty")
     * @Extbase\Validate("Romm\ConfigurationObject\Validation\Validator\ClassImplementsValidator", options={"interface": "CuyZ\Notiz\Core\Channel\Channel"})
     */
    protected $className;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var ChannelSettings
     *
     * @mixedTypesResolver \CuyZ\Notiz\Core\Definition\Tree\Notification\Channel\Settings\ChannelSettingsResolver
     */
    protected $settings;

    /**
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return LocalizationService::localize($this->label);
    }

    /**
     * @return ChannelSettings
     */
    public function getSettings(): ChannelSettings
    {
        return $this->settings;
    }

    /**
     * Method called during the definition object construction: it allows
     * manipulating the data array before it is actually used to construct the
     * object.
     *
     * We use it to add the settings class name further in the data array so it
     * can be fetched later.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        // Settings must always be set.
        if (!is_array($data['settings'])) {
            $data['settings'] = [];
        }

        $data['settings'][ChannelSettings::SETTINGS_CLASS_NAME] = ChannelSettings::TYPE_DEFAULT;

        try {
            $data = self::fetchSettingsClassName($data);
        } catch (NotizException $exception) {
            $error = new Error($exception->getMessage(), $exception->getCode());
            $processor->addError($error);
        }

        $processor->setData($data);
    }

    /**
     * @param array $data
     * @return array
     *
     * @throws ClassNotFoundException
     * @throws InvalidClassException
     */
    protected static function fetchSettingsClassName(array $data): array
    {
        $channelClassName = $data['className'] ?? null;

        if (class_exists($channelClassName)
            && in_array(Channel::class, class_implements($channelClassName))
        ) {
            /** @var Channel $channelClassName */
            $settingsClassName = $channelClassName::getSettingsClassName();

            if (!class_exists($settingsClassName)) {
                throw ClassNotFoundException::channelSettingsClassNotFound($settingsClassName);
            }

            if (!in_array(ChannelSettings::class, class_implements($settingsClassName))) {
                throw InvalidClassException::channelSettingsMissingInterface($settingsClassName);
            }

            $data['settings'][ChannelSettings::SETTINGS_CLASS_NAME] = $settingsClassName;
        }

        return $data;
    }
}
