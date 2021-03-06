<?php
/**
 * Main application engine. Extended by the specific application's class. That application's class is then
 * instantiated in the site's index page to start the app
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


abstract class Application
{
    public $dbConn = false;
    public $configIni = 'application.ini';
    public $displayFullErrors = false;

    protected $route = false;
    protected $configLoaded = false;
    protected $configSettings = [];
    protected $configSettingsRequired = [
        'installation_path',
        'db_host',
        'db_database',
        'db_user',
        'db_pass',
    ];
    protected $controllerName = false;
    protected $actionName = false;
    protected $controller = false;


    public function __construct()
    {
        //  Autoload classes in the utils dir
        spl_autoload_register([
            $this,
            'autoLoaderUtils'
        ]);
    }


    /**
     * Autoloads the controller classes
     */
    public function autoLoaderControllers($class)
    {
        $classFile = dirname(__FILE__) . '/../mvc/controllers/' . $class . '.class.php';
        if (is_file($classFile) && !class_exists($class))
            require_once($classFile);
    }


    /**
     * Autoloads the model classes
     */
    public function autoLoaderModels($class)
    {
        $classFile = dirname(__FILE__) . '/../mvc/models/' . $class . '.class.php';
        if (is_file($classFile) && !class_exists($class))
            require_once($classFile);
    }


    /**
     * Autoloads the utils classes
     */
    public function autoLoaderUtils($class)
    {
        $classFile = dirname(__FILE__) . '/../utils/' . $class . '.class.php';
        if (is_file($classFile) && !class_exists($class))
            require_once($classFile);
    }


    /**
     * Loads the config INI file and adds the information to the configSettings array. Config fields are used
     * anywhere in the app
     */
    public function loadConfig()
    {
        //  Config settings
        $configIni = $this->getIniFilePath(dirname(__FILE__), $this->configIni);
        if ($configIni === false)
            die('INI file: ' . $this->configIni . ' not found. It must be present in this directory or in a parent directory');

        $iniSettings = parse_ini_file($configIni, true);
        if (empty($iniSettings))
            die('INI file: ' . $this->configIni . ' contains no settings');

        foreach ($iniSettings as $iniSettingsSectionName => $iniSettingsSection)
        {
            if (is_array($iniSettingsSection))   //  Is an array (section in this case) and isn't empty
            {
                foreach ($iniSettingsSection as $iniSettingName => $iniSetting)
                    $this->config($iniSettingName, $iniSetting);
            }
            else
            {
                $this->config($iniSettingsSectionName, $iniSettingsSection);
            }
        }

        return $this;
    }


    /**
     * Checks that the config INI file contains all the required settings for this app
     */
    protected function checkConfig()
    {
        $missingSettings = [];
        //  Check all the required config settings have been set
        foreach ($this->configSettingsRequired as $requiredSetting)
        {
            if (!array_key_exists($requiredSetting, $this->configSettings))
                $missingSettings[] = $requiredSetting;
        }

        return $missingSettings;
    }


    /**
     * Gets or sets a config field in the configSettings array
     */
    public function config($setting, $value = null)
    {
        if ($value !== null)
        {
            $this->configSettings[$setting] = $value;

            return true;
        }
        if (!array_key_exists($setting, $this->configSettings))
            die('Config setting: ' . $setting . ' does not exist');

        return $this->configSettings[$setting];
    }


    /**
     * Finds the path of the config INI file
     */
    protected function getIniFilePath($dir, $fileName)
    {
        if (file_exists($dir . '/' . $fileName))
            return $dir . '/' . $fileName;
        $path = dirname($dir);
        if (substr_count($path, '/') > 1)
            return $this->getIniFilePath($path, $fileName);
        else
            return false;
    }


    /**
     * Initialise the app. Not known at this point if using app for web front end of script
     */
    public function init()
    {
        //  Check all required config settings are in place
        $missingSettings = $this->checkConfig();
        if (!empty($missingSettings))
            die('Missing config settings');
        $this->configLoaded = true;
        //  Connect to database
        $this->dbConn = new Dbconn([
            'db_host'     => $this->config('db_host'),
            'db_database' => $this->config('db_database'),
            'db_user'     => $this->config('db_user'),
            'db_pass'     => $this->config('db_pass')
        ]);

        return $this;
    }


    /**
     * Start the process for displaying the web front end
     */
    public function loadPage()
    {
        //  Start session
        session_start();

        if (!$this->controllerName)
            $this->throwError(404, 'Controller not set: ' . $this->controllerName);
        if (!$this->actionName)
            $this->throwError(404, 'Action not set: ' . $this->actionName);

        //  Controller and action method existence has already been checked.
        //  Create controller object from variable class name
        $this->controller                    = new $this->controllerName($this);
        $this->controller->displayFullErrors = $this->displayFullErrors;
        //  Set the views directory path in the controller
        $this->controller->viewsDir = $this->config('installation_path') . '/application/mvc/views/';
        //  Call action method from variable method name
        $actionMethod = $this->actionName;
        $this->controller->$actionMethod();
    }


    /**
     * Checks and handles the page URL. Checks the controller and action exist and sets these for the app
     */
    public function router()
    {
        //  Autoload MVC related classes at this point as controller and action are about to be checked
        //  Controllers
        spl_autoload_register([
            $this,
            'autoLoaderControllers'
        ]);
        //  Models
        spl_autoload_register([
            $this,
            'autoLoaderModels'
        ]);

        //die($_GET['route']);
        $_GET['route'] = !empty($_GET['route']) ? $_GET['route'] : '/home/';
        //  Check the requested route is valid
        if (!Validator::routeFormat($_GET['route']))
            $this->throwError(404, 'Route format invalid');

        //  Replace any multiple slashes with a single
        $_GET['route'] = preg_replace('%/+%i', '/', $_GET['route']);
        $_GET['route'] = trim($_GET['route'], '/');
        $_GET['route'] = explode('/', $_GET['route']);

        //  Set the requested route. If there is no action in the route, set to index
        if (count($_GET['route']) == 1)
            $_GET['route'][1] = 'index';

        $this->route = $_GET['route'];

        //  Check route length
        if (count($this->route) < 2)
            $this->throwError(404, 'Route does not contain the required parts');

        //  Get the name of the controller and action method to use
        $actionRoute = $this->route;
        $controller  = array_shift($actionRoute);
        $controller  = preg_replace('/[-_]/', ' ', $controller);
        $controller  = ucwords($controller);
        $controller  = preg_replace('/\s/', '', $controller);

        //  First part of the route represents the controller
        //  Check the controller class exists
        if (!class_exists($controller . 'Controller'))
            $this->throwError(404, 'Controller "' . $controller . 'Controller' . '" does not exist');

        //  Rest of the parts of the route represent the action method within the controller
        $action = implode('', $actionRoute);    //  Join the action parts together e.g route of /register/save/ would become registersaveAction
        //  Check the action method exists in the controller
        if (!method_exists($controller . 'Controller', $action . 'Action'))
            $this->throwError(404, 'Action "' . $action . 'Action' . '" does not exist');

        //  Assign the controller and action for the application. These will be used by renderPage()
        $this->controllerName = $controller . 'Controller';
        $this->actionName     = $action . 'Action';

        return $this;
    }


    /**
     * Handles errors that occur in this app
     */
    public function throwError($code = 404, $debugMessage = false)
    {
        $controller                    = new Controller($this);
        $controller->displayFullErrors = $this->displayFullErrors;
        $controller->throwHttpError($code, $debugMessage);
    }
}