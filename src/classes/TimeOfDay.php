<?php

declare(strict_types=1);

class TimeOfDay
{
    const TIME_EARLY_MORNING = 'early_morning';
    const TIME_MORNING = 'morning';
    const TIME_LATE_MORNING = 'late_morning';
    const TIME_NOON = 'noon';
    const TIME_EARLY_AFTERNOON = 'early_afternoon';
    const TIME_AFTERNOON = 'afternoon';
    const TIME_LATE_AFTERNOON = 'late_afternoon';
    const TIME_EARLY_EVENING = 'early_evening';
    const TIME_EVENING = 'evening';
    const TIME_NIGHT = 'night';

    /**
     * Specifies the hour until
     * @var string[]
     */
    protected static $times = array(
        0 => self::TIME_NIGHT,
        5 => self::TIME_EARLY_MORNING,
        8 => self::TIME_MORNING,
        11 => self::TIME_LATE_MORNING,
        12 => self::TIME_NOON,
        13 => self::TIME_EARLY_AFTERNOON,
        15 => self::TIME_EVENING,
        16 => self::TIME_LATE_AFTERNOON,
        17 => self::TIME_EARLY_EVENING,
        19 => self::TIME_EVENING,
        21 => self::TIME_NIGHT,
        99 => self::TIME_NIGHT
    );

    /**
     * @var DateTime
     */
    private $time;

    /**
     * @var string
     */
    private $type;

    public function __construct(DateTime $time)
    {
        $this->time = $time;
        $this->type = $this->resolveType();
    }

    /**
     * @return DateTime
     */
    public function getTime(): DateTime
    {
        return $this->time;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function isNight() : bool
    {
        return $this->hasAnyType(self::TIME_NIGHT);
    }

    public function isMorning() : bool
    {
        return $this->hasAnyType(
          self::TIME_EARLY_MORNING,
          self::TIME_MORNING,
          self::TIME_LATE_MORNING
        );
    }

    public function isAfternoon() : bool
    {
        return $this->hasAnyType(
            self::TIME_EARLY_AFTERNOON,
            self::TIME_AFTERNOON,
            self::TIME_LATE_AFTERNOON
        );
    }

    public function isEvening() : bool
    {
        return $this->hasAnyType(
            self::TIME_EARLY_EVENING,
            self::TIME_EVENING
        );
    }

    public function isNoon() : bool
    {
        return $this->hasAnyType(self::TIME_NOON);
    }

    protected function hasAnyType(...$types) : bool
    {
        return in_array($this->type, $types);
    }

    protected function resolveType() : string
    {
        $hour = (int)$this->time->format('h');

        $prev = '';
        foreach (self::$times as $until => $type)
        {
            if($hour < $until)
            {
                return $prev;
            }

            $prev = $type;
        }

        return self::TIME_NIGHT;
    }
}

