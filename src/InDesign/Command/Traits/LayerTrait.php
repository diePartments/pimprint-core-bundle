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

/**
 * Trait to add layer param to a command.
 * The layer param is used to set the layer in which a new element should be placed.
 *
 * @package Mds\PimPrint\CoreBundle\InDesign\Command\Traits
 */
trait LayerTrait
{
    /**
     * Initializes trait
     *
     * @return void
     */
    protected function initLayer(): void
    {
        $this->initParams(['layer' => null]);
    }

    /**
     * Sets $layer as target layer for a placed element.
     *
     * @param string $layer
     *
     * @return LayerTrait|AbstractBox
     * @throws \Exception
     */
    public function setLayer(string $layer): AbstractBox|static
    {
        $this->setParam('layer', $layer);

        return $this;
    }
}
