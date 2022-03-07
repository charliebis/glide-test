<?php
/**
 * UpdateController - controller for AJAX calls requesting the running of the data import script
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


class UpdateController extends Controller
{
    private $pollLockFile = '/tmp/import_lock.txt';


    /**
     * Starts the import script via shell_exec() and returns status via JSON
     */
    public function startAction()
    {
        header('Content-Type: application/json; charset=utf-8');   //  Output for this action is JSON
        //  Start the data import script. Don't wait for it to finish. The script itself writes a lock file, the presence of which will be polled for to determine when
        //  the script has ended
        shell_exec('php ' . $this->application->config('installation_path') . '/application/scripts/import_calorific_values_data.php > /dev/null 2>/dev/null &');

        echo json_encode([
            'result'  => 'success',
            'started' => true
        ]);
    }


    /**
     * Polls for the presence of the lock file. Used by JS AJAX to determine the completion status of the data import script
     */
    public function pollCompleteAction()
    {
        header('Content-Type: application/json; charset=utf-8');   //  Output for this action is JSON
        //  If the lock file exists then the import data script is still running
        if (file_exists($this->pollLockFile))
        {
            echo json_encode([
                'result'   => 'success',
                'complete' => false
            ]);
        }
        else
        {
            //  Import data script is not running
            echo json_encode([
                'result'   => 'success',
                'complete' => true
            ]);
        }
    }
}