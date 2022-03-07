<?php
/**
 * Application for Glide technical test
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/../application/engine/Application.class.php');
require_once(dirname(__FILE__) . '/../application/CalorieDataViewerApp.class.php');

$application                    = new CalorieDataViewerApp();
//$application->displayFullErrors = true;
//  Run the app for web front end
$application->loadConfig()->init()->router()->loadPage();