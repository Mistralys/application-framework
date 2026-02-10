<?php

declare(strict_types=1);

namespace Application\TimeTracker\AutoFiller;

use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\DateTimeHelper\DaytimeStringInfo;
use UI;
use UI_Badge;

class WorkBlock
{
    public const string TYPE_GENERATED = 'generated';
    public const string TYPE_OCCUPIED = 'occupied';

    public const string KEY_TYPE = 'type';
    public const string KEY_START_TIME = 'startTime';
    public const string KEY_DURATION = 'duration';
    private string $type;
    private DaytimeStringInfo $startTime;
    private int $duration;

    public function __construct(string $type, DaytimeStringInfo $startTime, int $duration)
    {
        $this->type = $type;
        $this->startTime = $startTime;
        $this->duration = $duration;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isOccupied() : bool
    {
        return $this->getType() === self::TYPE_OCCUPIED;
    }

    public function isGenerated() : bool
    {
        return $this->getType() === self::TYPE_GENERATED;
    }

    public function getStartTime(): DaytimeStringInfo
    {
        return $this->startTime;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getDurationPretty() : string
    {
        return ConvertHelper::interval2string(ConvertHelper::seconds2interval($this->getDuration()));
    }

    public function getEndTime(): DaytimeStringInfo
    {
        return DaytimeStringInfo::fromSeconds($this->getStartTime()->getTotalSeconds() + $this->getDuration());
    }

    public function serialize() : array
    {
        return array(
            self::KEY_TYPE => $this->getType(),
            self::KEY_START_TIME => $this->getStartTime()->getNormalized(),
            self::KEY_DURATION => $this->getDuration(),
        );
    }

    public static function fromSerialized(array $data) : WorkBlock
    {
        $info = ArrayDataCollection::create($data);

        return new self(
            $info->getString(self::KEY_TYPE),
            DaytimeStringInfo::fromString($info->getString(self::KEY_START_TIME)),
            $info->getInt(self::KEY_DURATION)
        );
    }

    public function getBadge() : UI_Badge
    {
        if($this->isOccupied()) {
            return UI::label('Existing')->makeInactive();
        }

        return UI::label('Generated')->makeSuccess();
    }
}
