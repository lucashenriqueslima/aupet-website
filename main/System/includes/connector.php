<?php
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once dirname(__DIR__).'/Core/General.php';
if (!isset($sistema)) {
    $sistema = new General();
}

if(!isset($_SESSION)) session_start();

error_reporting(0); // Set E_ALL for debuging

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinderConnector.class.php';
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinder.class.php';
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinderVolumeLocalFileSystem.class.php';

function access($attr, $path, $data, $volume) {
    return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
            : null;                                    // else elFinder decide it itself
}

// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options

$dir = dirname(dirname(__DIR__)) . '/uploads/' .$sistema->_cliente_.'/';
if(!file_exists($dir)) mkdir($dir, 0777, true);
$opts = array(
    // 'debug' => true,
    'roots' => array(
        array(
            'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
            'path' => $dir, // path to files (REQUIRED)
            'URL' => dirname(dirname(dirname($_SERVER['PHP_SELF']))) . '/uploads/'.$sistema->_cliente_.'/', // URL to files (REQUIRED)
            'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
        )
    )
);


// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

