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

namespace CuyZ\Notiz\Core\Definition\Tree\EventGroup;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use CuyZ\Notiz\Core\Definition\Tree\AbstractDefinitionComponent;
use CuyZ\Notiz\Core\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Core\Exception\EntryNotFoundException;
use CuyZ\Notiz\Service\LocalizationService;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;

class EventGroup extends AbstractDefinitionComponent implements DataPreProcessorInterface
{
    /**
     * @var string
     *
     * @Extbase\Validate("NotEmpty")
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var \CuyZ\Notiz\Core\Definition\Tree\EventGroup\Event\EventDefinition[]
     *
     * @Extbase\Validate("NotEmpty")
     */
    protected $events = [];

    /**
     * @param string $identifier
     */
    public function __construct(string $identifier = null)
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
    public function getLabel(): string
    {
        return LocalizationService::localize($this->label);
    }

    /**
     * @return EventDefinition[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasEvent(string $identifier): bool
    {
        return true === isset($this->events[$identifier]);
    }

    /**
     * @param string $identifier
     * @return EventDefinition
     *
     * @throws EntryNotFoundException
     */
    public function getEvent(string $identifier): EventDefinition
    {
        if (false === $this->hasEvent($identifier)) {
            throw EntryNotFoundException::definitionEventNotFound($identifier);
        }

        return $this->events[$identifier];
    }

    /**
     * @return EventDefinition
     */
    public function getFirstEvent(): EventDefinition
    {
        $array = array_reverse($this->getEvents());
        return array_pop($array);
    }

    /**
     * Method called during the definition object construction: it allows
     * manipulating the data array before it is actually used to construct the
     * object.
     *
     * We use it to automatically fill the `identifier` property of the events
     * with the keys of the array.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        self::forceIdentifierForProperty($processor, 'events');
    }
}
