<?php

declare(strict_types=1);

namespace UI\Interfaces;

use UI\Traits\ButtonSizeTrait;

/**
 * @see ButtonSizeTrait
 */
interface ButtonSizeInterface
{
    /**
     * @var array<int,array<string,string>>
     */
    public const BUTTON_SIZES_TABLE = array(
        2 => array(
            self::SIZE_LARGE => 'large',
            self::SIZE_SMALL => 'small',
            self::SIZE_MINI => 'mini'
        ),
        4 => array(
            self::SIZE_LARGE => 'lg',
            self::SIZE_SMALL => 'sm',
            self::SIZE_MINI => 'xs'
        )
    );

    public const SIZE_MINI = 'mini';
    public const SIZE_LARGE = 'large';
    public const SIZE_SMALL = 'small';
    public const ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION = 66601;
    public const ERROR_UNKNOWN_BOOTSTRAP_SIZE = 66602;

    /**
     * @return $this
     */
    public function makeSmall() : self;

    /**
     * @return $this
     */
    public function makeMini() : self;

    /**
     * @return $this
     */
    public function makeLarge() : self;

    /**
     * @param string $size
     * @return $this
     */
    public function makeSize(string $size) : self;

    public function getSize() : ?string;
    public function isLarge() : bool;
    public function isSmall() : bool;
    public function isMini() : bool;
}
