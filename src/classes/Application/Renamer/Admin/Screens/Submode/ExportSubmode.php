<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Submode;

use Application;
use Application\Admin\Area\Mode\BaseSubmode;
use Application\Renamer\Admin\RenamerScreenRights;
use Application\Renamer\Admin\Traits\RenamerSubmodeInterface;
use Application\Renamer\Admin\Traits\RenamerSubmodeTrait;
use Application\Renamer\RenamerException;
use Application\Renamer\RenamingManager;

class ExportSubmode extends BaseSubmode implements RenamerSubmodeInterface
{
    use RenamerSubmodeTrait;

    public const string URL_NAME = 'export';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return RenamerScreenRights::SCREEN_RENAMER_EXPORT;
    }

    public function getNavigationTitle(): string
    {
        return t('Export');
    }

    public function getTitle(): string
    {
        return t('Export');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    /**
     * Custom CSV writing logic to handle large datasets without exhausting memory:
     * The export is streamed directly to the output buffer in chunks, flushing periodically.
     *
     * @return never
     */
    protected function _handleActions(): never
    {
        $headers = array(
            'Column ID',
            'Hash',
            'Primary values',
            'Matched Text',
        );

        $collection = RenamingManager::getInstance()->createCollection();
        $ids = $collection->getFilterCriteria()->getIDs();

        // Prepare a filename with timestamp
        $filename = date('Y-m-d-H-i-s').'-renamer-export.csv';

        // Send headers for CSV download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache');

        // Open output stream and write CSV rows incrementally to avoid memory issues
        $out = fopen('php://output', 'wb');

        if ($out === false) {
            throw new RenamerException(
                'Failed to open output stream for CSV export.',
                '',
                RenamerException::ERROR_EXPORT_CANNOT_OPEN_OUTPUT
            );
        }

        // Write a UTF-8 BOM so Excel recognizes UTF-8 correctly
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers, ',', '"', "", PHP_EOL);

        // Iterate over IDs and write each record's export columns
        $counter = 0;
        foreach ($ids as $id)
        {
            $counter++;

            fputcsv($out, $collection->getByID($id)->toExportColumns(), ',', '"', "", PHP_EOL);

            // Free memory related to loaded objects and reset collection every 30 records
            if ($counter % 30 === 0) {
                $collection->resetCollection();

                // Flush output buffers so data is sent progressively to the client
                fflush($out);
            }
        }

        fflush($out);
        fclose($out);

        Application::exit('Downloaded an export.');
    }
}
