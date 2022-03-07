<?php
/**
 * HomeController - controller for page requests related to the home page
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


class HomeController extends Controller
{
    /**
     * Loads home page
     */
    public function indexAction()
    {
        header('Content-Type: text/html; charset=utf-8');   //  Output for this action is HTML
        //  Set page data
        $this->pageData['page_title'] = 'Calorie Data Viewer';
        //  Load in the view
        require($this->viewsDir . '/home/index.phtml');
    }
}