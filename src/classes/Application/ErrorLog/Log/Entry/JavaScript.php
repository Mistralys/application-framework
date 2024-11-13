<?php

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;

class Application_ErrorLog_Log_Entry_JavaScript extends Application_ErrorLog_Log_Entry
{
    public const KEY_CODE = 'code';
    public const KEY_REFERER = 'referer';
    public const KEY_COLUMN = 'column';
    public const KEY_LINE = 'line';
    public const KEY_DETAILS = 'details';
    public const KEY_LOG_LINES = 'log_lines';
    public const KEY_MESSAGE = 'message';
    public const KEY_URL = 'url';
    public const KEY_TYPE = 'type';
    public const KEY_CALL_TRACE = 'call_trace';

    public const INDEX_CODE = 0;
    public const INDEX_MESSAGE = 2;
    public const INDEX_DETAILS = 3;
    public const INDEX_REFERER = 4;
    public const INDEX_URL = 5;
    public const INDEX_LINE = 6;
    public const INDEX_COLUMN = 7;

    public function getTypeLabel() : string
    {
        return t('JavaScript');
    }
    
    public function getCode() : int
    {
        return (int)$this->getTokenIndex(self::INDEX_CODE);
    }
    
    public function getMessage() : string
    {
        return $this->getTokenIndex(self::INDEX_MESSAGE);
    }
    
    public function getDetails() : string
    {
        return $this->getTokenIndex(self::INDEX_DETAILS);
    }
    
    public function getReferer() : string
    {
        return $this->getTokenIndex(self::INDEX_REFERER);
    }
    
    public function getSource() : string
    {
        return $this->getTokenIndex(self::INDEX_URL);
    }
    
    public function getLine() : int
    {
        $val = $this->getTokenIndex(self::INDEX_LINE);
        if(!empty($val)) {
            return (int)$val;
        }
        
        return 0;
    }
    
    public function getColumn() : int
    {
        $val = $this->getTokenIndex(self::INDEX_COLUMN);
        if(!empty($val)) {
            return (int)$val;
        }
        
        return 0;
    }

    public function addProperties(UI_PropertiesGrid $grid) : void
    {
        $grid->add(t('Line'), $this->getLine());
        $grid->add(t('Column'), $this->getColumn());
        $grid->add(t('Details'), $this->getDetails());
        $grid->add(t('Source'), $this->getSource())->setHelpText(t('The URL or file in which the error happened - not always available.'));
    }

    /**
     * @param array<string,string|array|int|float|bool|null> $rawData
     * @return void
     */
    public static function logError(array $rawData) : void
    {
        $data = ArrayDataCollection::create($rawData);

        // 0 = code
        // 1 = type
        // 2 = message
        // 3 = details,
        // 4 = referer,
        // 5 = url
        // 6 = line
        // 7 = column

        $tokens = array(
            $data->getInt(self::KEY_CODE),
            $data->getString(self::KEY_TYPE),
            $data->getString(self::KEY_MESSAGE),
            $data->getString(self::KEY_DETAILS),
            $data->getString(self::KEY_REFERER),
            $data->getString(self::KEY_URL),
            $data->getInt(self::KEY_LINE),
            $data->getInt(self::KEY_COLUMN)
        );

        $errorLog = AppFactory::createErrorLog();
        $logID = $errorLog->logJavascriptError($tokens);

        $lines = $data->getArray(self::KEY_LOG_LINES);
        $lines[] = '';
        $lines[] = 'Stack trace:';
        $lines[] = $data->getString(self::KEY_CALL_TRACE);
        $lines[] = '';
        $lines[] = 'Raw JSON error data:';
        $lines[] = JSONConverter::var2json($rawData);

        $errorLog->writeLogfile($logID, $lines);
    }
}
