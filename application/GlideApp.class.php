<?php
/**
 * Class GlideApp
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */

class GlideApp extends Application
{

    public function __construct()
    {
        $this->configSettingsRequired[] = 'site_url';
        parent::__construct();
    }
}