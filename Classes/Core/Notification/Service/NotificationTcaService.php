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

namespace CuyZ\Notiz\Core\Notification\Service;

use CuyZ\Notiz\Core\Definition\DefinitionService;
use CuyZ\Notiz\Core\Definition\Tree\Definition;
use CuyZ\Notiz\Core\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Core\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Core\Exception\NotImplementedException;
use CuyZ\Notiz\Core\Notification\Notification;
use CuyZ\Notiz\Core\Support\NotizConstants;
use CuyZ\Notiz\Domain\Property\Marker;
use CuyZ\Notiz\Service\Container;
use CuyZ\Notiz\Service\LocalizationService;
use CuyZ\Notiz\Service\StringService;
use CuyZ\Notiz\Service\ViewService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Utility service to ease TCA manipulation for TYPO3 notification records.
 */
abstract class NotificationTcaService implements SingletonInterface
{
    /**
     * @var DefinitionService
     */
    protected $definitionService;

    /**
     * @var ViewService
     */
    protected $viewService;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var Notification[]
     */
    private $notification;

    /**
     * Manual dependency injection.
     */
    public function __construct()
    {
        $this->definitionService = Container::get(DefinitionService::class);
        $this->viewService = Container::get(ViewService::class);
        $this->dataMapper = Container::get(DataMapper::class);
    }

    /**
     * Loads all available events and stores them as an array to be used in the
     * TCA.
     *
     * @param array $parameters
     */
    public function getEventsList(array &$parameters)
    {
        if ($this->definitionHasErrors()) {
            return;
        }

        $eventGroups = $this->getDefinition()->getEventGroups();

        foreach ($eventGroups as $eventGroup) {
            $parameters['items'][] = [
                $eventGroup->getLabel(),
                '--div--',
            ];

            foreach ($eventGroup->getEvents() as $event) {
                $parameters['items'][] = [
                    $event->getLabel(),
                    $event->getFullIdentifier(),
                ];
            }
        }
    }

    /**
     * This function will fetch the event selected in the given notification.
     *
     * If the notification is new or if the event identifier is not found, the
     * first event from the first event group is returned.
     *
     * @param array $row
     * @return EventDefinition
     */
    protected function getSelectedEvent(array $row): EventDefinition
    {
        $definition = $this->getDefinition();

        // The first configured event is selected by default.
        $event = $definition->getFirstEventGroup()->getFirstEvent();

        if (isset($row['event']) && !empty($row['event'])) {
            $eventValue = is_array($row['event'])
                ? $row['event'][0]
                : $row['event'];

            if ($definition->hasEventFromFullIdentifier($eventValue)) {
                $event = $definition->getEventFromFullIdentifier($eventValue);
            }
        }

        return $event;
    }

    /**
     * Loads all markers for the current selected event and formats them as a
     * list to be displayed on the edit form.
     *
     * @param array $row
     * @return string
     */
    public function getMarkersLabel(array $row): string
    {
        if ($this->definitionHasErrors()) {
            return '';
        }

        $eventDefinition = $this->getSelectedEvent($row);
        $notification = $this->getNotification($row);

        /** @var Marker[] $markers */
        $markers = $eventDefinition->getPropertyDefinition(Marker::class, $notification)->getEntries();

        $output = '';

        foreach ($markers as $marker) {
            $label = StringService::mark($marker->getLabel());
            $output .= "<tr><td><strong>{$marker->getFormattedName()}</strong></td><td>$label</td></tr>";
        }

        $description = LocalizationService::localize('Notification/Entity:field.markers.description', [$eventDefinition->getLabel()]);

        return <<<HTML
<p>$description</p>

<table class="table table-striped table-hover">
    <tbody>
        $output
    </tbody>
</table>
HTML;
    }

    /**
     * Loads all available channels and stores them as an array to be used in
     * the TCA.
     *
     * @param array $parameters
     */
    public function getChannelsList(array &$parameters)
    {
        if ($this->definitionHasErrors()) {
            return;
        }

        foreach ($this->getNotificationDefinition()->getChannels() as $channelDefinition) {
            $label = $channelDefinition->getLabel();

            if (empty($label)) {
                $label = $channelDefinition->getIdentifier();
            }

            $parameters['items'][] = [$label, $channelDefinition->getClassName()];
        }
    }

    /**
     * Returns a notification object based on an array containing the
     * notification properties.
     *
     * @param array $row
     * @return Notification
     */
    protected function getNotification(array $row): Notification
    {
        $hash = json_encode($row);

        if (!isset($this->notification[$hash])) {
            $map = $this->dataMapper->map($this->getNotificationDefinition()->getClassName(), [$row]);
            $this->notification[$hash] = isset($row['uid']) && is_integer($row['uid'])
                ? $this->getNotificationDefinition()->getProcessor()->getNotificationFromIdentifier((string)$row['uid'])
                : reset($map);
        }

        return $this->notification[$hash];
    }

    /**
     * @param array $array
     * @param $label
     */
    protected function appendOptionGroup(array &$array, $label)
    {
        array_unshift($array, ['label' => "––– $label –––", 'value' => '--div--']);
    }

    /**
     * @return string
     */
    public function getNotificationIconPath(): string
    {
        if ($this->definitionService->getValidationResult()->hasErrors()) {
            return NotizConstants::EXTENSION_ICON_DEFAULT;
        }

        return $this->getNotificationDefinition()->getIconPath();
    }

    /**
     * @return Definition
     */
    public function getDefinition(): Definition
    {
        return $this->definitionService->getDefinition();
    }

    /**
     * @return NotificationDefinition
     */
    protected function getNotificationDefinition(): NotificationDefinition
    {
        return $this->getDefinition()->getNotification($this->getDefinitionIdentifier());
    }

    /**
     * This method must return the current notification identifier to be used to
     * retrieve the current notification definition.
     *
     * @return string
     * @throws NotImplementedException
     */
    protected function getDefinitionIdentifier(): string
    {
        throw NotImplementedException::tcaServiceNotificationIdentifierMissing(__METHOD__);
    }

    /**
     * @return bool
     */
    public function definitionHasErrors(): bool
    {
        return $this->definitionService->getValidationResult()->hasErrors();
    }
}
