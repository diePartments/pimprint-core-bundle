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

namespace Mds\PimPrint\CoreBundle\Project\Traits;

use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\OpenDocument;
use Mds\PimPrint\CoreBundle\InDesign\Command\RemoveEmptyLayers;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\Project\AbstractProject;
use Pimcore\Localization\IntlFormatter;
use Pimcore\Localization\LocaleService;
use Pimcore\Model\Asset;
use Pimcore\Tool;

/**
 * Trait RenderingTrait
 *
 * @package Mds\PimPrint\CoreBundle\Project\Traits
 */
trait RenderingTrait
{
    /**
     * Indicated if a generation of project is active.
     *
     * @var bool
     */
    private bool $generationActive = false;

    /**
     * Reference string for box ident generation.
     * Used to generate unique content related box idents to create coupling between Pimcore content
     * (Objects, Assets, Documents) and InDesign elements.
     * Typical usage: use Object-Ids here.
     *
     * @var string
     */
    private string $boxIdentReference = '';

    /**
     * Postfix used in generic box ident generation.
     *
     * @var string
     */
    private string $boxIdentGenericPostfix = '';

    /**
     * Generates PimPrint commands to build a publication in InDesign.
     *
     * @return array
     * @throws \Exception
     */
    final public function run(): array
    {
        $this->generationActive = true;
        $this->buildPublication();

        return $this->getCommandQueue()
                    ->getCommands();
    }

    /**
     * Returns true if the current request generated a project.
     *
     * @return bool
     */
    final public function isGenerationActive(): bool
    {
        return $this->generationActive;
    }

    /**
     * Returns $boxIdentReference.
     *
     * @return string
     */
    public function getBoxIdentReference(): string
    {
        return $this->boxIdentReference;
    }

    /**
     * Sets $ident as boxIdentReference for content aware updates.
     *
     * @param string $ident
     *
     * @see RenderingTrait::$boxIdentReference
     */
    public function setBoxIdentReference(string $ident): void
    {
        $this->boxIdentReference = $ident;
    }

    /**
     * Appends $ident to existing for content aware updates.
     *
     * @param string $ident
     *
     * @see RenderingTrait::$boxIdentReference
     */
    public function appendToBoxIdentReference(string $ident): void
    {
        $this->setBoxIdentReference(
            $this->getBoxIdentReference() . $ident
        );
    }

    /**
     * Returns $boxIdentGenericPostfix.
     *
     * @return string
     */
    public function getBoxIdentGenericPostfix(): string
    {
        return $this->boxIdentGenericPostfix;
    }

    /**
     * Sets $boxIdentGenericPostfix.
     *
     * @param string $postfix
     */
    public function setBoxIdentGenericPostfix(string $postfix): void
    {
        $this->boxIdentGenericPostfix = $postfix;
    }

    /**
     * Convenience method that initializes renderMode, opens InDesign template and jumps to first page.
     *
     * @param bool $openFirstPage
     *
     * @throws \Exception
     */
    protected function startRendering(bool $openFirstPage = true): void
    {
        $this->initFrontend();
        $this->initRenderMode()
             ->initInDesignDocument();
        if ($openFirstPage) {
            $this->addCommand(new GoToPage(1, false));
        }
    }

    /**
     * Initializes Pimcore frontend for rendered publication.
     *
     * @throws \Exception
     */
    protected function initFrontend(): void
    {
        $this->setPimcoreLocales();
    }

    /**
     * Sets current rendered language as locale in Request and Pimcore services.
     *
     * @throws \Exception
     */
    protected function setPimcoreLocales(): void
    {
        $locale = $this->getLanguage();
        if (false === Tool::isValidLanguage($locale)) {
            throw new \Exception("Language '$locale' is no valid Pimcore language.");
        }
        $this->requestHelper->getRequest()
                            ->setLocale($locale);

        $localeService = \Pimcore::getKernel()
                                 ->getContainer()
                                 ->get('pimcore.locale');
        if ($localeService instanceof LocaleService) {
            $localeService->setLocale($locale);
        }

        $service = \Pimcore::getKernel()
                           ->getContainer()
                           ->get(IntlFormatter::class);
        if ($service instanceof IntlFormatter) {
            $service->setLocale($locale);
        }
    }

    /**
     * Convenience method that ends the rendering process by sending a RemoveEmptyLayers command.
     *
     * @return AbstractProject
     * @throws \Exception
     */
    protected function stopRendering(): AbstractProject
    {
        $this->addCommand(new RemoveEmptyLayers());

        return $this;
    }

    /**
     * Sets PHP settings for generation mode.
     *
     * @return AbstractProject
     * @throws \Exception
     */
    protected function initRenderMode(): AbstractProject
    {
        $this->setPhpSettings();
        $this->setNumericLocale();

        return $this;
    }

    /**
     * Sets PHP settings.
     *
     * @throws \Exception
     */
    protected function setPhpSettings(): void
    {
        set_time_limit(
            $this->config()
                 ->offsetGet('php_time_limit')
        );
        ini_set(
            'memory_limit',
            $this->config()
                 ->offsetGet('php_memory_limit')
        );
    }

    /**
     * Sets locale for LC_NUMERIC according to PimPrint configuration.
     *
     * @throws \Exception
     */
    protected function setNumericLocale(): void
    {
        $locales = $this->config()
                        ->offsetGet('lc_numeric');
        if (empty($locales)) {
            return;
        }
        setlocale(LC_NUMERIC, $locales);
    }

    /**
     * Opens a new InDesign document and loads the InDesign template parameter template file.
     *
     * @return AbstractProject
     * @throws \Exception
     */
    final protected function initInDesignDocument(): AbstractProject
    {
        $template = $this->getTemplate();
        if ($template instanceof Asset) {
            $template = $template->getFilename();
        }
        //Declares current open InDesign document as target document to generate publication in.
        $this->addCommand(new OpenDocument(OpenDocument::TYPE_USECURRENT))
            //opens the InDesign template document.
             ->addCommand(new OpenDocument(OpenDocument::TYPE_TEMPLATE, '0', $template))
             ->addCommand(new Variable('GENERATED_AT', time()));

        return $this;
    }
}
