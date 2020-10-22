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
 * Component for placement commands. If the placed element ends at a larger position than maxXPos, the element is
 * automatically repositioned on the following page at $newYPos and $newxPos.
 *
 * @package Mds\PimPrint\CoreBundle\InDesign\Command
 */
class CheckNewPage extends AbstractCommand implements ComponentInterface
{
    /**
     * Command name.
     *
     * @var string
     */
    const CMD = 'checknewpage';

    /**
     * Available command params with default values.
     *
     * @var array
     */
    protected $availableParams = [
        'pos'      => '',
        'newpos'   => '',
        'newpos_x' => null,
    ];

    /**
     * GoToPage constructor.
     *
     * @param float|int|string      $maxYPos Maximum allowed y position on page in mm.
     * @param float|int|string      $newYPos New y position in mm on next page.
     * @param float|int|string|null $newXPos Optional new x position in mm on next page.
     *
     * @throws \Exception
     */
    public function __construct(
        $maxYPos = '',
        $newYPos = '',
        $newXPos = null
    ) {
        $this->initParams($this->availableParams);

        $this->setMaxYPos($maxYPos);
        $this->setNewYPos($newYPos);
        $this->setNewXPos($newXPos);
    }

    /**
     * Sets maximum Y-Position where the placed box should end.
     *
     * @param float|int|string $maxYPos
     *
     * @return CheckNewPage
     * @throws \Exception
     */
    public function setMaxYPos($maxYPos)
    {
        $this->setParam('pos', $maxYPos);

        return $this;
    }

    /**
     * Sets the new Y-Position on the following page where the box is replaced.
     *
     * @param float|int|string $newYPos
     *
     * @return CheckNewPage
     * @throws \Exception
     */
    public function setNewYPos($newYPos)
    {
        $this->setParam('newpos', $newYPos);

        return $this;
    }

    /**
     * Sets the optional X-Position where the box is replaced.
     *
     * @param float|int|string $newXPos
     *
     * @return CheckNewPage
     * @throws \Exception
     */
    public function setNewXPos($newXPos)
    {
        $this->setParam('newpos_x', $newXPos);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getComponentIdent(): string
    {
        return static::CMD;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isMultipleComponent(): bool
    {
        return false;
    }
}
