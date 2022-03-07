<?php
/**
 * Class for this specific application. Extends the generic Application class
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


class CalorieDataViewerApp extends Application
{

    public function __construct()
    {
        $this->configSettingsRequired[] = 'site_url';
        parent::__construct();
    }
}