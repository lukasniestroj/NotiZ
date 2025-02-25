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

namespace CuyZ\Notiz\Core\Event\Configuration\FlexForm;

use TYPO3\CMS\Extbase\Annotation as Extbase;
/**
 * Default implementation of a FlexForm provider, it does nothing more than
 * returning the path to the given file, so TCA can handle the FlexForm file
 * itself.
 */
class DefaultEventFlexFormProvider implements EventFlexFormProvider
{
    /**
     * @var string
     *
     * @Extbase\Validate("Romm\ConfigurationObject\Validation\Validator\FileExistsValidator")
     */
    protected $file;

    /**
     * @param string $file
     */
    public function __construct(string $file = '')
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFlexFormValue(): string
    {
        return 'FILE:' . $this->file;
    }

    /**
     * @return bool
     */
    public function hasFlexForm(): bool
    {
        return !empty($this->file);
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
}
