<?php
/**
 * Application for technical test
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/../application/engine/Application.class.php');
require_once(dirname(__FILE__) . '/../application/GlideApp.class.php');

$GLOBALS['application'] = new GlideApp();
$GLOBALS['application']->displayFullErrors = true;
$GLOBALS['application']->loadConfig()->init()->router()->loadPage();