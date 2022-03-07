<?php
/**
 * Class Dbconn
 */


class Dbconn
{
    /**
     * @var bool
     */
    public $hostname = false;
    /**
     * @var bool
     */
    public $database = false;
    /**
     * @var bool
     */
    public $username = false;
    /**
     * @var bool
     */
    public $password = false;
    /**
     * @var bool
     */
    public $connection = false;


    /**
     * @param mixed $default
     */
    public function __construct($default = false)
    {
        if (is_array($default) && !empty($default['db_host']) && !empty($default['db_database']) && !empty($default['db_user']) && !empty($default['db_pass']))
        {
            $this->hostname = $default['db_host'];
            $this->database = $default['db_database'];
            $this->username = $default['db_user'];
            $this->password = $default['db_pass'];
            $this->connectToDb($this->hostname, $this->database, $this->username, $this->password);
        }
    }


    public function connectToDb($hostname, $database, $username, $password)
    {
        $this->hostname = $hostname;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;

        $this->connect();
    }


    public function connect()
    {
        try
        {
            //echo 'mysql:host=' . $this->hostname . ';dbname=' . $this->database.' '.$this->username.' '.$this->password;
            $this->connection = new PDO('mysql:host=' . $this->hostname . ';dbname=' . $this->database, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            //$this->connection->query("SET group_concat_max_len = 16384");
        } catch (PDOException $e)
        {
            die('Database connection error');
        }
    }
}