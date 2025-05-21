<?php

use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization;
use AppUtils\ConvertHelper;

class Application_Maintenance_Plan
{
    /**
     * @var Application_Maintenance
     */
    protected $maintenance;
    
    /**
     * @var DateTime
     */
    protected $start;
    
    /**
     * @var DateInterval
     */
    protected $duration;
    
   /**
    * @var string
    */
    protected $durationString;
    
   /**
    * @var array<string,string>
    */
    protected $infoTexts;
    
    /**
     * @var DateTime
     */
    protected $end;
    
   /**
    * @var integer
    */
    protected $id;
    
    public function __construct(Application_Maintenance $maintenance, int $id, DateTime $start, string $durationString)
    {
        $this->id = $id;
        $this->maintenance = $maintenance;
        $this->start = $start;
        $this->durationString = $durationString;
    }
    
    public function getID() : int
    {
        return $this->id;
    }
    
    /**
     * Checks whether this plan is currently enabled.
     * @return boolean
     */
    public function isEnabled()
    {
        $now = new DateTime();
        
        if($this->start > $now) {
            return false;
        }
        
        $end = $this->getEnd();
        if($now < $end) {
            return true;
        }
        
        return false;
    }
    
    public function hasInfoText(?LocaleInterface $locale=null)
    {
        if(!$locale) {
            $locale = Localization::getAppLocale();
        }
        
        $name = $locale->getName();
        return isset($this->infoTexts[$name]);
    }
    
    public function getInfoText(?LocaleInterface $locale=null) : string
    {
        if(!$locale) 
        {
            $locale = Localization::getAppLocale();
        }
        
        $name = $locale->getName();
        if(isset($this->infoTexts[$name])) {
            return $this->infoTexts[$name];
        }
        
        return '';
    }
    
    public function setInfoText(LocaleInterface $locale, string $text) : Application_Maintenance_Plan
    {
        $this->infoTexts[$locale->getName()] = $text;
        return $this;
    }
    
    public function getStart() : DateTime
    {
        return $this->start;
    }
    
    /**
     * The ending date of the maintenance plan.
     * @return DateTime
     */
    public function getEnd() : DateTime
    {
        if(!isset($this->end)) {
            $this->end = new DateTime($this->start->format(DateTime::ISO8601));
            $this->end->add($this->getDuration());
        }
        
        return $this->end;
    }
    
    /**
     * The duration of the maintenance plan, starting from the start time.
     * @return DateInterval
     */
    public function getDuration() : DateInterval
    {
        if(!isset($this->duration)) {
            $this->duration = DateInterval::createFromDateString($this->durationString);
        }
        
        return $this->duration;
    }
    
   /**
    * @return array<string,mixed>
    */
    public function serialize() : array
    {
        return array(
            'id' => $this->id,
            'start' => $this->start->format(DateTime::ISO8601),
            'duration' => $this->durationString,
            'infoTexts' => $this->infoTexts
        );
    }
    
    public function isValid() : bool
    {
        $now = new DateTime();
        
        if($this->getEnd() > $now) {
            return true;
        }
        
        return false;
    }
    
    public function getEnabledBadge() : string
    {
        if($this->isEnabled()) {
            return UI::label(mb_strtoupper(t('Enabled')))
            ->makeWarning().' '.
            t('Ends in %1$s', ConvertHelper::interval2string($this->getTimeLeft()));
        }
        
        return UI::label(mb_strtoupper(t('Inactive')))
        ->makeInactive().' '.
        t('Starts in %1$s', ConvertHelper::interval2string($this->getCountdown()));
    }
    
    public function getTimeLeft() : DateInterval
    {
        $now = new DateTime();
        return $this->getEnd()->diff($now);
    }
    
    public function getCountdown() : DateInterval
    {
        $now = new DateTime();
        return $now->diff($this->start);
    }
}
