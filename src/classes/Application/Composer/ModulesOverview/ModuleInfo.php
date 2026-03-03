<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\ModulesOverview;

/**
 * Immutable value object holding parsed metadata for a single
 * module discovered from a `module-context.yaml` file.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModuleInfo
{
    /**
     * @var string[]
     */
    private array $relatedModules;

    /**
     * @var string[]
     */
    private array $keywords;

    private string $id;
    private string $label;
    private string $description;
    private string $sourcePath;
    private string $contextOutputFolder;
    private string $composerPackage;

    /**
     * @param string   $id                   Module ID (e.g. `comtypes`).
     * @param string   $label                Human-readable label (e.g. `Communication Types`).
     * @param string   $description          One-sentence description.
     * @param string[] $relatedModules       List of related module IDs (may be empty).
     * @param string   $sourcePath           Relative path to the module directory from the project root.
     * @param string   $contextOutputFolder  Relative path to the CTX output folder under `.context/modules/`.
     * @param string   $composerPackage      Composer package name (e.g. `com.ionos.communication/maileditor`).
     * @param string[] $keywords             Domain-specific keywords declared in the module's `module-context.yaml`.
     */
    public function __construct(
        string $id,
        string $label,
        string $description,
        array  $relatedModules,
        string $sourcePath,
        string $contextOutputFolder,
        string $composerPackage,
        array  $keywords = array()
    )
    {
        $this->id                  = $id;
        $this->label               = $label;
        $this->description         = $description;
        $this->relatedModules      = $relatedModules;
        $this->sourcePath          = $sourcePath;
        $this->contextOutputFolder = $contextOutputFolder;
        $this->composerPackage     = $composerPackage;
        $this->keywords            = $keywords;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @return string[]
     */
    public function getRelatedModules() : array
    {
        return $this->relatedModules;
    }

    public function getSourcePath() : string
    {
        return $this->sourcePath;
    }

    public function getContextOutputFolder() : string
    {
        return $this->contextOutputFolder;
    }

    public function getComposerPackage() : string
    {
        return $this->composerPackage;
    }

    /**
     * Returns the list of domain-specific keywords declared for this module
     * in its `module-context.yaml` `moduleMetaData.keywords` field.
     *
     * @return string[]
     */
    public function getKeywords() : array
    {
        return $this->keywords;
    }
}
