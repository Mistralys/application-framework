<?php
/**
 * @package Ratings
 * @subpackage Screens
 */

declare(strict_types=1);

namespace Application\Ratings\Screens;

use Application_Driver;
use AppUtils\ConvertHelper\JSONConverter;
use DBHelper;
use DBHelper_BaseCollection;
use RatingScreenRecord;

/**
 * Collection manager for the rating screens that users have accessed:
 * each time a rating is added, the details on the requested page are
 * stored in the global screens list. The user's rating only references
 * the screen entry.
 *
 * @package Ratings
 * @subpackage Screens
 * @author Sebastian Mordziol <s.mordziol@gmail.com>
 *
 * @method RatingScreenRecord getByID(int $screenID)
 * @method RatingScreenRecord createNewRecord(array $data, bool $silent = false)
 */
class RatingScreensCollection extends DBHelper_BaseCollection
{
    public const string COL_PATH = 'path';
    public const string COL_DISPATCHER = 'dispatcher';
    public const string COL_PARAMS = 'params';
    public const string COL_HASH = 'hash';
    public const string TABLE_NAME = 'app_ratings_screens';
    public const string PRIMARY_NAME = 'rating_screen_id';
    public const string RECORD_TYPE_NAME = 'app_rating_screen';

    public function getRecordClassName(): string
    {
        return RatingScreenRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return RatingScreensFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return RatingScreensFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_PATH;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_PATH => t('Path'),
            self::COL_DISPATCHER => t('Dispatcher file'),
            self::COL_PARAMS => t('Parameters')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return self::RECORD_TYPE_NAME;
    }

    public function getCollectionLabel(): string
    {
        return t('Application rating screens');
    }

    public function getRecordLabel(): string
    {
        return t('Application rating screen');
    }

    /**
     * Creates a screen instance for the target URL. Automatically creates
     * a new screen entry in the database if it does not exist yet.
     *
     * @param string $screenURL
     * @return RatingScreenRecord
     */
    public function createNewByURL(string $screenURL): RatingScreenRecord
    {
        $info = Application_Driver::getInstance()->parseURL($screenURL);
        $hash = $info->getHash();

        $screen = $this->getByHash($hash);
        if ($screen) {
            return $screen;
        }

        $data = array(
            self::COL_HASH => $hash,
            self::COL_DISPATCHER => $info->getDispatcher(),
            self::COL_PATH => $info->getScreenPath(),
            self::COL_PARAMS => ''
        );

        if ($info->hasParams()) {
            $data[self::COL_PARAMS] = JSONConverter::var2json($info->getParams());
        }

        return $this->createNewRecord($data);
    }

    /**
     * Attempts to retrieve a screen entry by a URL hash.
     *
     * @param string $hash
     * @return RatingScreenRecord|NULL
     */
    public function getByHash(string $hash): ?RatingScreenRecord
    {
        $id = DBHelper::fetchKeyInt(
            self::PRIMARY_NAME,
            "SELECT
                rating_screen_id
            FROM
                app_ratings_screens
            WHERE
                hash=:hash",
            array(
                self::COL_HASH => $hash
            )
        );

        if (!empty($id)) {
            return $this->getByID($id);
        }

        return null;
    }
}
