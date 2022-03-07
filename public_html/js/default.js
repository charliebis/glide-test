/**
 *  Calorie Data Viewer App main JS file
 *  @author Charles Edwards <charlie@burcottis.co.uk>
 */

/**
 * Selector for the main data table component
 */
const dataTableContainerSelector = 'div#container-datatable';
/**
 * Selector for the last updated message component
 */
const lastUpdatedMessageContainerSelector = 'div#last-updated-header';
/**
 * Max number of times the completion status of the remote source update can be polled for
 * before giving up
 */
const maxPollAttempts = 240;
/**
 * Max number of times the completion status of the remote source update can be polled for
 * before giving up
 */
const pollAttemptTime = 2500;   //  5 seconds between each polling attempt

/**
 * Builds a Chart.js chart, given the canvas element ID and chartData object
 */
function areaDataItemsChart(canvasId, chartData) {

    let datasetsForChart = [];
    let labels = [];
    let plotdata = [];
    for (let key in chartData) {
        labels.push(key);
        plotdata.push(chartData[key]);
    }

    let datasetForChart = {
        label          : 'Applicable For',
        backgroundColor: 'rgba(151,187,205,1)',
        hitRadius      : 5,
        hoverRadius    : 10,
        data           : plotdata
    };

    datasetsForChart.push(datasetForChart);

    let ctx = document.getElementById(canvasId).getContext("2d");
    let areaChart = new Chart(ctx, {
        type   : 'bar',
        data   : {
            labels  : labels,
            datasets: datasetsForChart
        },
        options: {
            responsive         : true,
            maintainAspectRatio: true,
            title              : {
                display: false
            }
        }
    });
}

/**
 * Success callback for chart data request. Builds the chart and displays in a dialog
 */
function areaDataItemsChartDialog(chartDataResponse) {
    //  Delete any box-chart elements
    $('div#box-chart').remove();
    //  If the fetch of the chart data succeeded
    if (chartDataResponse.result === 'success') {
        $('body').append('<div id="box-chart" title="Data Items By Date For Area"></div>');
        $('div#box-chart').html('<canvas class="box-chart-canvas" id="box-chart-canvas"></canvas>');
        //  Build the chart
        areaDataItemsChart('box-chart-canvas', chartDataResponse.chart_data);
        //  Open the chart in a dialog
        $('div#box-chart').dialog({
            modal        : true,
            closeOnEscape: true,
            width        : 800,
            height       : 520,
            buttons      : {
                Close: function () {
                    $(this).dialog("close");
                }
            }
        });
    }
    else {
        console.warn('Could not fetch chart data');
        alert("Error! Chart could not be loaded");
    }
}

/**
 * Bind clicks on the area chart buttons
 */
function dataTableBindChartButtons() {
    $('a.btn-area-chart').off('click').bind('click', function () {
        let areaId = $(this).attr('data-areaid');
        let areaName = $(this).attr('data-area');
        console.info('Loading chart data for ' + areaName + ' (ID: ' + areaId + ')');

        let chartDataRq = $.ajax({
            url     : '/chart-data/areadataitemschart/',
            type    : 'post',
            data    : 'area_id=' + areaId,
            dataType: 'json',
            cache   : false
        });
        chartDataRq.done(areaDataItemsChartDialog);
        chartDataRq.fail(function (jqXHR, textStatus, errorThrown) {
            console.warn('HTTP error occurred. Error: ' + errorThrown);
        });

        return false;
    });
}


/**
 * Load the main data table component
 */
function dataTableLoad() {
    console.info('Loading data table');
    renderLoadingMessage('Loading...');
    //  navGetActiveTab() returns the name of the action required
    let loadRq = loadComponentHtml('/component/' + navGetActiveTab() + '/')
    loadRq.done(renderTable);
    loadRq.fail(renderError);
}


/**
 * Load the last updated message component
 */
function lastUpdatedLoad() {
    console.info('Loading data table');
    lastUpdatedMessageHide();
    let loadRq = loadComponentHtml('/component/lastupdatedmessage/')
    loadRq.done(renderLastUpdatedMessage);
    loadRq.fail(function (jqXHR, textStatus, errorThrown) {
        console.warn('HTTP error occurred. Error: ' + errorThrown);
    });
}


/**
 * Makes the last updated message invisible
 */
function lastUpdatedMessageHide() {
    let container = $(lastUpdatedMessageContainerSelector);
    container.fadeOut();
}


/**
 * Set the content of the last updated message
 */
function lastUpdatedMessageSet(html) {
    let container = $(lastUpdatedMessageContainerSelector);
    container.html(html);
}


/**
 * Makes the last updated message visible
 */
function lastUpdatedMessageShow() {
    let container = $(lastUpdatedMessageContainerSelector);
    container.fadeIn();
}


/**
 * Load the table data from the database
 */
function loadComponentHtml(url) {

    return $.ajax({
        url     : url,
        type    : 'post',
        dataType: 'html',
        cache   : false
    });
}


/**
 * Handles clicks on the nav tabs
 */
function navBindTabClick() {
    $('a.nav-tab').bind('click', function () {
        //  Do nothing if the click is on the currently active tab
        if ($(this).hasClass('nav-tab-active'))
            return false;

        let tabClicked = $(this).attr('data-tab');
        console.info('Nav tab has been clicked: ' + tabClicked);
        //  Remove the nav-tab-active class from all tabs
        $('a.nav-tab').removeClass('nav-tab-active');
        //  Add the nav-tab-active to the clicked tab
        $(this).addClass('nav-tab-active');
        //  Reload the data table
        dataTableLoad();

        return false;
    });
}


/**
 * Returns the name of the active tab
 */
function navGetActiveTab() {
    return $('a.nav-tab-active').first().attr('data-tab');
}


/**
 * Failure callback for a lookup. Displays an error in place of the main data table
 */
function renderError(jqXHR, textStatus, errorThrown) {
    let container = $(dataTableContainerSelector);

    console.warn('HTTP error occurred. Error: ' + errorThrown);
    container.html('<h4>Error occurred</h4><p>' + errorThrown + '</p>');
}


/**
 * Success callback for load of last updated message
 */
function renderLastUpdatedMessage(html) {
    lastUpdatedMessageSet(html);
    lastUpdatedMessageShow();
}


/**
 * Replaces the data table with whatever is in content. Used as a loading message
 */
function renderLoadingMessage(content) {
    let container = $(dataTableContainerSelector);
    content = '<div id="box-loading">' + content + '</div>';
    container.html(content);
}


/**
 * Success callback for data table lookup. Displays the data table
 */
function renderTable(tableHtml) {
    lastUpdatedLoad();
    let container = $(dataTableContainerSelector);
    console.info('Data table loaded');
    container.html(tableHtml);
    dataTableBindChartButtons();
}


/**
 * Click on button invokes update of remote data
 */
function updateButtonBindClick() {
    $('.update-data').bind('click', function () {
        console.info('Updating data from remote source');
        let updateRq = $.ajax({
            url       : '/update/start/',
            type      : 'post',
            dataType  : 'json',
            cache     : false,
            beforeSend: function () {
                lastUpdatedMessageHide();
                renderLoadingMessage('Data is being updated. Please wait');
            }
        });
        updateRq.done(updateButtonClickSuccess);
        updateRq.fail(renderError);

        return false;
    });
}

/**
 * Success callback for remote data source update process starting. Handles the error, if there was one
 * or starts polling for update process completion
 */
function updateButtonClickSuccess(updater) {
    //  If the update process started OK
    if (updater.result === 'success' && updater.started) {
        console.info('Update from remote source has started. Waiting for it to complete');
        updateButtonDisable();
        updateButtonPollStatus();
    }
    else {  //  Update process failed to start
        console.warn('Update from remote source could not be started');
        alert("Update from remote source could not be started!");
        dataTableLoad();
    }
}


/**
 * Disables the update button
 */
function updateButtonDisable() {
    let updateButton = $('.update-data');
    updateButton.attr('disabled', true);
    updateButton.addClass('btn-disabled');
}


/**
 * Enables the update button
 */
function updateButtonEnable() {
    let updateButton = $('.update-data');
    $('.update-data').attr('disabled', false);
    updateButton.removeClass('btn-disabled');
}

/**
 * Polls for the completion status of the remote data updater. Recursive - calls itself until
 * the completion status says it's complete or until max attempts have been made
 */
function updateButtonPollStatus(attempt) {

    if (undefined === attempt) {
        attempt = 0;
    }

    if (attempt >= maxPollAttempts) {
        console.warn('Too many polling attempts');
        alert("Update from remote source has timed out!");
        dataTableLoad();
    }
    else {
        console.log('Polling attempt ' + attempt + ' (max. ' + maxPollAttempts + ') started');
        attempt++;
        setTimeout(function () {
            let pollRq = $.ajax({
                url     : '/update/pollcomplete/',
                type    : 'post',
                dataType: 'json',
                cache   : false,

                success: function (poll) {
                    //  If the script has finished
                    if (poll.result === 'success' && poll.complete) {
                        lastUpdatedLoad();
                        updateButtonEnable();
                        dataTableLoad();
                    }
                    else {
                        console.log('Process still locked');
                        updateButtonPollStatus(attempt);
                    }
                }
            });
            pollRq.fail(renderError);

        }, pollAttemptTime);
    }
}


$().ready(function () {
    dataTableLoad();
    navBindTabClick();
    updateButtonBindClick();
});
