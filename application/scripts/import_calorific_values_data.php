<?php
/**
 * import_calorific_values_data.php
 *
 * Command line script to fetch the CSV data from the remote source and import it into the
 * database.
 *
 * It can be run manually in a terminal or invoked from the front end via a button click.
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */

//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//  ********************
$timeStart    = time();
$pollLockFile = '/tmp/import_lock.txt';
register_shutdown_function('__shutdown_func', $pollLockFile); //  Delete the lock file when this script ends
file_put_contents($pollLockFile, 'Lock');
//  ********************
require_once(dirname(__FILE__) . '/../engine/Application.class.php');
require_once(dirname(__FILE__) . '/../CalorieDataViewerApp.class.php');
//  ********************
$application                    = new CalorieDataViewerApp();
$application->displayFullErrors = true;
//  Run the app for scripts
$application->loadConfig()->init();
//  ********************
//  Set start date to the beginning of the current year
$dataStartDateTime = date('Y-m-d', strtotime('1st January')) . 'T00:00:00.000Z';
//  Set end date to now
$dataEndDateTime = date('Y-m-d\TH:i:s') . '.000Z';
//  Update the local CSV file from remote data
$cdObj = new CalorificData($application->dbConn);
echo 'Updating CSV data from remote source... ';
$csvCached = $cdObj->updateCsvFromRemote($dataStartDateTime, $dataEndDateTime);
//  Check the result of the data update
if (!empty($csvCached['error']))
    die('Error updating from remote source: ' . $csvCached['error']);
echo 'Done' . PHP_EOL;
//  ********************
//  Empty the data table
echo 'Emptying DB tables...';
$tableEmptied = $cdObj->emptyDataTables();
if (!empty($tableEmptied['error']))
    die('Error emptying data table: ' . $tableEmptied['error']);
echo 'Done' . PHP_EOL;
//  ********************
//  Updated CSV file has been saved. Tables have been emptied. Now loop its contents and save to the database
echo 'Processing CSV file. Saving data to DB...';
$fhInput = fopen($cdObj->csvCachedFilePath, 'r');
//  ********************
//  Init counters and totals array
$lines  = 0;
$count  = 0;
$totals = [
    'total_records'                  => 0,
    'total_saved'                    => 0,
    'total_failed_area_parse'        => 0,
    'total_failed_area_save'         => 0,
    'total_failed_calorie_data_save' => 0
];

while (($inputLine = fgetcsv($fhInput)) !== false)
{
    //  ********************
    //  First line of the input CSV is the headers
    if ($lines == 0)
    {
        $inputHeaders = $inputLine;
        //print_r($inputHeaders);
        //exit;
        $lines++;
        continue;
    }
    //  ********************
    //  Process this data record
    $totals['total_records']++;
    //echo 'Processing: ' . number_format($count) . PHP_EOL;
    $inputRecord = array_combine($inputHeaders, $inputLine);
    $inputRecord = array_map('trim', $inputRecord);
    $count++;
    $lines++;
    //  ********************
    //  Get the area name from the Data Item field. It may or may not be within LDZ()
    $inputRecord['Area Name'] = false;
    if (preg_match('/^Calorific Value, (?:LDZ\()?(.+?)(?:\))?$/im', $inputRecord['Data Item'], $dataItemMatches))
        $inputRecord['Area Name'] = $dataItemMatches[1];
    //  If we can't parse the area name it can't be saved
    if (empty($inputRecord['Area Name']))
    {
        $totals['total_failed_area_parse']++;
        continue;
    }
    //  ********************
    $inputRecord['Area ID'] = $cdObj->saveAreaRow($inputRecord['Area Name']);
    if (empty($inputRecord['Area ID']))
    {
        $totals['total_failed_area_save']++;
        continue;
    }
    //  ********************
    //  Rebuild the Applicable At date field from "dd/mm/yyyy hh:mm:ss" to "yyyy-mm-dd hh:mm:ss" for DB field
    $inputRecord['Applicable At']    = explode(' ', $inputRecord['Applicable At']);
    $inputRecord['Applicable At'][0] = explode('/', $inputRecord['Applicable At'][0]);
    $inputRecord['Applicable At']    = $inputRecord['Applicable At'][0][2] . '-' . $inputRecord['Applicable At'][0][1] . '-' . $inputRecord['Applicable At'][0][0] . ' ' . $inputRecord['Applicable At'][1];
    //  ********************
    //  Rebuild the Applicable For date field from "dd/mm/yyyy" to "yyyy-mm-dd 00:00:00" for DB field
    $inputRecord['Applicable For'] = explode('/', $inputRecord['Applicable For']);
    $inputRecord['Applicable For'] = $inputRecord['Applicable For'][2] . '-' . $inputRecord['Applicable For'][1] . '-' . $inputRecord['Applicable For'][0] . ' 00:00:00';
    //  ********************
    //  Rebuild the Generated Time date field from "dd/mm/yyyy" to "yyyy-mm-dd 00:00:00" for DB field
    $inputRecord['Generated Time']    = explode(' ', $inputRecord['Generated Time']);
    $inputRecord['Generated Time'][0] = explode('/', $inputRecord['Generated Time'][0]);
    $inputRecord['Generated Time']    = $inputRecord['Generated Time'][0][2] . '-' . $inputRecord['Generated Time'][0][1] . '-' . $inputRecord['Generated Time'][0][0] . ' ' . $inputRecord['Generated Time'][1];
    //  ********************
    //  Quality Indicator - this should be 1 char long but make sure
    if (empty($inputRecord['Quality Indicator']))
        $inputRecord['Quality Indicator'] = '0';    //  No quality indicator set as 0 for DB
    $inputRecord['Quality Indicator'] = substr($inputRecord['Quality Indicator'], 0, 1);
    //  ********************
    $inputRecord['Calorific Value ID'] = $cdObj->saveCalorificValue($inputRecord);
    if (empty($inputRecord['Calorific Value ID']))
    {
        $totals['total_failed_calorie_data_save']++;
        continue;
    }

    $totals['total_saved']++;
}
echo 'Done' . PHP_EOL;
echo 'Closing CSV input file: ' . $cdObj->csvCachedFilePath . PHP_EOL;
fclose($fhInput);
//  ********************
//  Delete the cached CSV file as it's no longer needed
echo 'Deleting cached CSV file... ';
$cdObj->deleteCachedCsvFile();
echo 'Done' . PHP_EOL;
echo PHP_EOL;
echo 'Processing complete. Results:' . PHP_EOL;
echo 'Total records in CSV: ' . number_format($totals['total_records']) . PHP_EOL;
echo 'Total CSV records saved to DB: ' . number_format($totals['total_saved']) . PHP_EOL;
echo 'Total records where area could not be parsed: ' . number_format($totals['total_failed_area_parse']) . PHP_EOL;
echo 'Total records where area failed to save to DB: ' . number_format($totals['total_failed_area_save']) . PHP_EOL;
echo 'Total records where calorie data failed to save to DB: ' . number_format($totals['total_failed_calorie_data_save']) . PHP_EOL;
echo PHP_EOL;
echo 'Saving record to log... ';
$logRecordSaved = $cdObj->saveLogRecord($totals, $timeStart, time());
if (!empty($logRecordSaved['error']))
    die('Error saving record to log: ' . $logRecordSaved['error']);
echo 'Done' . PHP_EOL;

//  Called at end of script execution to delete the lock file
function __shutdown_func($lockFile)
{
    if (file_exists($lockFile))
        unlink($lockFile);
}