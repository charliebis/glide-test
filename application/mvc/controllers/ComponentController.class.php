<?php
/**
 * ComponentController - controller for AJAX calls requesting web page components
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


class ComponentController extends Controller
{

    /**
     * Loads areas HTML table
     */
    public function areasTableAction()
    {
        header('Content-Type: text/html; charset=utf-8');   //  Output for this action is HTML
        //  Set page data
        //  Get areas data
        $model                                        = new CalorificDataModel($this->application);
        $this->pageData['table_data']                 = $model->getAreasData();
        $this->pageData['total_data_items']           = count($this->pageData['table_data']);
        $this->pageData['total_data_items_formatted'] = number_format(count($this->pageData['table_data']));

        //  Prepare values for display
        foreach ($this->pageData['table_data'] as &$item)
        {
            //  applicable_for is stored as datetime but only date needs to be shown for this field
            $item['total_calorific_data_items'] = number_format($item['total_calorific_data_items']);
            $item['average_value']              = number_format($item['average_value'], 4);
        }

        //  Load in the view
        require($this->viewsDir . '/components/areastable.phtml');
    }


    /**
     * Loads calorific data HTML table
     */
    public function calorificDataTableAction()
    {
        header('Content-Type: text/html; charset=utf-8');   //  Output for this action is HTML
        //  Set page data
        //  Get calorie data
        $model                                        = new CalorificDataModel($this->application);
        $this->pageData['table_data']                 = $model->getCalorificValuesData();
        $this->pageData['total_data_items']           = count($this->pageData['table_data']);
        $this->pageData['total_data_items_formatted'] = number_format(count($this->pageData['table_data']));

        //  Prepare values for display
        foreach ($this->pageData['table_data'] as &$item)
        {
            //  applicable_for is stored as datetime but only date needs to be shown for this field
            $item['applicable_for'] = explode(' ', $item['applicable_for']);
            $item['applicable_for'] = $item['applicable_for'][0];
            $item['value']          = number_format($item['value'], 4);

            //  Prepare quality_indicator_full for display, based on the code in quality_indicator
            switch (strtoupper($item['quality_indicator']))
            {
                case 'E':
                    $item['quality_indicator_full'] = 'Expired';
                    break;
                case 'A':
                    $item['quality_indicator_full'] = 'Amended';
                    break;
                case 'C':
                    $item['quality_indicator_full'] = 'Corrected';
                    break;
                case 'S':
                    $item['quality_indicator_full'] = 'Substituted';
                    break;
                case 'L':
                    $item['quality_indicator_full'] = 'Late Received';
                    break;
                case '0':
                    $item['quality_indicator_full'] = 'Not Provided';
                    break;
                default:
                    $item['quality_indicator_full'] = $item['quality_indicator'];
            }
        }

        //  Load in the view
        require($this->viewsDir . '/components/calorificdatatable.phtml');
    }


    /**
     * Loads import log HTML table
     */
    public function importLogTableAction()
    {
        header('Content-Type: text/html; charset=utf-8');   //  Output for this action is HTML
        //  Set page data
        //  Get areas data
        $model                                        = new AppDataModel($this->application);
        $this->pageData['table_data']                 = $model->getImportLogData();
        $this->pageData['total_data_items']           = count($this->pageData['table_data']);
        $this->pageData['total_data_items_formatted'] = number_format(count($this->pageData['table_data']));

        //  Prepare values for display
        foreach ($this->pageData['table_data'] as &$item)
        {
            $item['total_records']                  = number_format($item['total_records']);
            $item['total_saved']                    = number_format($item['total_saved']);
            $item['total_failed_area_parse']        = number_format($item['total_failed_area_parse']);
            $item['total_failed_area_save']         = number_format($item['total_failed_area_save']);
            $item['total_failed_calorie_data_save'] = number_format($item['total_failed_calorie_data_save']);
        }

        //  Load in the view
        require($this->viewsDir . '/components/importlogtable.phtml');
    }


    /**
     * Loads last updated message (HTML)
     */
    public function lastUpdatedMessageAction()
    {
        header('Content-Type: text/html; charset=utf-8');   //  Output for this action is HTML
        //  Set page data
        $model                                   = new CalorificDataModel($this->application);
        $this->pageData['last_updated_datetime'] = $model->getLastUpdatedDateTime();

        if (!empty($this->pageData['last_updated_datetime']))
            $this->pageData['last_updated_datetime'] = date('D jS M Y H:i', strtotime($this->pageData['last_updated_datetime']));
        else
            $this->pageData['last_updated_datetime'] = 'Never';

        //  Load in the view
        require($this->viewsDir . '/components/lastupdatedmessage.phtml');
    }
}