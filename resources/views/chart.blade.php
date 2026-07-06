{{-- @extends('layouts.master') --}}
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/emn178/chartjs-plugin-labels/src/chartjs-plugin-labels.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-doughnutlabel/2.0.3/chartjs-plugin-doughnutlabel.js"></script>
</head>
<style>
    html, body {width: 100%; height: 100%; margin: 0;}
.chart-inner {padding: 20px;}
#BA-chart-job-error {margin: 0 auto;}
</style>

{{-- @section('content') --}}

<div class="chart-inner">
	<canvas id="BA-chart-job-error" width="800" height="600"></canvas>
</div>
<!-- End New Free Trial Area -->
{{-- @endsection --}}

<script>
    var BAChartDataValue = [
        500,
        170,
        330,
    ]; /* 개발 연동 데이터 */
    var BAChartDataLabel = [
        'A',
        'B',
        'c',
    ]; /* 개발 연동 데이터 */
    var BAChartJobErrColors = [
        'rgba(26, 176, 169, 1)',
        'rgba(119, 209, 190, 1)',
        'rgba(127, 188, 212, 1)',
        'rgba(28, 120, 212, 1)',
        'rgba(3, 3, 158, 1)',
    ];

    var BAChartCountTotal = 0;
    if (BAChartDataValue.length > 0) {
        BAChartCountTotal = BAChartDataValue.reduce(function(acc, currentVal, currentIdx, arr){
            return acc + currentVal;
        }, 0);
    }

    window.addEventListener('load', function(){
        var BAChartCtx = document.getElementById('BA-chart-job-error').getContext('2d');
        var BAChartJobErr = new Chart(BAChartCtx, {
            type: 'doughnut',
            data: {
                labels: BAChartDataLabel,
                datasets: [{
                    data: BAChartDataValue,
                    backgroundColor: BAChartJobErrColors,
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    position: 'top',
                    fontSize: 12,
                    fontColor: '#000',
                    fontStyle: 'bold',
                    padding: 24,
                    text: '오류 현황',
                },
                plugins: {
                    labels: [
                        {
                            render: 'label',
                            fontColor: '#000',
                            position: 'outside'
                        },
                        {
                            render: 'percentage',
                            fontColor: '#fff',
                        }
                    ],
                    doughnutlabel: {
                        labels: [
                            {
                                text: 'Total: ' + BAChartCountTotal,
                            }
                        ]
                    }
                },
                legend: {
                    display: false
                }
            }
        });
    });
</script>
