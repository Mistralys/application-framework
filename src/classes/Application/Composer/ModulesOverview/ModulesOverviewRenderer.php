<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\ModulesOverview;

/**
 * Renders a Markdown overview document from a collection of {@see ModuleInfo}
 * objects. Modules are grouped by Composer package, sorted alphabetically
 * within each group. Packages themselves are sorted alphabetically.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModulesOverviewRenderer
{
    /**
     * @var ModuleInfo[]
     */
    private array $modules;

    /**
     * @param ModuleInfo[] $modules
     */
    public function __construct(array $modules)
    {
        $this->modules = $modules;
    }

    /**
     * Builds and returns the complete Markdown document string.
     *
     * @return string
     */
    public function render() : string
    {
        // NOTE: "\n" is used intentionally — PHP_EOL must NOT be used here
        // because the generated file must have OS-independent (LF) line endings.
        $grouped = $this->groupAndSort();

        $lines = array();
        $lines[] = $this->renderHeader($grouped);

        foreach($grouped as $packageName => $packageModules) {
            $lines[] = $this->renderPackageSection($packageName, $packageModules);
        }

        $lines[] = $this->renderRelationshipSection($this->modules);

        return implode("\n\n", $lines) . "\n";
    }

    /**
     * Groups all modules by Composer package name and sorts both packages
     * and modules alphabetically.
     *
     * @return array<string, ModuleInfo[]>
     */
    private function groupAndSort() : array
    {
        $grouped = array();

        foreach($this->modules as $module) {
            $pkg = $module->getComposerPackage();
            if(!isset($grouped[$pkg])) {
                $grouped[$pkg] = array();
            }
            $grouped[$pkg][] = $module;
        }

        ksort($grouped);

        foreach($grouped as $pkg => $pkgModules) {
            usort($pkgModules, static function(ModuleInfo $a, ModuleInfo $b) : int {
                return strcmp($a->getId(), $b->getId());
            });
            $grouped[$pkg] = $pkgModules;
        }

        return $grouped;
    }

    /**
     * Renders the document title, generation timestamp, and module summary.
     *
     * @param array<string, ModuleInfo[]> $grouped
     * @return string
     */
    private function renderHeader(array $grouped) : string
    {
        $total = count($this->modules);
        $packageCount = count($grouped);

        $lines = array();
        $lines[] = '# Modules Overview';
        $lines[] = '';
        $lines[] = '> Auto-generated on ' . date('Y-m-d H:i:s') . '. Do not edit manually.';
        $lines[] = '';
        $lines[] = 'Total: ' . $total . ' module' . ($total !== 1 ? 's' : '') . ' across ' . $packageCount . ' package' . ($packageCount !== 1 ? 's' : '') . '.';

        return implode("\n", $lines);
    }

    /**
     * Renders a section heading and module table for one Composer package.
     *
     * @param string       $packageName
     * @param ModuleInfo[] $modules
     * @return string
     */
    private function renderPackageSection(string $packageName, array $modules) : string
    {
        $lines = array();
        $lines[] = '## ' . $packageName;
        $lines[] = '';
        $lines[] = $this->renderModuleTable($modules);

        return implode("\n", $lines);
    }

    /**
     * Renders the Markdown table header and rows for a group of modules.
     *
     * @param ModuleInfo[] $modules
     * @return string
     */
    private function renderModuleTable(array $modules) : string
    {
        $lines = array();
        $lines[] = '| ID | Label | Description | Source Path | Context Docs | Related Modules |';
        $lines[] = '|----|-------|-------------|-------------|--------------|-----------------|';

        foreach($modules as $module) {
            $relatedModules = $module->getRelatedModules();
            $related = !empty($relatedModules) ? implode(', ', $relatedModules) : '—';

            $lines[] = sprintf(
                '| `%s` | %s | %s | `%s` | `%s` | %s |',
                $module->getId(),
                $module->getLabel(),
                $module->getDescription(),
                rtrim($module->getSourcePath(), '/') . '/',
                rtrim($module->getContextOutputFolder(), '/') . '/',
                $related
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Renders cross-module relationships as a bulleted list.
     * Only includes modules that have at least one related module.
     *
     * @param ModuleInfo[] $allModules
     * @return string
     */
    private function renderRelationshipSection(array $allModules) : string
    {
        // Sort by ID for consistent output
        $sorted = $allModules;
        usort($sorted, static function(ModuleInfo $a, ModuleInfo $b) : int {
            return strcmp($a->getId(), $b->getId());
        });

        $lines = array();
        $lines[] = '## Module Relationships';
        $lines[] = '';

        $hasAny = false;

        foreach($sorted as $module) {
            if(empty($module->getRelatedModules())) {
                continue;
            }

            $hasAny = true;
            $lines[] = sprintf(
                '- **%s** → %s',
                $module->getId(),
                implode(', ', $module->getRelatedModules())
            );
        }

        if(!$hasAny) {
            $lines[] = '_No cross-module relationships defined._';
        }

        return implode("\n", $lines);
    }
}
