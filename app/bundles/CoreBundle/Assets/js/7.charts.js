//set global Chart defaults
if (typeof Chart != 'undefined') {
    // configure global Chart options
    Chart.defaults.global.elements.line.borderWidth = 2;
    Chart.defaults.global.elements.point.radius = 0;
    Chart.defaults.global.legend.labels.boxWidth = 12;
    Chart.defaults.global.maintainAspectRatio = false;
    Chart.defaults.scale.ticks.padding = 10;
    Chart.defaults.global.elements.point.hoverRadius = 6;
    Chart.defaults.global.elements.point.hitRadius = 20;
    Chart.defaults.global.legend.labels.usePointStyle = true;
    Chart.defaults.global.legend.labels.pointStyle = 'circle';
}

/**
 * Render the chart.js charts
 *
 * @param mQuery|string scope
 */
MailVotech.renderCharts = function(scope) {
    var charts = [];
    if (!MailVotech.chartObjects) MailVotech.chartObjects = [];

    if (mQuery.type(scope) === 'string') {
        charts = mQuery(scope).find('canvas.chart');
    } else if (scope) {
        charts = scope.find('canvas.chart');
    } else {
        charts = mQuery('canvas.chart');
    }

    if (charts.length) {
        charts.each(function(index, canvas) {
            canvas = mQuery(canvas);
            if (!canvas.hasClass('chart-rendered')) {
                if (canvas.hasClass('line-chart')) {
                    MailVotech.renderLineChart(canvas)
                } else if (canvas.hasClass('pie-chart')) {
                    MailVotech.renderPieChart(canvas)
                } else if (canvas.hasClass('bar-chart')) {
                    MailVotech.renderBarChart(canvas)
                } else if (canvas.hasClass('liefechart-bar-chart')) {
                    MailVotech.renderLifechartBarChart(canvas)
                } else if (canvas.hasClass('simple-bar-chart')) {
                    MailVotech.renderSimpleBarChart(canvas)
                } else if (canvas.hasClass('horizontal-bar-chart')) {
                    MailVotech.renderHorizontalBarChart(canvas)
                } else if (canvas.hasClass('hour-chart')) {
                    MailVotech.renderHourChart(canvas)
                }
            }
            canvas.addClass('chart-rendered');
        });
    }
};

/**
 * Render the chart.js line chart
 *
 * @param mQuery element canvas
 */
MailVotech.renderLineChart = function(canvas) {
    var data = JSON.parse(canvas.text());
    if (!data.labels.length || !data.datasets.length) return;
    var chart = new Chart(canvas, {
        type: 'line',
        data: data,
        options: {
            lineTension : 0.2,
            borderWidth: 1,
            tooltips: {
                mode: 'index',
                intersect: false
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        callback: function(value, index, values) {
                            if (index === 0 || index === values.length - 1) {
                                return value;
                            }
                            return '';
                        }
                    }
                }],
                yAxes: [{
                    afterBuildTicks: function(scale) {
                        scale.ticks = [];
                        scale.ticks.push(scale.min);
                        scale.ticks.push((scale.max - scale.min) / 2);
                        scale.ticks.push(scale.max);
                    },
                    gridLines: {
                        drawBorder: false,
                    },
                    ticks: {
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            if (index === 0 || index === values.length - 1) {
                                return value;
                            }
                            if (/^\d+\.5$/.test(value.toString())) {
                                return '';
                            }
                            if (index === Math.floor(values.length / 2)) {
                                return value !== 0.5 ? value : '';
                            }
                            return '';
                        }
                        
                    }
                }]
            }
        }
    });
    MailVotech.chartObjects.push(chart);
};

MailVotech.renderHourChart = function(canvas) {
    const data = JSON.parse(canvas.text());
    const chart = new Chart(canvas, {
        type: 'line',
        data,
        options: {
            tooltips: { mode: 'index', intersect: false },
            scales: {
                xAxes: [{
                    gridLines: { display: false },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 6,
                        maxRotation: 0,
                        callback: value => value.split(' - ')[0]
                    }
                }],
                yAxes: [{
                    afterBuildTicks: scale => {
                        scale.ticks = [scale.min, (scale.max - scale.min) / 2, scale.max];
                    },
                    gridLines: { drawBorder: false },
                    ticks: {
                        beginAtZero: true,
                        callback: (value, index, values) => {
                            if (index === 0 || index === values.length - 1) return value;
                            if (/^\d+\.5$/.test(value.toString())) return '';
                            if (index === Math.floor(values.length / 2)) return value !== 0.5 ? value : '';
                            return '';
                        }
                    }
                }]
            }
        }
    });
    MailVotech.chartObjects.push(chart);
};


/**
 * Render the chart.js pie chart
 *
 * @param mQuery element canvas
 */
MailVotech.renderPieChart = function(canvas) {
    var data = JSON.parse(canvas.text());
    var options = {borderWidth: 1};
    var disableLegend = canvas.attr('data-disable-legend');
    if (typeof disableLegend !== 'undefined' && disableLegend !== false) {
        options.legend = {
            display: false
        }
    }
    // data = MailVotech.emulateNoDataForPieChart(data);
    var chart = new Chart(canvas, {
        type: 'pie',
        data: data,
        options: options
    });
    MailVotech.chartObjects.push(chart);
};

/**
 * Render the chart.js bar chart
 *
 * @param mQuery element canvas
 */
MailVotech.renderBarChart = function(canvas) {
    var data = JSON.parse(canvas.text());
    var chart = new Chart(canvas, {
        type: 'bar',
        data: data,
        options: {
            scales: {
                xAxes: [{
                    barPercentage: 0.9,
                }]
            }
        }
    });
    MailVotech.chartObjects.push(chart);
};

/**
 * Render the chart.js bar chart
 *
 * @param mQuery element canvas
 */
MailVotech.renderLifechartBarChart = function(canvas) {
    var canvasWidth = mQuery(canvas).parent().width();
    var barWidth    = (canvasWidth < 300) ? 5 : 25;
    var data = JSON.parse(canvas.text());
    var chart = new Chart(canvas, {
        type: 'bar',
        data: data,
        options: {
            scales: {
                xAxes: [
                    {
                        barThickness: barWidth,
                    }
                ]
            }
        }
    });
    MailVotech.chartObjects.push(chart);
};

/**
 * Render the chart.js simple bar chart
 *
 * @param mQuery element canvas
 */
MailVotech.renderSimpleBarChart = function(canvas) {
    var data = JSON.parse(canvas.text());
    var chart = new Chart(canvas, {
        type: 'bar',
        data: data,
        options: {
            scales: {
                xAxes: [{
                    stacked: false,
                    ticks: {fontSize: 9},
                    gridLines: {display:false},
                }],
                yAxes: [{
                    display: false,
                    stacked: false,
                    ticks: {beginAtZero: true, display: false},
                    gridLines: {display:false}
                }],
                display: false
            },
            legend: {
                display: false
            }
        }
    });
    MailVotech.chartObjects.push(chart);
};

/**
 * Render the chart.js simple bar chart
 *
 * @param mQuery element canvas
 */
MailVotech.renderHorizontalBarChart = function(canvas) {
    var data = JSON.parse(canvas.text());
    var chart = new Chart(canvas, {
        type: 'horizontalBar',
        data: data,
        options: {
            scales: {
                xAxes: [{
                    display: true,
                    stacked: false,
                    gridLines: {display:false},
                    ticks: {beginAtZero: true,display: true, fontSize: 8, stepSize: 5}
                }],
                yAxes: [{
                    stacked: false,
                    ticks: {beginAtZero: true, display: true, fontSize: 9},
                    gridLines: {display:false},
                    barPercentage: 0.5,
                    categorySpacing: 1
                }],
                display: false
            },
            legend: {
                display: false
            },
            tooltips: {
                mode: 'single',
                bodyFontSize: 9,
                bodySpacing: 0,
                callbacks: {
                    title: function(tooltipItems, data) {
                        // Title doesn't make sense for scatter since we format the data as a point
                        return '';
                    },
                    label: function(tooltipItem, data) {
                        return  tooltipItem.xLabel + ': ' + tooltipItem.yLabel;
                    }
                }

            }
        }
    });
    MailVotech.chartObjects.push(chart);
};

/**
 * Initialize graph date range selectors
 */
MailVotech.initDateRangePicker = function (fromId, toId) {
    var dateFrom = mQuery(fromId);
    var dateTo = mQuery(toId);

    if (dateFrom.length && dateTo.length) {
        dateFrom.datetimepicker({
            format: 'M j, Y',
            onShow: function (ct) {
                this.setOptions({
                    maxDate: dateTo.val() ? new Date(dateTo.val()) : false
                });
            },
            timepicker: false,
            scrollMonth: false,
            scrollInput: false
        });

        dateTo.datetimepicker({
            format: 'M j, Y',
            onShow: function (ct) {
                this.setOptions({
                    maxDate: new Date(),
                    minDate: dateFrom.val() ? new Date(dateFrom.val()) : false
                });
            },
            timepicker: false,
            scrollMonth: false,
            scrollInput: false
        });
    }
};

MailVotech.setDateRange = (option) => {
  const today = new Date();
  const dayInMilliseconds = 24 * 60 * 60 * 1000;
  let fromDate;
  let toDate;

  switch (option) {
    case 'today':
      fromDate = today;
      toDate = today;
      break;
    case 'yesterday':
      fromDate = new Date(today.getTime() - dayInMilliseconds);
      toDate = fromDate;
      break;
    default:
      if (typeof option !== 'number') {
        console.error('Invalid date range option.');

        return;
      }

      fromDate = new Date(today.getTime() - (option * dayInMilliseconds));
      toDate = today;
  }

  const dateFromInput = document.getElementById('daterange_date_from');
  const dateToInput = document.getElementById('daterange_date_to');
  const applyButton = document.getElementById('daterange_apply');

  if (!dateFromInput || !dateToInput || !applyButton) {
    console.error('Date range inputs are missing.');

    return;
  }

  dateFromInput.value = MailVotech.formatDate(fromDate);
  dateToInput.value = MailVotech.formatDate(toDate);
  applyButton.click();
};

MailVotech.formatDate = (date) => {
  const monthNames = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec'
  ];

  return `${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
};

document.addEventListener('click', (event) => {
  if (!(event.target instanceof Element)) {
    return;
  }

  const dateRangeTrigger = event.target.closest('[data-date-range-option]');

  if (!dateRangeTrigger) {
    return;
  }

  event.preventDefault();

  const option = dateRangeTrigger.dataset.dateRangeOption;
  const dateRangeOption = /^\d+$/.test(option) ? Number(option) : option;

  MailVotech.setDateRange(dateRangeOption);
});

/**
 * Helper function to timeframe based graphs
 *
 * @param element
 * @param action
 * @param query
 * @param callback
 */
MailVotech.getChartData = function(element, action, query, callback) {
    var element = mQuery(element);
    var wrapper = element.closest('ul');
    var button  = mQuery('#time-scopes .button-label');
    wrapper.find('a').removeClass('bg-primary');
    element.addClass('bg-primary');
    button.text(element.text());

    // Append action
    query = query + '&action=' + action;

    mQuery.ajax({
        showLoadingBar: true,
        url: mailvotechAjaxUrl,
        type: 'POST',
        data: query,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                MailVotech.stopPageLoadingBar();
                if (typeof callback == 'function') {
                    callback(response);
                } else if(typeof window["MailVotech"][callback] !== 'undefined') {
                    window["MailVotech"][callback].apply('window', [response]);
                }
            }
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        }
    });
};

/**
 * Emulates empty data object if doughnut/pie chart data are empty.
 *
 *
 * @param data
 */
MailVotech.emulateNoDataForPieChart = function (data) {
    var dataEmpty = true;
    mQuery.each(data, function (i, part) {
        if (part.value) {
            dataEmpty = false;
        }
    });
    if (dataEmpty) {
        data = [{
            value: 1,
            color: "#efeeec",
            highlight: "#EBEBEB",
            label: "No data"
        }];
    }
    return data;
};
