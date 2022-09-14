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

namespace Mds\PimPrint\CoreBundle\InDesign\Command\Traits;

use Mds\PimPrint\CoreBundle\InDesign\Command\AbstractBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;

/**
 * Trait to add params for positioning to a command.
 * The params left and top are used to position a element absolutely in the document. When this params are used
 * the upper-left corner of the element is positioned left/top position.
 *
 * With the method setRelativePosition a element can be placed relative to prior defined variables in InDesign via
 * the Variable command or VariableTrait.
 *
 * @package Mds\PimPrint\CoreBundle\InDesign\Command\Traits
 */
trait PositionTrait
{
    /**
     * Name of the variables which the element is relative positioned.
     *
     * @var array
     */
    protected array $relativePositionVariables = [];

    /**
     * Initializes trait
     *
     * @return void
     */
    protected function initPosition(): void
    {
        $this->initParams(
            [
                'left' => 0,
                'top'  => 0,
            ]
        );
    }

    /**
     * Sets the left position in mm where the element should be placed in the document.
     *
     * @param float|string|null $left Left position in mm.
     *
     * @return PositionTrait|AbstractBox
     * @throws \Exception
     */
    public function setLeft(float|string|null $left): AbstractBox|static
    {
        $this->setParam('left', $left);
        $this->checkRelativePositionVariable('left', $left);

        return $this;
    }

    /**
     * Sets the top position in mm where the element should be placed in the document.
     *
     * @param float|string|null $top Top position in mm.
     *
     * @return PositionTrait|AbstractBox
     * @throws \Exception
     */
    public function setTop(float|string|null $top): AbstractBox|static
    {
        $this->setParam('top', $top);
        $this->checkRelativePositionVariable('top', $top);

        return $this;
    }

    /**
     * Sets $position relative to $variable with an optional $margin.
     *
     * @param string    $position Left or top position for relative positioning.
     * @param string    $variable Variable name in InDesign for relative positioning.
     * @param float|int $margin   Margin in mm to the InDesign variable.
     *
     * @return PositionTrait|AbstractBox
     * @throws \Exception
     */
    public function setRelativePosition(string $position, string $variable, float|int $margin = 0): AbstractBox|static
    {
        $this->validateRelativePosition($position);
        $this->relativePositionVariables[$position] = $variable;
        $method = 'set' . ucfirst($position);

        return $this->$method("=[$variable] + $margin");
    }

    /**
     * Convenience method to set relative left position to $variable and $margin.
     *
     * @param string    $variable Variable name in InDesign for relative positioning.
     * @param float|int $margin   Margin in mm to the InDesign variable.
     *
     * @return AbstractBox|PositionTrait
     * @throws \Exception
     */
    public function setLeftRelative(string $variable, float|int $margin = 0): AbstractBox|static
    {
        return $this->setRelativePosition(Variable::POSITION_LEFT, $variable, $margin);
    }

    /**
     * Convenience method to set relative top position to $variable and $margin.
     *
     * @param string    $variable Variable name in InDesign for relative positioning.
     * @param float|int $margin   Margin in mm to the InDesign variable.
     *
     * @return AbstractBox|PositionTrait
     * @throws \Exception
     */
    public function setTopRelative(string $variable, float|int $margin = 0): AbstractBox|static
    {
        return $this->setRelativePosition(Variable::POSITION_TOP, $variable, $margin);
    }

    /**
     * Checks if $value contains a position variable. If not the registered variable is removed.
     *
     * @param string      $position
     * @param string|null $value
     */
    protected function checkRelativePositionVariable(string $position, string|null $value): void
    {
        if (str_starts_with($value, '=[')) {
            return;
        }
        unset($this->relativePositionVariables[$position]);
    }

    /**
     * Validates $position parameter for relative positioning.
     *
     * @param string $position
     *
     * @return void
     * @throws \Exception
     */
    protected function validateRelativePosition(string $position): void
    {
        if ($position !== Variable::POSITION_TOP && $position !== Variable::POSITION_LEFT) {
            $message = "Invalid position '%s' for relative positioning in '%s'." .
                "Use '%s::POSITION_TOP' or '%s::POSITION_LEFT'.";
            throw new \Exception(
                sprintf(
                    $message,
                    $position,
                    static::class,
                    Variable::class,
                    Variable::class
                )
            );
        }
    }

    /**
     * Returns true if element is relative positioned.
     *
     * @return bool
     */
    public function isRelativePositioned(): bool
    {
        return !empty($this->relativePositionVariables);
    }

    /**
     * Returns the names of all variable to which the element is relative positioned.
     *
     * @return array
     */
    public function getRelativePositionVariables(): array
    {
        return $this->relativePositionVariables;
    }

    /**
     * Returns true if element is relative positioned to $variable.
     *
     * @param string $variable
     *
     * @return bool
     */
    public function isRelativePositionedToVariable(string $variable): bool
    {
        return in_array($variable, $this->relativePositionVariables);
    }

    /**
     * Returns an array with all variables command is dependent from.
     *
     * @return array
     */
    public function getDependentVariables(): array
    {
        return $this->getRelativePositionVariables();
    }
}
