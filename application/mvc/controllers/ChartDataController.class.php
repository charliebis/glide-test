<?php
/**
 * ChartDataController - controller for AJAX calls requesting data sets for JS charts
 *
 * @author Charles Edwards <charlie@burcottis.co.uk>
 *
 */


class ChartDataController extends Controller
{
    /**
     * Loads chart data for the data items by area chart and returns them as JSON
     */
    public function areaDataItemsChartAction()
    {
        header('Content-Type: application/json; charset=utf-8');   //  Output for this action is JSON
        //  Check area_id was passed in POST and is an integer
        if (empty($_POST['area_id']) || !Validator::integer($_POST['area_id']))
            $this->throwHttpError(500, 'area_id was missing or invalid');

        //  Get calorie data for area
        $model     = new CalorificDataModel($this->application);
        $dataItems = $model->getCalorificValuesDataByAreaId($_POST['area_id']);

        $chartData = [];
        foreach ($dataItems as $dataItem)
        {
            $chartData[$dataItem['applicable_for']] = $dataItem['value'];
        }

        echo json_encode([
            'result'           => 'success',
            'chart_data'       => $chartData,
            'total_data_items' => count($dataItems),
            'total_dates'      => count($chartData)
        ]);

    }
}