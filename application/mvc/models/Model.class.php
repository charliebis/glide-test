<?php


/**
 * Model - Base model class, common to all models
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */
class Model
{
    protected $application;


    public function __construct($application)
    {
        $this->application = $application;
    }


    /**
     * Handles errors that occur in this model
     */
    protected function throwError($debugMessage = false)
    {
        //  If the controller has been set, use its throwHttpError() function
        if (is_object($this->application->controller))
            $this->application->controller->throwHttpError(500, $debugMessage);
        else
            die($debugMessage);
    }
}