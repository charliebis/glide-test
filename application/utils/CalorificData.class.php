<?php
/**
 * Calorific data functions related to fetching remote CSV data and saving to local DB
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


class CalorificData
{

    private $csvBaseUrl = 'https://mip-prd-web.azurewebsites.net/DataItemViewer/DownloadFile';
    private $csvPostData;
    private $dbConn;

    public $csvCachedFilePath = '/tmp/calorific_data.csv';


    public function __construct($dbConn)
    {
        $this->dbConn      = $dbConn;
        $this->csvPostData = [
            'LatestValue'                 => 'true',
            'PublicationObjectIds'        => '408:28, 408:5328, 408:5320, 408:5291, 408:5366, 408:5312, 408:5346, 408:5324, 408:5316, 408:5308, 408:5336, 408:5333, 408:5342, 408:5354, 408:82, 408:70, 408:59, 408:38, 408:49',
            'PublicationObjectStagingIds' => 'PUBOBJ1660,PUBOB4507,PUBOB4508,PUBOB4510,PUBOB4509,PUBOB4511,PUBOB4512,PUBOB4513,PUBOB4514,PUBOB4515,PUBOB4516,PUBOB4517,PUBOB4518,PUBOB4519,PUBOB4521,PUBOB4520,PUBOB4522,PUBOBJ1661,PUBOBJ1662',
            'Applicable'                  => 'applicableAt',
            'PublicationObjectCount'      => 19,
            'FromUtcDateTime'             => false,
            'ToUtcDateTime'               => false,
            'FileType'                    => 'Csv'
        ];
    }

    /**
     * Deletes the cached CSV file that was fetched from the remote source
     */
    public function deleteCachedCsvFile()
    {
        if (file_exists($this->csvCachedFilePath))
            unlink($this->csvCachedFilePath);
    }

    /**
     * Empties the calorific values data tables. Used before importing new data
     */
    public function emptyDataTables()
    {
        try
        {
            $q    = 'TRUNCATE areas;';
            $stmt = $this->dbConn->connection->prepare($q);
            $stmt->execute();

            $q    = 'TRUNCATE calorific_value_data;';
            $stmt = $this->dbConn->connection->prepare($q);
            $stmt->execute();

            return true;

        } catch (PDOException $e)
        {
            return ['error' => 'MySQL Error in ' . __FUNCTION__ . '(): ' . $e->getMessage()];
        }
    }

    /**
     * Saves a row to the areas table. One for each area found in the CSV data
     */
    public function saveAreaRow($areaName)
    {
        try
        {
            $q = 'INSERT IGNORE INTO areas
                    (area)
                    VALUES (:area)
                    ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);';

            $stmt = $this->dbConn->connection->prepare($q);

            $stmt->execute([
                ':area' => $areaName
            ]);

            return $this->dbConn->connection->lastInsertId();

        } catch (PDOException $e)
        {
            return ['error' => 'MySQL Error in ' . __FUNCTION__ . '(): ' . $e->getMessage()];
        }
    }

    /**
     * Saves a row to the calorific_value_data table. One for each data item found in the CSV data
     */
    public function saveCalorificValue($data)
    {
        try
        {
            $q = 'INSERT INTO calorific_value_data
                    (area_id, applicable_at, applicable_for, value, generated_time, quality_indicator)
                    VALUES (:area_id, :applicable_at, :applicable_for, :value, :generated_time, :quality_indicator)
                    ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);';

            $stmt = $this->dbConn->connection->prepare($q);

            $stmt->execute([
                ':area_id'           => $data['Area ID'],
                ':applicable_at'     => $data['Applicable At'],
                ':applicable_for'    => $data['Applicable For'],
                ':value'             => $data['Value'],
                ':generated_time'    => $data['Generated Time'],
                ':quality_indicator' => $data['Quality Indicator']
            ]);

            return $this->dbConn->connection->lastInsertId();
        } catch (PDOException $e)
        {
            return ['error' => 'MySQL Error in ' . __FUNCTION__ . '(): ' . $e->getMessage()];
        }
    }

    /**
     * Saves a row to the process_log table. One for execution of the import script. Logs the results of the script run
     */
    public function saveLogRecord($totals, $processStart, $processEnd)
    {
        try
        {
            $q = 'INSERT INTO process_log
                    (process_start, process_end, total_records, total_saved, total_failed_area_parse, total_failed_area_save, total_failed_calorie_data_save)
                    VALUES (:process_start, :process_end, :total_records, :total_saved, :total_failed_area_parse, :total_failed_area_save, :total_failed_calorie_data_save);';

            $stmt = $this->dbConn->connection->prepare($q);

            $stmt->execute([
                ':process_start'                  => date('Y-m-d H:i:s', $processStart),
                ':process_end'                    => date('Y-m-d H:i:s', $processEnd),
                ':total_records'                  => $totals['total_records'],
                ':total_saved'                    => $totals['total_saved'],
                ':total_failed_area_parse'        => $totals['total_failed_area_parse'],
                ':total_failed_area_save'         => $totals['total_failed_area_save'],
                ':total_failed_calorie_data_save' => $totals['total_failed_calorie_data_save']
            ]);

            return $this->dbConn->connection->lastInsertId();
        } catch (PDOException $e)
        {
            return ['error' => 'MySQL Error in ' . __FUNCTION__ . '(): ' . $e->getMessage()];
        }
    }

    /**
     * Saves a row to the process_log table. One for execution of the import script. Logs the results of the script run
     */
    public function fetchRemoteCsv($dateStart, $dateEnd)
    {
        //  Set start and end dates in post data
        $this->csvPostData['FromUtcDateTime'] = $dateStart;
        $this->csvPostData['ToUtcDateTime']   = $dateEnd;

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_URL, $this->csvBaseUrl);
        if (!empty($this->csvPostData))
            curl_setopt($handle, CURLOPT_POSTFIELDS, $this->csvPostData);
        $rs = curl_exec($handle);
        //die($rs);
        //  Check there is a response
        if (empty($rs))
            return ['error' => 'Response from remote CSV source was empty'];
        //  Cache the CSV data in local file
        $csvCached = file_put_contents($this->csvCachedFilePath, $rs);
        if (!$csvCached)
            return ['error' => 'calorific_data.csv was not saved to file: ' . $this->csvCachedFilePath];

        return true;
    }
}