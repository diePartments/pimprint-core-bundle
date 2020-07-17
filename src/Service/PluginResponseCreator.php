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

namespace Mds\PimPrint\CoreBundle\Service;

use Mds\PimPrint\CoreBundle\InDesign\Traits\MissingAssetNotifier;
use Mds\PimPrint\CoreBundle\Session\PimPrintSessionBagConfigurator;
use Pimcore\Http\RequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class PluginResponseCreator
 *
 * @package Mds\PimPrint\CoreBundle\Service
 */
class PluginResponseCreator
{
    use MissingAssetNotifier;

    /**
     * Lazy loading.
     *
     * @var bool
     */
    private static $isDebugMode;

    /**
     * Pimcore RequestHelper.
     *
     * @var RequestHelper
     */
    private $requestHelper;

    /**
     * PluginResponseCreator constructor.
     *
     * @param RequestHelper $requestHelper
     */
    public function __construct(RequestHelper $requestHelper)
    {
        $this->requestHelper = $requestHelper;
    }

    /**
     * Builds a success (success true) response for InDesign.
     *
     * @param array $data
     *
     * @return JsonResponse
     */
    public function success(array $data)
    {
        $data['success'] = true;

        return $this->buildResponse($data, Response::HTTP_ACCEPTED);
    }

    /**
     * Builds an error (success false) response for InDesign plugin with exception information.
     *
     * @param \Exception $exception
     *
     * @return JsonResponse
     */
    public function error(\Exception $exception)
    {
        $data = [
            'success'  => false,
            'messages' => [$exception->getMessage()]
        ];
        if (true === $this->isDebugMode()) {
            $data['messages'][] = $exception->getTraceAsString();
        }

        return $this->buildResponse($data, Response::HTTP_ACCEPTED);
    }

    /**
     * Adds charset to header and builds json response with $data as payload.
     *
     * @param array $data
     * @param int   $status
     *
     * @return JsonResponse
     */
    protected function buildResponse(array $data, $status = Response::HTTP_OK)
    {
        $headers['content-type'] = 'application/json;charset=utf-8';
        if (false === isset($data['messages'])) {
            $data['messages'] = [];
        }
        $data['debugMode'] = $this->isDebugMode();
        $this->addMissingAssetPreMessage();
        $this->addMessages($data);
        $this->addImages($data);
        $this->addSettings($data);
        $this->addSession($data);

        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Returns true if debug mode is enabled and optional ip is matching.
     *
     * @return bool
     */
    protected function isDebugMode()
    {
        if (null === self::$isDebugMode) {
            self::$isDebugMode = false;
            $debugModeFile = PIMCORE_CONFIGURATION_DIRECTORY . '/debug-mode.php';
            $debugMode = [];
            if (file_exists($debugModeFile)) {
                $debugMode = include $debugModeFile;
            }
            $config['debug'] = $debugMode['active'] ?? false;
            $config['debug_ip'] = $debugMode['ip'] ?? '';

            if (true === $config['debug']) {
                $debugIps = $config['debug_ip'] ?? '';
                if (true === empty($debugIps)) {
                    self::$isDebugMode = true;
                } else {
                    $debugIps = explode(',', $debugIps);
                    $clientIp = $this->requestHelper->getRequest()
                                                    ->getClientIp();
                    if (true === in_array($clientIp, $debugIps)) {
                        self::$isDebugMode = true;
                    }
                }
            }
        }

        return self::$isDebugMode;
    }

    /**
     * Adds project settings if a project is currently selected.
     *
     * @param array $data
     */
    private function addSettings(array &$data)
    {
        try {
            $data['settings'] = ProjectsManager::getProject()
                                               ->getSettings();
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Adds sessionId to JSON response $data if a login session was created for InDesign.
     *
     * @param array $data
     */
    private function addSession(array &$data)
    {
        $request = $this->requestHelper->getRequest();
        $session = $request->getSession();
        if (false === $session instanceof SessionInterface) {
            return;
        }
        $sessionBag = $session->getBag(PimPrintSessionBagConfigurator::NAMESPACE);
        if (false === $sessionBag instanceof NamespacedAttributeBag) {
            return;
        }
        if (false === $sessionBag->has('sendId')) {
            return;
        }
        $data['session'] = [
            'name' => $session->getName(),
            'id'   => $session->getId(),
        ];
        $sessionBag->remove('sendId');
    }

    /**
     * Adds project preMessages to $data.
     *
     * @param array $data
     */
    private function addMessages(array &$data)
    {
        try {
            $messages = ProjectsManager::getProject()
                                       ->getPreMessages();
            if (empty($messages)) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            return;
        }
        $data['messages'] = array_merge(
            $data['messages'],
            $messages
        );
    }

    /**
     * Adds used images.
     *
     * @param array $data
     */
    private function addImages(array &$data)
    {
        try {
            $project = ProjectsManager::getProject();
            if (false === $project->config()
                                  ->isAssetDownloadEnabled()) {
                return;
            }
            $data['images'] = $project->getCommandQueue()
                                      ->getRegisteredAssets();
        } catch (\Exception $e) {
            //don't add images to response when no project is generated.
        }
    }
}
