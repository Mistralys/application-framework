<?php

declare(strict_types=1);

namespace Application\UI\Form\Element\DateTimePicker;

use AppUtils\Interfaces\StringableInterface;
use UI_Exception;

class BasicTime implements StringableInterface
{
    public const int ERROR_INVALID_TIME = 145701;

    private int $hour;
    private int $minutes;

    /**
     * @param int $hour
     * @param int $minutes
     * @throws UI_Exception {@see self::ERROR_INVALID_TIME}
     */
    public function __construct(int $hour, int $minutes)
    {
        $this->hour = $hour;
        $this->minutes = $minutes;

        if(
            ($hour < 0 || $hour > 23)
            ||
            ($minutes < 0 || $minutes > 59)
        ) {
            throw new UI_Exception(
                'Invalid time.',
                sprintf(
                    'Hour or minutes ou of bounds: %02d:%02d',
                    $hour,
                    $minutes
                ),
                self::ERROR_INVALID_TIME
            );
        }
    }

    public function getAsString(): string
    {
        return sprintf(
            '%02d:%02d',
            $this->getHour(),
            $this->getMinutes()
        );
    }

    public function getHour() : ?int
    {
        return $this->hour;
    }

    public function getMinutes() : ?int
    {
        return $this->minutes;
    }

    public function __toString(): string
    {
        return $this->getAsString();
    }
}
