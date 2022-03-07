<?php


/**
 * CalorificDataModel - model for providing calorie related data
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */
class CalorificDataModel extends Model
{
    protected $dbConn;


    public function __construct($application)
    {
        parent::__construct($application);
        $this->dbConn = $this->application->dbConn;
    }


    /**
     * Gets areas data from the database
     */
    public function getAreasData()
    {
        $q      = 'SELECT
                        a.id, a.area, COUNT(cvd.id) AS total_calorific_data_items, AVG(cvd.value) AS average_value
                    FROM
                        calorific_value_data cvd
                            LEFT JOIN areas a ON cvd.area_id = a.id
                    GROUP BY a.area    
                    ORDER BY area ASC;';
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


    /**
     * Gets all calorific values data from the database
     */
    public function getCalorificValuesData()
    {
        $q      = 'SELECT
                        cvd.*, a.area
                    FROM
                        calorific_value_data cvd
                            LEFT JOIN areas a ON cvd.area_id = a.id
                    ORDER BY cvd.applicable_for DESC;';
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


    /**
     * Gets calorific values data from the database, for a specific area
     */
    public function getCalorificValuesDataByAreaId($areaId)
    {
        $q      = 'SELECT
                        cvd.*, a.area
                    FROM
                        calorific_value_data cvd
                            LEFT JOIN areas a ON cvd.area_id = a.id
                    WHERE a.id = :id
                    ORDER BY cvd.applicable_for DESC;';
        $params = [':id' => $areaId];

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


    /**
     * Gets the last data update datetime from the DB by using the latest record and taking the process_end datatime field
     */
    public function getLastUpdatedDateTime()
    {
        $q      = 'SELECT
                        pl.process_end
                    FROM
                        process_log pl
                    ORDER BY process_end DESC
                    LIMIT 1;';
        $params = [];

        try
        {
            $stmt = $this->dbConn->connection->prepare($q);
            $stmt->execute($params);
            $dateTime = $stmt->fetch();

            return $dateTime['process_end'];
        } catch (PDOException $e)
        {
            $this->throwError('MySQL Error > ' . __FUNCTION__ . '(): ' . $e->getMessage() . '.<br /><br />Query:<br />' . $q . '<br /><br />Params: ' . '<pre>' . var_dump($params) . '</pre>');
        }

        return false;
    }
}