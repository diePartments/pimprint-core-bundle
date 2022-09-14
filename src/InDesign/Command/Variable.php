<?php
/**
 * mds PimPrint
 *
 * This source file is licensed under GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license    https://pimprint.mds.eu/license GPLv3
 */

namespace Mds\PimPrint\CoreBundle\InDesign\Command;

/**
 * Standalone or component for placement commands. Sets a variable with a position value in InDesign.
 * If the command is used as a component, positions of the placed element can be defined as variables for relative
 * positioning.
 *
 * @package Mds\PimPrint\CoreBundle\InDesign\Command
 */
class Variable extends AbstractCommand implements ComponentInterface
{
    /**
     * Constant for defining relative positions to box left value.
     *
     * @var string
     */
    const POSITION_LEFT = 'left';

    /**
     * Constant for defining relative positions to box right value.
     *
     * @var string
     */
    const POSITION_RIGHT = 'right';

    /**
     * Constant for defining relative positions to box top value.
     *
     * @var string
     */
    const POSITION_TOP = 'top';

    /**
     * Constant for defining relative positions to box bottom value.
     *
     * @var string
     */
    const POSITION_BOTTOM = 'bottom';

    /**
     * InDesign variable name for yPosition.
     *
     * @var string
     */
    const VARIABLE_Y_POSITION = 'yPos';

    /**
     * InDesign variable name for xPosition.
     *
     * @var string
     */
    const VARIABLE_X_POSITION = 'xPos';

    /**
     * Array to validate positions.
     *
     * @var array
     */
    public static array $allowedPositions = [
        self::POSITION_LEFT,
        self::POSITION_RIGHT,
        self::POSITION_TOP,
        self::POSITION_BOTTOM,
    ];

    /**
     * Command name.
     *
     * @var string
     */
    const CMD = 'variable';

    /**
     * Available command params with default values.
     *
     * @var array
     */
    protected array $availableParams = [
        'name'  => '',
        'value' => '',
    ];

    /**
     * Variable constructor.
     *
     * @param string $name
     * @param string $value
     *
     * @throws \Exception
     */
    public function __construct(string $name = '', string $value = '')
    {
        $this->initParams($this->availableParams);
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * Sets name of variable in InDesign.
     *
     * @param string $name Name of variable in InDesign.
     *
     * @return Variable
     * @throws \Exception
     */
    public function setName(string $name): Variable
    {
        $this->setParam('name', $name);

        return $this;
    }

    /**
     * Returns InDesign variable name.
     *
     * @return string
     */
    public function getName(): string
    {
        try {
            return $this->getParam('name');
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Sets value of variable in InDesign.
     *
     * @param float|int|string $value Value of variable in InDesign.
     *
     * @return Variable
     * @throws \Exception
     */
    public function setValue(float|int|string $value): Variable
    {
        $this->setParam('value', $value);

        return $this;
    }

    /**
     * Validates existence of variable name.
     *
     * @return void
     * @throws \Exception
     */
    protected function validate(): void
    {
        $this->validateEmptyParam('name', 'name');
    }

    /**
     * Returns ident of command when used as compound.
     *
     * @return string
     */
    public function getComponentIdent(): string
    {
        return 'variables';
    }

    /**
     * Returns true if component can be used multiple times in the same command.
     *
     * @return bool
     */
    public function isMultipleComponent(): bool
    {
        return true;
    }
}
