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

namespace Mds\PimPrint\CoreBundle\EventListener;

use Mds\PimPrint\CoreBundle\Security\Traits\InDesignRequestDetector;
use Pimcore\Bundle\AdminBundle\Security\Exception\BruteforceProtectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sends brute force errors in PimPrint InDesign-Plugin response.
 *
 * @see     \Pimcore\Bundle\AdminBundle\EventListener\BruteforceProtectionListener
 *
 * @package Mds\PimPrint\CoreBundle\EventListener
 */
class BruteforceProtectionListener implements EventSubscriberInterface
{
    use InDesignRequestDetector;

    /**
     * Subscribe event listener with priority 70, to have it run earlier than Pimcore BruteforceProtectionListener.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 70]
        ];
    }

    /**
     * Returns PimPrint InDesign-Plugin error response.
     *
     * @param ExceptionEvent $event
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if (false === $this->isInDesignRequest($event->getRequest())) {
            return;
        }
        $exception = $event->getThrowable();
        if ($exception instanceof BruteforceProtectionException) {
            $response = new JsonResponse(
                [
                    'success'   => false,
                    'debugMode' => false,
                    'messages'  => [$exception->getMessage()]
                ],
                Response::HTTP_UNAUTHORIZED
            );
            $event->setResponse($response);
        }
    }
}
