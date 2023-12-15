<?php

declare(strict_types=1);

namespace Mistralys\Examples;

use AppUtils\Collections\BaseIntegerPrimaryCollection;

/**
 * @method HerbRecord getByID(int $id)
 * @method HerbRecord[] getAll()
 * @method HerbRecord getDefault()
 */
class HerbsCollection extends BaseIntegerPrimaryCollection
{
    public const BASIL = 1;
    public const THYME = 2;
    public const ROSEMARY = 3;
    public const SAGE = 4;
    public const OREGANO = 5;
    public const PARSLEY = 6;
    public const CHIVES = 7;
    public const DILL = 8;
    public const MINT = 9;
    public const TARRAGON = 10;
    public const CILANTRO = 11;
    public const LAVENDER = 12;
    public const LEMON_BALM = 13;
    public const MARJORAM = 14;
    public const CHAMOMILE = 15;

    public const DEFAULT = self::BASIL;

    private static ?HerbsCollection $instance = null;

    public static function getInstance() : HerbsCollection
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getDefaultID(): int
    {
        return self::DEFAULT;
    }

    protected function registerItems(): void
    {
        $this->registerItem(
            new HerbRecord(
                self::BASIL,
                t('Basil'),
                5,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::THYME,
                t('Thyme'),
                7,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::ROSEMARY,
                t('Rosemary'),
                7,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::SAGE,
                t('Sage'),
                13,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::OREGANO,
                t('Oregano'),
                8,
                false
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::PARSLEY,
                t('Parsley'),
                26,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::CHIVES,
                t('Chives'),
                63,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::DILL,
                t('Dill'),
                5,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::MINT,
                t('Mint'),
                11,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::TARRAGON,
                t('Tarragon'),
                22,
                false
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::CILANTRO,
                t('Cilantro'),
                39,
                false
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::LAVENDER,
                t('Lavender'),
                16,
                false
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::LEMON_BALM,
                t('Lemon Balm'),
                32,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::MARJORAM,
                t('Marjoram'),
                6,
                true
            )
        );

        $this->registerItem(
            new HerbRecord(
                self::CHAMOMILE,
                t('Chamomile'),
                12,
                true
            )
        );
    }
}
