<?php
if (!isset($_SESSION)) session_start(); 
require_once 'System/Core/Loader.php'; 
require dirname(__DIR__).'/vendor/autoload.php'; 
use System\Base\SistemaBase; 

$sistema = new SistemaBase(); 

if(!isset($_SESSION['admin_id']) && isset($_COOKIE['session_token'])) 
	$_SESSION = json_decode($sistema->desembaralhar($_COOKIE['session_token']),true); 

require_once $sistema->getRoute();