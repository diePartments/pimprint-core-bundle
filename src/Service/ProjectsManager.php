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

use Mds\PimPrint\CoreBundle\Project\AbstractProject;
use Mds\PimPrint\CoreBundle\Project\Config;

/**
 * ProjectsManager registers all configured PimPrint rendering project services defined in
 * mds_pim_print_core configuration and acts as a factory for accessing the concrete rendering services.
 *
 * @package Mds\PimPrint\CoreBundle\Service
 */
class ProjectsManager
{
    /**
     * Configuration of registered PimPrint Projects.
     *
     * @var array
     */
    private array $projects = [];

    /**
     * Instance of current selected project for generation.
     *
     * @var AbstractProject|null
     */
    private static ?AbstractProject $project = null;

    /**
     * Projects constructor.
     *
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->registerProjects($config);
    }

    /**
     * Registers all PimPrint projects from $config.
     *
     * @param array $config
     *
     * @return void
     */
    private function registerProjects(array $config): void
    {
        foreach ($config as $ident => $project) {
            if (empty($project['ident'])) {
                $project['ident'] = (string)$ident;
            } else {
                $ident = $project['ident'];
            }
            $this->projects[$ident] = $project;
        }
    }

    /**
     * Returns a array with information for all projects.
     *
     * @return array
     * @throws \Exception
     */
    public function getProjectsInfo(): array
    {
        $return = [];
        foreach ($this->projects as $ident => $project) {
            $return[] = $this->projectServiceFactory($ident, false)
                             ->getInfo();
        }

        return $return;
    }

    /**
     * Returns current selected project.
     *
     * @return AbstractProject
     * @throws \Exception
     */
    public static function getProject(): AbstractProject
    {
        if (false === self::$project instanceof AbstractProject) {
            throw new \Exception('No project selected for generation.');
        }

        return self::$project;
    }

    /**
     * Loads and returns project service with $ident.
     *
     * @param string $ident
     * @param bool   $registerSelected
     *
     * @return AbstractProject
     * @throws \Exception
     */
    public function projectServiceFactory(string $ident, bool $registerSelected = true): AbstractProject
    {
        if (false === isset($this->projects[$ident])) {
            throw new \Exception(
                sprintf("No PimPrint project with ident '%s' registered.", $ident)
            );
        }
        $config = $this->projects[$ident];
        try {
            $service = \Pimcore::getKernel()
                               ->getContainer()
                               ->get($config['service']);
        } catch (\Exception) {
            throw new \Exception(
                sprintf("No public PimPrint project service '%s' found.", $config['service'])
            );
        }
        if (false === $service instanceof AbstractProject) {
            throw new \Exception(
                sprintf(
                    "PimPrint project service '%s' is no instance of '%s'.",
                    $ident,
                    AbstractProject::class
                )
            );
        }
        $service->setConfig(new Config($config));
        $service->assertServiceInitialized();
        if (true === $registerSelected) {
            self::$project = $service;
        }

        return $service;
    }
}
