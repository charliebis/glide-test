<?php
/**
 * Class HomeController
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */

class HomeController extends Controller
{
    public $output = '';


    public function indexAction()
    {
        header('Content-Type: text/html; charset=utf-8');   //  Output for this action is HTML
        //  Set page data items
        $this->pageDataItems['page_title'] = 'Glide Tech Test';
        $this->pageDataItems['meta_keywords'] = '';
        $this->pageDataItems['meta_description'] = '';
        //  Load in the view
        require($this->viewsDir . '/home/index.phtml');
    }
}