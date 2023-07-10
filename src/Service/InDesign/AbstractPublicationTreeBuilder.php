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

namespace Mds\PimPrint\CoreBundle\Service\InDesign;

use Pimcore\Model\DataObject\AbstractObject;

/**
 * Class AbstractPublicationTreeBuilder
 *
 * @package Mds\PimPrint\CoreBundle\Service\InDesign
 */
abstract class AbstractPublicationTreeBuilder
{
    /**
     * Builds publication tree element with $identifier and $label.
     *
     * @param string $identifier
     * @param string $label
     *
     * @return array
     */
    public function buildTreeElement(string $identifier, string $label): array
    {
        return [
            'identifier' => $identifier,
            'label'      => $label,
            'children'   => [],
        ];
    }

    /**
     * Builds recursive object tree.
     *
     * @param AbstractObject $object
     *
     * @return array
     */
    public function buildObjectTree(AbstractObject $object): array
    {
        $tree = $this->buildTreeElementFromObject($object);
        foreach ($object->getChildren() as $child) {
            if (false === $this->showObjectInTree($child)) {
                continue;
            }
            $tree['children'][] = $this->buildObjectTree($child);
        }

        return $tree;
    }

    /**
     * Builds a publication tree element array for $object.
     *
     * @param AbstractObject $object
     *
     * @return array
     */
    protected function buildTreeElementFromObject(AbstractObject $object): array
    {
        return $this->buildTreeElement(
            $this->getObjectIdentifier($object),
            $this->getObjectLabel($object)
        );
    }

    /**
     * Template method.
     * Returns true if $object is a renderable Publication and should be displayed in PimPrint Plugin a publication.
     *
     * This abstract implementation only checks for visibility to current user. Overwrite for project domain use.
     *
     * @param AbstractObject $object
     *
     * @return bool
     */
    protected function showObjectInTree(AbstractObject $object): bool
    {
        return $object->isAllowed('view');
    }

    /**
     * Template method.
     * Returns identifier used in publication tree and publicationIdent generation parameter.
     * As default the object id is used.
     *
     * @param AbstractObject $object
     *
     * @return int|null
     */
    protected function getObjectIdentifier(AbstractObject $object): ?int
    {
        return $object->getId();
    }

    /**
     * Template method.
     * Returns label displayed in publication tree. As Default in this implementation key is used.
     *
     * @param AbstractObject $object
     *
     * @return string
     */
    protected function getObjectLabel(AbstractObject $object): string
    {
        return $object->getKey();
    }
}
