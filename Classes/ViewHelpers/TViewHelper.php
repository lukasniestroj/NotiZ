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

namespace CuyZ\Notiz\ViewHelpers;

use CuyZ\Notiz\Service\LocalizationService;
use CuyZ\Notiz\Service\StringService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class TViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', 'The translation key');
        $this->registerArgument('args', 'array', 'Translation arguments');
        $this->registerArgument('wrapLines', 'bool', 'Wrap each line of the translated string into HTML p tags');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $key = isset($arguments['key'])
            ? $arguments['key']
            : $renderChildrenClosure();

        $args = isset($arguments['args'])
            ? $arguments['args']
            : [];

        $result = LocalizationService::localize($key, $args);

        if ($arguments['wrapLines']) {
            $result = StringService::get()->wrapLines($result);
        }

        return $result;
    }
}
