<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Submode;

use Application\Development\DevScreenRights;
use Application\Renamer\Index\RenamerIndex;
use Application\Renamer\Index\RenamerRecord;
use Application\Renamer\RenamingManager;
use Application\Traits\AllowableMigrationTrait;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use DBHelper\Admin\Screens\Submode\BaseRecordListSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record;
use Maileditor\Renamer\RenamerConfig;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_Badge;
use UI_DataGrid_Entry;
use UI_Themes_Theme_ContentRenderer;

class BaseResultsSubmode extends BaseRecordListSubmode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'results';
    public const string COL_COLUMN = 'column';
    public const string COL_MATCHED_TEXT = 'matched_text';
    public const string COL_MATCHES = 'matches';

    private RenamerConfig $config;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_RENAMER_RESULTS;
    }

    public function getNavigationTitle(): string
    {
        return t('Results');
    }

    public function getTitle(): string
    {
        return t('Results');
    }

    protected function validateRequest(): void
    {
        $this->config = BaseConfigurationSubmode::getConfig();
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('new_search', t('New Search'))
            ->setIcon(UI::icon()->search())
            ->link(RenamingManager::getInstance()->adminURL()->configuration(true));

        $this->sidebar->addButton('export', t('Export Results'))
            ->setIcon(UI::icon()->export())
            ->link(RenamingManager::getInstance()->adminURL()->export());

        $this->sidebar->addButton('replace', t('Replace').'...')
            ->setIcon(UI::icon()->edit())
            ->setTooltip(t('Open the replace screen to replace the matches that were found.'))
            ->link(RenamingManager::getInstance()->adminURL()->replace());

        $this->sidebar->addSeparator();

        $this->sidebar->addCustom(sb()

        );

        $this->sidebar->addSeparator();

        parent::_handleSidebar();
    }

    public static function configureSubline(UI_Themes_Theme_ContentRenderer $renderer, RenamerConfig $config) : void
    {
        $renderer->getTitle()
            ->setSubline(sb()
                ->t('Search for:')->code(htmlspecialchars($config->getSearch()))
                ->nl()
                ->t('Created on:')->add(ConvertHelper::date2listLabel($config->getDate(), true, true))
                ->ifTrue($config->isCaseSensitive(), function() : string {
                    return (string)sb()->nl()->add(UI::label(t('Case sensitive')));
                })
            );
    }

    protected function _handleHelp(): void
    {
        $collection = $this->createCollection();

        self::configureSubline($this->renderer, $this->config);

        $this->renderer->setAbstract(
            t(
                'The search found a total of %1$s matches in the database, for %2$s unique texts.',
                $collection->countRecords(),
                $collection->countAllRecords()
            )
        );
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return RenamingManager::getInstance()->adminURL()->configuration();
    }

    protected function createCollection(): RenamerIndex
    {
        return RenamingManager::getInstance()->createCollection();
    }

    protected function getEntryData(DBHelperRecordInterface $record, DBHelper_BaseFilterCriteria_Record $entry): array|UI_DataGrid_Entry
    {
        $item = ClassHelper::requireObjectInstanceOf(
            RenamerRecord::class,
            $record
        );

        return array(
            self::COL_MATCHES => $item->countMatches(),
            self::COL_COLUMN => $item->getColumn()->getLabel(),
            self::COL_MATCHED_TEXT => $this->renderMatchedText($item)
        );
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_MATCHES, t('Matches'))->alignRight();
        $this->grid->addColumn(self::COL_COLUMN, t('DB Column'))->setNowrap();
        $this->grid->addColumn(self::COL_MATCHED_TEXT, t('Matched Text'));
    }

    protected function configureActions(): void
    {
    }

    private function renderMatchedText(RenamerRecord $item) : string
    {
        $search = $this->config->getSearch();
        $text = $item->loadMatchedText();

        // If there's no search term, just escape and insert word-break hints
        if ($search === '') {
            return $this->escapeWithWbr($text);
        }

        // Build a case-insensitive, unicode-aware pattern. We capture the match
        // so preg_split returns matched segments as separate elements.
        $pattern = '/(' . preg_quote($search, '/') . ')/iu';

        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            // Fallback: escape whole string and return
            return $this->escapeWithWbr($text);
        }

        $out = '';
        foreach ($parts as $index => $part) {
            if ($index % 2 === 1) {
                // This is a matched segment. Escape it (preserves original case),
                // add wbrs, then wrap with the existing highlight markup.
                $out .= (string)sb()->bold(sb()->warning($this->escapeWithWbr($part)));
            } else {
                // Non-matched segment: just escape and add wbrs.
                $out .= $this->escapeWithWbr($part);
            }
        }

        return $out;
    }

    /**
     * Escape HTML special characters and insert <wbr> hints after '}' and '>'
     * so long strings break safely in the UI.
     */
    private function escapeWithWbr(string $text): string
    {
        $escaped = htmlspecialchars($text);
        return str_replace(
            array('}', '>'),
            array('}<wbr>', '><wbr>'),
            $escaped
        );
    }
}
