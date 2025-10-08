<?php
/**
 * File containing a collection of global helper functions.
 * @package Application
 * @subpackage Core
 */

use Application\ConfigSettings\BaseConfigRegistry;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\FileHelper;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\XMLHelper;
use function AppUtils\parseURL;

const APP_ERROR_PAGE_TITLE = 'A system error occurred';
const ERROR_OBJECT_SORTBYWEIGHT_METHOD_MISSING = 64401;
const ERROR_OBJECT_SORTBYLABEL_METHOD_MISSING = 64402;

/**
 * Sorts a collection of objects by their weight
 * property. Expects an indexed array with each
 * entry being an object that has a getWeight()
 * method.
 *
 * @param array<int,object> $array
 */
function object_sortByWeight(&$array)
{
	uasort($array, 'callback_object_sortByWeight');
}

/**
 * Sorts a collection of objects by their label
 * property. Expects an indexed array with each
 * entry being an object that has a getLabel()
 * method.
 *
 * @param array<int,object> $array
 */
function object_sortByLabel(&$array)
{
	uasort($array, 'callback_object_sortByLabel');
}

/**
 * Callback function used by {@link object_sortByWeight()}.
 * @param object $a
 * @param object $b
 * @return int
 */
function callback_object_sortByWeight($a, $b) : int
{
    if(!method_exists($a, 'getWeight') || !method_exists($b, 'getWeight'))
    {
        throw new Application_Exception(
            'Target object does not implement the [getWeight] method.',
            '',
            ERROR_OBJECT_SORTBYWEIGHT_METHOD_MISSING
        );
    }
        
	// store these to avoid two function calls in case the
	// first check does not return.
	$weightA = $a->getWeight();
	$weightB = $b->getWeight();

	if($weightA > $weightB) {
		return 1;
	}

	if($weightA < $weightB) {
		return -1;
	}

	return 0;
}

/**
 * Callback function used by {@link object_sortByLabel()}.
 * @param object $a
 * @param object $b
 * @return int
 */
function callback_object_sortByLabel($a, $b)
{
    if(!method_exists($a, 'getLabel') || !method_exists($b, 'getLabel'))
    {
        throw new Application_Exception(
            'Target object does not implement the [getLabel] method.',
            '',
            ERROR_OBJECT_SORTBYLABEL_METHOD_MISSING
        );
    }
    
	return strnatcasecmp($a->getLabel(), $b->getLabel());
}

/**
 * Returns the full URL to an image within the
 * application. Expects an image file name from
 * the theme's images folder.
 *
 * @param string $img
 * @return string
 */
function imageURL($img)
{
    return UI::getInstance()->getTheme()->getImageURL($img);
}

/**
 * Returns a new unique ID for use as element ID in
 * clientside HTML and javascript.
 *
 * @return string
 */
function nextJSID()
{
    $session = Application::getSession();
    
    $value = intval($session->getValue('jsid_counter')) + 1;
    
    $session->setValue('jsid_counter', $value);
    
	return 'EL'.$value;
}

/**
 * Returns the currently active UI object from
 * the application.
 *
 * @return UI
 */
function getUI()
{
	global $app;

	return $app->getUI();
}

/**
 * Takes an array of name => value pairs and generates
 * the corresponding attributes string for use in an
 * HTML tag. Returns the list of attributes with a
 * space in front and back so this can be safely inserted
 * withut spaces around it.
 *
 * @param array $attributes
 * @return string
 */
function compileAttributes( $attributes )
{
	$tokens = array();
	foreach( $attributes as $name => $value ) 
	{
		if(empty($value) && $value!==0) {
			continue;
		}

		// property
		if($name == $value) {
		    $tokens[] = $name;
		} else {
		    $tokens[] = $name.'="'.$value.'"';
		}
	}

	if(empty($tokens)) {
		return '';
	}

	return ' '.implode( ' ', $tokens ).' ';
}

/**
 * Takes an associative array of CSS styles and
 * turns them into a style string that can be used
 * in the HTML style attribute.
 * 
 * @param array $styles
 * @return string
 */
function compileStyles($styles)
{
    $tokens = array();
    foreach($styles as $name => $value) {
        $tokens[] = $name . ':' . $value;
    }
     
    return implode(';', $tokens);
}

/**
 * Retrieves the content type header that has been set
 * up to this moment, or null if none has been specified.
 * 
 * @return string For example "text/html; charset=UTF-8"
 */
function getContentType() : string
{
    $headers = headers_list();
    foreach($headers as $header) {
        $tokens = explode(':', $header);
        if(!stristr($tokens[0], 'content-type')) {
            continue;
        }
    
        return $tokens[1];
    }
    
    return '';
}

function isContentTypeText() : bool
{
    $contentType = getContentType();
    if($contentType && stristr($contentType, 'text/plain')) {
        return true;
    }
    
    return false;
}

/**
 * Checks whether the current script is running from the command line.
 * @return boolean
 */
function isCLI()
{
    return php_sapi_name() == "cli";
}

function isContentTypeHTML()
{
    if(isCLI()) {
        return false;
    }
    
    $contentType = getContentType();
    
    // if no content type has been set specifically so far,
    // we assume HTML.
    if(!$contentType) {
        return true;
    }
    
    if(stristr($contentType, 'text/html')) {
        return true;
    }
    
    return false;
}

/**
 * Serializes the information from an exception to HTML and
 * displays the generated markup as a full HTML page complete
 * with doctype. Intended to be used instead of a regular page.
 * 
 * Note: In case the content type header has been set to something
 * other than <code>text/html</code>, the output will automatically
 * be switched to plain text.
 *
 * @param Throwable $e
 * @return never
 */
function displayError(Throwable $e)
{
    $develinfo = true;
    $output = ob_get_clean();

    if($e instanceof Application_Exception) {
        $output = $e->getPageOutput().$output;
    }

    try
    {
        if(Application::isUserReady()) {
            $user = Application::getUser();
            $develinfo = $user->isDeveloper();
        }
    }
    catch(Exception $ue) {}

    if(isDevelMode()) {
        $develinfo = true;
    }

    $contentType = 'html';
    if(!isContentTypeHTML())
    {
        $contentType = 'txt';
    }
    
    $locations = array();

    if(defined('APP_THEME')) {
        $locations[] = array(APP_URL.'/themes/'.APP_THEME, APP_URL.'/themes/'.APP_THEME);
        $locations[] = array(APP_INSTALL_URL.'/themes/'.APP_THEME, APP_INSTALL_FOLDER.'/themes/'.APP_THEME);
    }
    
    $locations[] = array(APP_URL.'/themes/default', APP_ROOT.'/themes/default');
    $locations[] = array(APP_INSTALL_URL.'/themes/default', APP_INSTALL_FOLDER.'/themes/default');
    
    $themeLocation = null;
    $templateFile = null;
    foreach($locations as $location) 
    {
        $file = $location[1].'/templates/error/'.$contentType.'.php';
        if(file_exists($file)) {
            $templateFile = $file;
            $themeLocation = $location;
            break;
        }
    }

    $error = new Application_ErrorDetails(
        APP_ERROR_PAGE_TITLE, 
        'An unexpected issue came up, and the system decided to stop the current operation to avoid breaking anything. '.
        'While this is certainly inconvenient, we invite you to review the error details below - they may shed some light on possible solutions. ',
        $themeLocation[1] ?? '(unknown)',
        $themeLocation[0] ?? '(unknown)',
        $locations,
        $output,
        $contentType,
        $e, 
        $develinfo
    );
    
    require_once $templateFile;
	
    Application::exit();
}

function renderExceptionInfo(Throwable $e, bool $develinfo=false, bool $html=false, bool $detailed=true) : string
{
    $nl = PHP_EOL;
    if($html) {
        $nl = '<br>';
    }
    
    $type = get_class($e);
    $prev = $e->getPrevious();
    
    if(!empty($prev))
    {
        $type = get_class($prev);
    }
    
    $lines = array();
    $lines[] = 'Error number: #<b>'.$e->getCode().'</b> of type '.$type;
	$lines[] = 'Error Message: <b>'. ConvertHelper::string2utf8($e->getMessage()).'</b>';
	
	if($detailed) 
	{
	   $lines[] = 'Instance ID: <b>'.APP_INSTANCE_ID.'</b> on '.parseURL(APP_URL)->getHost();
	   $lines[] = 'Database: <b>'.APP_DB_NAME.'</b> on '.APP_DB_HOST;
	}
	
	$lines[] = 'Source file: <b>'.basename($e->getFile()).' line '.$e->getLine().'</b>';

	if($detailed)
	{
        $lines[] = 'Source URL: '.getRequestURI();
	}
	
	if($develinfo)
	{
	    $info = null;
	    
	    if(is_callable(array($e, 'getDeveloperInfo'))) 
	    {
	        $info = $e->getDeveloperInfo(); 
		} 
		else if(is_callable(array($e, 'getDetails')))
		{
		    $info = $e->getDetails();
		}
		
		if(empty($info))
		{
		    $info = 'No developer-specific information available.';
		}
		
        if(!$html) {
            $info = strip_tags($info);
        } else {
            $info = nl2br($info);
        }

        $lines[] = '<h4 class="errorpage-header">Developer info</h4>';
        $lines[] = $info;
	}
	
	$code = implode($nl, $lines);
	
	if(!$html) {
	    $code = strip_tags($code);
	}
	
	return $code;
}

function getRequestURI()
{
    if(isCLI()) {
        global $argv;
        return 'cli://'.$argv[0];
    }
    
    return $_SERVER['REQUEST_URI'];
}

/**
 * Shorthand for using the ConvertHelper bool2string
 * method, which will return the false string if an
 * exception occurs.
 *
 * @param boolean|string|int $bool
 * @param bool $yesno
 * @return string
 */
function bool2string($bool, bool $yesno=false) : string
{
    try
    {
        return ConvertHelper::bool2string($bool, $yesno);
    }
    catch (ConvertHelper_Exception $e)
    {
        return bool2string(false, $yesno);
    }
}

/**
 * @param mixed $string
 * @return bool
 */
function string2bool($string) : bool
{
    try
    {
        return ConvertHelper::string2bool($string);
    }
    catch (ConvertHelper_Exception $e)
    {
        return string2bool('false');
    }
}
	
function renderTrace(Throwable $e) : string
{
    $maxFolderDepth = 2; // how many folders to show of the path to the source file
    
    $trace = $e->getTrace();
    $trace = array_reverse($trace, true);
    
    $html = isContentTypeHTML();
    $content = '';
    
    if($html) {
		$content .= 
		'<table class="table table-hover">'.
			'<tbody>';
    } else {
        $content .=
        '----------------------------------------------'.PHP_EOL.
        'FULL TRACE'.PHP_EOL.
        '----------------------------------------------'.PHP_EOL.
        PHP_EOL;
    }
    
    $appRoot = ltrim(str_replace('\\', '/', APP_ROOT), '/');
    
	foreach($trace as $entry) 
	{
		$origin = 'Unknown';
		
		if(isset($entry['file'])) 
		{
		    $info = pathinfo(str_replace('\\', '/', $entry['file']));
		    
		    $folder = str_replace('\\', '/', $info['dirname']);
		    $folder = str_replace($appRoot, '', $folder);
		    $folder = rtrim($folder, '/');
		    
		    $fileName = $info['basename'];
		    
		    if(!empty($folder)) 
		    {
		        $parts = explode('/', $folder);
		        $depth = count($parts);
		        
		        if($depth > $maxFolderDepth) 
		        {
		            $offset = $depth - $maxFolderDepth;
		            $parts = array_slice($parts, $offset);
		            $folder = implode('/', $parts);
		        }
		    }

		    if($html) {
                $origin = '<span title="' . $entry['file'] . '">' . $folder . '/' . $fileName . '</span>:' . $entry['line'];
            } else {
                $origin = $folder . '/' . $fileName . ':' . $entry['line'];
            }
		}

		if($html) {
		    $content .=
		    '<tr>'.
			    '<td style="text-align:right;white-space:nowrap;vertical-align:top;padding:3px 6px">'.$origin.'</td>'.
			    '<td style="width:100%;font-family:monospace;padding:3px 6px">';
		} else {
		    $content .= $origin . ' | '; 
		}

		if(isset($entry['class'])) {
		    if($html) {
			    $content .= '<span style="color:#cf5e20">'.$entry['class'].'</span>'.$entry['type'];
		    } else {
		        $content .= $entry['class'].$entry['type'];
		    }
		}
		
		$args = array();
		if(isset($entry['args']) && !empty($entry['args'])) 
		{
			foreach($entry['args'] as $arg) {
				switch(gettype($arg)) {
					case 'integer':
						$args[] = 
						'<span style="color:#1c2eb1">int </span>'.
						'<span style="color:#ce0237;">'.$arg.'</span>';
						break;

					case 'array':
					    $json = json_encode($arg, JSON_PRETTY_PRINT);
					    if(strlen($json) > 1000) {
					        $json = substr($json, 0, 1000).' [...]';
					    }
						$args[] = 
						'<span style="color:#1c2eb1">array </span>'.
						'<span style="color:#027ace;">'.nl2br($json).'</span>';
						break;

					case 'object':
						$args[] = 
						'<span style="color:#1c2eb1">class </span>'.
						'<span style="color:#cf5e20">' . get_class($arg) . '</span>';
						break;

					case 'string':
					    if($html) {
						  $args[] = '<span style="color:#1fa507;">"'.nl2br(htmlspecialchars($arg)).'"</span>';
					    } else {
					      $args[] = 'string("'.nl2br($arg).'")';
					    }
						break;
						
					case 'boolean':
					    if($html) {
					        $args[] = '<span style="color:#1c2eb1">'.AppUtils\ConvertHelper::bool2string($arg).'</span>';
					    } else {
					        $args[] = AppUtils\ConvertHelper::bool2string($arg);
					    }
					    break;

					case 'NULL':
					    $args[] = '<span style="color:#1c2eb1">null</span>';
					    break;
					    
					default:
					    $args[] = 'unknown '.gettype($arg);
						break;
				}
			}
			
			$func = $entry['function'].'<br>('.
	 			'<div style="margin-left:20px">'.
	 			   implode(',<br>', $args).
		        '</div>'.
 			')';
		} 
		else
		{
		    $func = $entry['function'].'()';
		}
		
		
		if($html) {
		    $content .= $func;
		} else {
		    $content .= strip_tags($func);
		}
		
		if($html) {
    				$content .=
    			'</td>'.
    		'</tr>';
		} else { 
	       $content .= PHP_EOL;
	    }
	}
	
	if($html) {
	            $content .=
			'</tbody>'.
		'</table>';
	} 
		
	return $content;
}

/**
 * Converts the specified amount of seconds into
 * a human readable string split in months, weeks,
 * days, hours, minutes and seconds.
 *
 * @param float $seconds
 * @return string
 */
function convert_time2string($seconds)
{
	static $units = null;
	if(is_null($units)) {
		$units = array(
			array(
				'value' => 31*7*24*3600,
				'singular' => t('month'),
				'plural' => t('months')
			),
			array(
				'value' => 7*24*3600,
				'singular' => t('week'),
				'plural' => t('months')
			),
			array(
				'value' => 24*3600,
				'singular' => t('day'),
				'plural' => t('days')
			),
			array(
				'value' => 3600,
				'singular' => t('hour'),
				'plural' => t('hours')
			),
			array(
				'value' => 60,
				'singular' => t('minute'),
				'plural' => t('minutes')
			),
			array(
				'value' => 1,
				'singular' => t('second'),
				'plural' => t('seconds')
			)
		);
	}

	// specifically handle zero
	if($seconds <= 0 ) {
		return '0 '.t('seconds');
	}

	$tokens = array();
	foreach($units as $def) {
		$quot = intval($seconds/$def['value']);
		if($quot) {
			$item = $quot.' ';
			if(abs($quot) > 1) {
				$item .= $def['plural'];
			} else {
				$item .= $def['singular'];
			}

			$tokens[] = $item;
			$seconds -= $quot * $def['value'];
		}
	}

	$last = array_pop($tokens);
	if(empty($tokens)) {
		return $last;
	}

	return implode(', ', $tokens).' '.t('and').' '.$last;
}

/**
 * Converts a timestamp into an easily understandable
 * format, e.g. "2 hours", "1 day", "3 months"
 *
 * If you set the date to parameter, the difference
 * will be calculated between the two dates and not
 * the current time.
 *
 * @param DateTime|int $datefrom
 * @param DateTime|int $dateto
 * @return string
 */
function convert_duration2string( $datefrom, $dateto = -1 )
{
    return ConvertHelper::duration2string($datefrom, $dateto);
}

/**
 * Creates XML markup to describe an application error
 * when using XML services.
 *
 * @param mixed $code
 * @param string $message
 * @param string $title
 * @return string
 */
function xml_buildError($code, $message, $title)
{
	$xml = new DOMDocument('1.0', 'UTF-8');
	$xml->formatOutput = true;

	$helper = new AppUtils\XMLHelper($xml);

	$root = $helper->createRoot('error');
	$helper->addTextTag($root, 'id', $code);
	$helper->addTextTag($root, 'message', $message);
	$helper->addTextTag($root, 'title', $title);

	return $xml->saveXML();
}

function xml_buildSuccess($message)
{
	$xml = new DOMDocument('1.0', 'UTF-8');
	$xml->formatOutput = true;

	$helper = new AppUtils\XMLHelper($xml);

	$root = $helper->createRoot('success');
	$helper->addTextTag($root, 'message', $message);
	$helper->addTextTag($root, 'time', date('Y-m-d H:i:s'));

	return $xml->saveXML();
}

/**
 * Removes any tags from the string, trims it and converts
 * special characters to HTML entities.
 *
 * @param string $string
 * @return string
 */
function convert_cleanRequestString($string)
{
	return htmlspecialchars(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sends the specified XML string to the browser with
 * the correct headers and terminates the request.
 *
 * @param string $xml
 */
function displayXML(string $xml) : void
{
    XMLHelper::displayXML($xml);

    Application::exit();
}

function displayExceptionXML(Exception $e, $code, string $title, bool $debug=false)
{
    $message = rtrim($e->getMessage(), '.').'.';
    
    $customInfo = array(
        'is_exception' => 'true',
        'exception_code' => $e->getCode(),
        'exception_file' => $e->getFile(),
        'exception_line' => $e->getLine()
    );
    
    if($e instanceof Application_Exception) {
        $customInfo['exception_info'] = $e->getDeveloperInfo();
        $customInfo['exception_id'] = $e->getID();
    }
    
    $request = Application_Request::getInstance();
    
    if($request->getBool('debug') || $request->getBool('debug_output') || $request->getBool(Application::REQUEST_VAR_SIMULATION)) {
        $customInfo['trace'] = $e->getTraceAsString();
    }
    
    displayErrorXML(
        $code,
        $message,
        $title,
        $customInfo,
        $debug
    );
}

/**
 * Creates the XML structure for an error response, and
 * sends the XML to the browser with a HTTP 400 response
 * header.
 *
 * @param int $errorCode
 * @param string $errorMessage
 * @param string $title
 */
function displayErrorXML($errorCode, $errorMessage, $title, $customInfo=array(), $debug=false)
{
	$driver = Application_Driver::getInstance();
	if(method_exists($driver, 'getCampaign')) {
		$campaign = $driver->getCampaign();
		$customInfo['campaign_id'] = $campaign->getID();
		$customInfo['campaign_label'] = $campaign->getLabel();
	}
	
	if(isset($_REQUEST['debug']) && $_REQUEST['debug']=='yes') {
	    Application::log('Send XML error', true);
	    Application::log('Error code: '.$errorCode);
	    Application::log('Error message: '.nl2br($errorMessage));
	    Application::log('Error title: '.$title);
	    exit;
	}
	
	XMLHelper::setSimulation($debug);
	
	XMLHelper::displayErrorXML(
	    (string)$errorCode, 
	    $errorMessage, 
	    $title, 
	    $customInfo
    );

    Application::exit();
}

/**
 * Sends the specified CSV string to the browser with
 * the correct headers and terminates the request.
 *
 * @param string $csv
 * @param string $filename 
 * @param boolean $debug In debug mode, HTML is added to display the CSV better.
 */
function displayCSV($csv, $filename='download.csv', $debug=false)
{
    if($debug) {
        echo
        '<hr>'.
        '<code>'.t('CSV File:').' '.$filename.'</code>'.
        '<hr>'.
        '<pre>'.$csv.'</pre>'.
        '<hr>';
        exit;
    } 
    
	header('Content-Type:text/plain; charset=utf-8');
    echo $csv;
	exit;
}

/**
 * Sends the specified CSV string to the browser with
 * the correct headers to trigger a download of the CSV
 * to a local file and terminates the request.
 *
 * @param string $csv
 * @param string $filename
 * @return never
 */
function downloadCSV(string $csv, string $filename='download.csv')
{
    header('Content-type:text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	
	$boms = FileHelper::createUnicodeHandling()->getUTFBOMs();
	
	echo 
	$boms['UTF8'].
	$csv;
	exit;
}

/**
 * Sends the specified XML string to the browser with
 * the correct headers to triggr a download of the XML
 * to a local file and terminates the request.
 *
 * @param string $xml
 * @param string $filename
 */
function downloadXML(string $xml, string $filename='download.xml') : void
{
	XMLHelper::downloadXML($xml, $filename);

	Application::exit();
}

/**
 * Checks whether the current operating system is windows.
 * @return boolean
 */
function isOSWindows()
{
    if(substr(PHP_OS, 0, 3) == 'WIN') {
        return true;
    }

    return false;
}

function loadtimeError($message, $code)
{
    header('Content-Type:text/plain; charset=UTF-8');
    die(sprintf(
        'FATAL LOADTIME ERROR [%s]: %s',
        $code,
        $message
    ));
}

/**
 * Calculates a subset sum: finds out which combinations of numbers
 * from the numbers array can be added together to come to the target
 * number.
 *
 * Returns an indexed array with arrays of number combinations.
 *
 * Example:
 *
 * <pre>
 * subset_sum(array(5,10,7,3,20), 25);
 * </pre>
 *
 * Returns:
 *
 * <pre>
 * Array
 *(
 *   [0] => Array
 *   (
 *       [0] => 3
 *       [1] => 5
 *       [2] => 7
 *       [3] => 10
 *   )
 *   [1] => Array
 *   (
 *       [0] => 5
 *       [1] => 20
 *   )
 *)
 *</pre>
 *
 * @param array<int,int|float> $numbers
 * @param float $target
 * @return array<int,array<int,int|float>>
 */
function subset_sum(array $numbers, float $target) : array
{
    return Mistralys\SubsetSum\SubsetSum::create($target, $numbers)->getMatches();
}

/**
 * Retrieves the content of a var_dump call instead of sending it to the browser.
 * 
 * @param mixed $var
 * @return string
 */
function var_dump_get($var)
{
    ob_start();
    var_dump($var);
    return ob_get_clean();
}

/**
 * Translates a string to the selected application locale.
 * @return string
 */
function t() : string
{
    return call_user_func_array('\AppLocalize\t', func_get_args());
}

/**
 * Translates a string to the selected application locale, 
 * and echos it to standard output.
 */
function pt() : void
{
    call_user_func_array('\AppLocalize\pt', func_get_args());
}

/**
 * Translates a string to the selected application locale,
 * and echos it to standard output, with a space at the end.
 */
function pts() : void
{
    call_user_func_array('\AppLocalize\pts', func_get_args());
}

/**
 * Creates a string builder instance.
 * @return UI_StringBuilder
 */
function sb() : UI_StringBuilder
{
    return UI::string();
}

/**
 * Ensures that the subject is scalar or a renderable,
 * and converts it to a string.
 *
 * @param mixed|StringableInterface $subject
 * @return string
 * @throws UI_Exception
 *
 * @see UI::ERROR_NOT_A_RENDERABLE
 */
function toString($subject) : string
{
    // avoid the additional function call
    if(is_string($subject))
    {
        return $subject;
    }

    if($subject === null)
    {
        return '';
    }

    return (string)UI::requireRenderable($subject);
}

/**
 * Can be used to ensure that the specified object
 * is an instance of the target class. It the type
 * matches, the object is returned, and an exception
 * is thrown otherwise.
 *
 * NOTE: This is PHPStan friendly.
 *
 * Usage:
 *
 * ```
 * function createClass() : ClassName
 * {
 *     $object = $this->getObject();
 *
 *     return ensureType(ClassName::class, $object);
 * }
 * ```
 *
 * @param string $className
 * @param object $object
 * @param int $code Optional code to override the built-in error code.
 * @return object
 *
 *
 * @throws ClassNotExistsException
 * @throws ClassNotImplementsException
 *
 * @deprecated Use {@see ClassHelper::requireObjectInstanceOf()} instead.
 */
function ensureType(string $className, object $object, int $code=0)
{
    return ClassHelper::requireObjectInstanceOf($className, $object, $code);
}

/**
 * Whether developer mode is enabled.
 *
 * NOTE: It is automatically enabled when unit tests are
 * running, or if the application runs in a development
 * environment.
 *
 * @return bool
 */
function isDevelMode() : bool
{
    if(boot_constant(BaseConfigRegistry::TESTS_RUNNING) === true) {
        return true;
    }

    if(boot_constant(BaseConfigRegistry::DEVELOPER_MODE) === true) {
        return true;
    }

    if(Application::isUserReady()) {
        return Application::getUser()->isDeveloperModeEnabled();
    }

    return false;
}

/**
 * 
 * @param object|string $subject
 * @return string
 */
function getClassTypeName($subject) : string
{
    $className = '';
    
    if(is_object($subject))
    {
        $className = get_class($subject);
    }
    else
    {
        $className = $subject;
    }

    $tokens = explode('\\', $className);
    $workName = array_pop($tokens);
    $tokens = explode('_', $workName);
    
    return array_pop($tokens);
}

/**
 * Retrieves the last value in the specified array.
 * 
 * @param array $array
 * @return mixed|NULL The value, or NULL if the array is empty.
 */
function array_value_get_last(array &$array)
{
    if(empty($array))
    {
        return null;
    }
    
    $val = end($array);
    
    reset($array);
    
    return $val;
}

/**
 * Sends HTML content and exits.
 *  
 * @param string $html
 */
function displayHTML(string $html) : void
{
    if(!headers_sent())
    {
        header('Content-Type:text/html; Charset=UTF-8');
    }
    
    echo $html;
    
    Application::exit();
}

/**
 * Sends TXT content and exits.
 * 
 * @param string $text
 */
function displayTXT(string $text) : void
{
    if(!headers_sent())
    {
        header('Content-Type:text/plain; Charset=UTF-8');
    }
    
    echo $text;
    
    Application::exit();
}


/**
 * Sends javascript content and exits.
 *
 * @param string $js
 */
function displayJS(string $js) : void
{
    if(!headers_sent())
    {
        header('Content-Type:application/javascript; charset=UTF-8');
    }
    
    echo $js;
    
    Application::exit();
}

/**
 * Creates a new instance of the DBHelper's statement
 * builder, used for generating SQL statements in an
 * object-oriented way.
 *
 * @param string $statementTemplate
 * @param DBHelper_StatementBuilder_ValuesContainer|null $valuesContainer
 * @return DBHelper_StatementBuilder
 */
function statementBuilder(string $statementTemplate, ?DBHelper_StatementBuilder_ValuesContainer $valuesContainer=null) : DBHelper_StatementBuilder
{
    $builder = new DBHelper_StatementBuilder($statementTemplate);

    if($valuesContainer !== null)
    {
        $builder->setContainer($valuesContainer);
    }

    return $builder;
}

function statementValues(?DBHelper_StatementBuilder_ValuesContainer $container=null) : DBHelper_StatementBuilder_ValuesContainer
{
    if($container !== null) {
        return $container;
    }

    return new DBHelper_StatementBuilder_ValuesContainer();
}

/**
 * Fetches the home folder of the current user.
 *
 * NOTE: Meant to be used when working locally
 * on a project. Supports Windows, Linux and
 * MacOS.
 *
 * @return string
 */
function getHomeFolder() : string
{
    $result = $_SERVER['HOME'] ?? getenv('HOME');

    if(empty($result) && function_exists('exec')) {
        if(PHP_OS_FAMILY === 'Windows') {
            $result = exec("echo %userprofile%");
        } else {
            $result = exec("echo ~");
        }
    }

    return $result;
}
