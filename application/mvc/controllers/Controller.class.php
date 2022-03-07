<?php
/**
 * Controller - Base controller class, common to all controllers
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


class Controller
{

    protected $application;

    public $output = '';
    public $pageData = [];
    public $viewsDir = false;
    public $displayFullErrors = false;


    public function __construct($application)
    {
        $this->application = $application;
        //  Start capturing output. Outputted in __destruct()
        ob_start();
    }


    public function __destruct()
    {
        //  Final output of page content
        $this->output = ob_get_clean();
        echo $this->output;
    }


    /**
     * Renders a partial template
     */
    public function partial($partial)
    {
        if (!file_exists($this->viewsDir . '/' . $partial))
            $this->throwHttpError(500, 'Error loading partials file: ' . $partial . ' in dir: ' . $this->viewsDir);

        ob_start();
        require($this->viewsDir . '/' . $partial);

        return ob_get_clean();
    }


    /**
     * Handles HTTP errors that occur in this app
     */
    public function throwHttpError($code = 404, $debugMessage = false)
    {
        if (ob_get_level())
            ob_end_clean(); //  Kill any buffered content built up so far
        $error = new HttpError($code);
        //  For page data items set in the page code
        header('HTTP/1.1 ' . $code . ' ' . $error->errorMessage);
        $output = '<h1>Error ' . $error->errorCode . '</h1>';
        $output .= '<p>' . $error->errorMessage . '</p>';
        //  Display the debug message if $this->displayFullErrors is true
        if ($debugMessage && $this->displayFullErrors)
            $output .= '<p>' . $debugMessage . '</p>';
        die($output);
    }
}