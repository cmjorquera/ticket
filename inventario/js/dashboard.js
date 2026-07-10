(function () {
    'use strict';

    var data = window.inventarioDashboardCharts || {};

    function chartColors(count) {
        var palette = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#20c997', '#6f42c1', '#0dcaf0', '#6c757d'];
        var colors = [];
        for (var i = 0; i < count; i += 1) {
            colors.push(palette[i % palette.length]);
        }
        return colors;
    }

    function createChart(id, type, source) {
        if (typeof Chart === 'undefined' || !source || !source.labels || !source.labels.length) {
            return;
        }
        var el = document.getElementById(id);
        if (!el) {
            return;
        }

        var colors = chartColors(source.labels.length);
        new Chart(el, {
            type: type,
            data: {
                labels: source.labels,
                datasets: [{
                    data: source.values,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: type !== 'bar',
                    position: 'bottom'
                },
                scales: type === 'bar' ? {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        },
                        gridLines: {
                            color: 'rgba(15, 23, 42, 0.08)'
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }]
                } : {},
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, chartData) {
                            var label = chartData.labels[tooltipItem.index] || '';
                            var value = chartData.datasets[0].data[tooltipItem.index] || 0;
                            return label + ': ' + value;
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        createChart('chartEstados', 'doughnut', data.estados);
        createChart('chartTipos', 'bar', data.tipos);
        createChart('chartRam', 'doughnut', data.ram);
        createChart('chartSistemas', 'bar', data.sistemas);
    });
}());
