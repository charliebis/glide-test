<?php


/**
 * AppDataModel - model for providing app related data
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */
class AppDataModel extends Model
{
    protected $dbConn;


    public function __construct($application)
    {
        parent::__construct($application);
        $this->dbConn = $this->application->dbConn;
    }


    /**
     * Gets import log data from the database
     */
    public function getImportLogData()
    {
        $q      = 'SELECT
                        pl.*, TIMEDIFF(pl.process_end, pl.process_start) AS process_duration
                    FROM
                        process_log pl
                    ORDER BY process_start DESC;';
        $params = [];

        try
        {
            $stmt = $this->dbConn->connection->prepare($q);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e)
        {
            $this->throwError('MySQL Error > ' . __FUNCTION__ . '(): ' . $e->getMessage() . '.<br /><br />Query:<br />' . $q . '<br /><br />Params: ' . '<pre>' . var_dump($params) . '</pre>');
        }

        return false;
    }
}