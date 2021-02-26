<?php
/**
 * Configuration generator for the toolset binary:
 * Can only be called via command line on windows.
 * The toolset application runs this script to 
 * access the application configuration.
 * 
 * Displays a json string with all required config
 * keys.
 * 
 * @package Application
 * @subpackage Toolsets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    // Only allowed via command line
    if(php_sapi_name() != 'cli') {
        exit;
    }
    
    // Only allowed on windows
    if(strtolower(substr(PHP_OS, 0, 3)) !== 'win') {
        exit;
    } 

    // Parameter must exist
	if(!isset($argv[1]) || $argv[1] != "gen_conf") {
		exit;
	}

	$appPath = realpath(dirname(__FILE__).'/../../../../../../');
	if(!$appPath) {
		die('ERROR: App not found');
	}
	
	$htdocsPath = $appPath.'/htdocs';
	
	// -----------------------------------------------------------
	// DETERMINE BRANCH/TRUNK LOCATION
	// -----------------------------------------------------------
	
	$isTrunk = false;
	$isBranch = false;
	$rootPath = null;
	
	$pathParts = explode('\\', $appPath);
	
	$branch = array_pop($pathParts);
	if(strtolower($branch) == 'trunk') {
		$isTrunk = true;
		$rootPath = implode('\\', $pathParts);
	} else {
		$isBranch = true;
		$branch = 'branches/'.$branch;
		array_pop($pathParts);
		$rootPath = implode('\\', $pathParts);
	}
	
	// -----------------------------------------------------------
	// LOAD VERSION STRING
	// -----------------------------------------------------------
	
	$versionFile = $htdocsPath.'/version';
	$version = 'none';
	if(file_exists($versionFile)) {
		$version = trim(file_get_contents($versionFile));
	}
	
	
	// -----------------------------------------------------------
	// DETERMINE REMOTE SVN REPOSITORY URL
	// -----------------------------------------------------------
	
	exec('svn info "'.$rootPath.'" ', $output);
	
	if(empty($output)) {
		die('ERROR: Could not get SVN info');
	}
	
	$svnURL = null;
	foreach($output as $entry) {
		$parts = explode(':', $entry);
		$key = array_shift($parts);
		$key = strtolower(trim($key));
		if($key == 'url') {
			$svnURL = trim(implode(':', $parts));
		}
	}
	
	if(empty($svnURL)) {
		die('ERROR: Could not determine SVN URL');
	}

	// -----------------------------------------------------------
	// LOAD COMPOSER DATA
	// -----------------------------------------------------------
	
	$composerFile = $htdocsPath.'/composer.json';
	if(!file_exists($composerFile)) {
	    die('ERROR: Composer file not found');
	}
	
	$composerData = json_decode(file_get_contents($composerFile), true);
	if($composerData === false) {
	    die('ERROR: Cannot load composer json file - malformed json?');
	}
	
	
	// -----------------------------------------------------------
	// OUTPUT THE CONFIGURATION
	// -----------------------------------------------------------
	
	require_once $htdocsPath.'/bootstrap.php';
	
	$data = array(
	    'DBHost' => APP_DB_HOST,
		'DBName' => APP_DB_NAME,
		'DBUser' => APP_DB_USER,
		'DBPass' => APP_DB_PASSWORD,
		'FrameworkPath' => realpath(dirname(__FILE__).'/../'),
	    'ComposerName' => $composerData['name'],
	    'AppClassName' => APP_CLASS_NAME,
		'AppPath' => $htdocsPath,
		'AppVersion' => $version,
		'SVNURL' => $svnURL,
		'SVNBranch' => $branch,
		'SVNIsBranch' => $isBranch,
		'SVNIsTrunk' => $isTrunk
	);
	
	echo json_encode($data);
	exit;
	
	