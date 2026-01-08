/**
 * archive.js
 * Handles interactivity for the Traffic Data Archive page
 */

let chartInstance = null;

/**
 * Initialize the traffic chart using Chart.js
 */
function initTrafficChart(chartData) {
    const ctx = document.getElementById('trafficChart');
    if (! ctx) return;

    // Destroy existing chart if it exists
    if (chartInstance) {
        chartInstance.destroy();
    }

    const labels = chartData.map(d => d.date);
    const lightData = chartData.map(d => d.light);
    const mediumData = chartData.map(d => d.medium);
    const heavyData = chartData. map(d => d.heavy);

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets:  [
                {
                    label: 'Light',
                    data: lightData,
                    backgroundColor: '#d4edda',
                    borderColor: '#28a745',
                    borderWidth: 1,
                },
                {
                    label:  'Medium',
                    data: mediumData,
                    backgroundColor: '#fff3cd',
                    borderColor: '#ffc107',
                    borderWidth: 1,
                },
                {
                    label: 'Heavy',
                    data:  heavyData,
                    backgroundColor: '#f8d7da',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio:  false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 15,
                        font: {
                            size: 14,
                            weight: '600',
                        },
                        color: '#1a2238',
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold',
                    },
                    bodyFont: {
                        size: 13,
                    },
                    borderColor: '#e63946',
                    borderWidth: 1,
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: {
                        display: false,
                    },
                    ticks: {
                        font: {
                            size: 12,
                        },
                        color: '#5a6c7d',
                    },
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                    },
                    ticks: {
                        font: {
                            size: 12,
                        },
                        color: '#5a6c7d',
                    },
                },
            },
        },
    });
}

/**
 * Initialize on page load
 */
document. addEventListener('DOMContentLoaded', function () {
    // Handle filter form submission
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            // Optional: add loading state
            console.log('Filters applied');
        });
    }

    // Add smooth scroll to table when filters are applied
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.toString()) {
        const tableSection = document.querySelector('.table-section');
        if (tableSection) {
            setTimeout(() => {
                tableSection.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }
    }
});