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

namespace CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use CuyZ\Notiz\Core\Definition\Tree\AbstractDefinitionComponent;
use CuyZ\Notiz\Core\Notification\Settings\NotificationSettings;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\View\View;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;

class EntityEmailSettings extends AbstractDefinitionComponent implements NotificationSettings, DataPreProcessorInterface
{
    /**
     * @var string
     *
     * @Extbase\Validate("NotEmpty")
     */
    protected $defaultSender;

    /**
     * @var \CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\GlobalRecipients\GlobalRecipients
     */
    protected $globalRecipients;

    /**
     * @var \CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\View\View
     */
    protected $view;

    /**
     * @return string
     */
    public function getDefaultSender(): string
    {
        return $this->defaultSender;
    }

    /**
     * @return GlobalRecipients\GlobalRecipients
     */
    public function getGlobalRecipients(): GlobalRecipients\GlobalRecipients
    {
        return $this->globalRecipients;
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        return $this->view;
    }

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        // View object must always be set.
        if (!is_array($data['view'])) {
            $data['view'] = [];
        }

        // Recipients object must always be set.
        if (!is_array($data['globalRecipients'])) {
            $data['globalRecipients'] = [];
        }

        $processor->setData($data);
    }
}
