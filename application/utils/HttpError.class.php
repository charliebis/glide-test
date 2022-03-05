<?php


/**
 * Class Http
 */
class HttpError
{

    public $errorCode;
    public $errorMessage;


    public function __construct($e = 404)
    {
        switch ($e)
        {
            case 400:
                $this->errorCode = 400;
                $this->errorMessage = 'Bad Request';
                break;
            case 401:
                $this->errorCode = 401;
                $this->errorMessage = 'Unauthorized';
                break;
            case 403:
                $this->errorCode = 403;
                $this->errorMessage = 'Forbidden';
                break;
            case 404:
                $this->errorCode = 404;
                $this->errorMessage = 'Page Not Found';
                break;
            case 500:
                $this->errorCode = 500;
                $this->errorMessage = 'Internal Server Error';
                break;
            default:
                $this->errorCode = 404;
                $this->errorMessage = 'Page Not Found';
        }
    }
}