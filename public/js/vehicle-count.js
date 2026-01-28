// ============================================
// VEHICLE COUNT PAGE - JAVASCRIPT
// ============================================

// Global variables
let barChart, pieChart;
let currentView = 'daily';
let selectedVehicleTypes = ['motorcycle', 'passenger_car', 'emergency_vehicle'];
let currentData = [];

// ============================================
// SAMPLE DATA GENERATION
// ============================================

function generateSampleData(startDate, endDate, view) {
    const data = [];
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (view === 'daily') {
        // Generate hourly data
        for (let d = new Date(start); d <= end; d.setHours(d.getHours() + 1)) {
            data.push({
                date: d.toISOString().split('T')[0],
                time: d.toTimeString().slice(0, 5),
                mayor_gil_fernando: {
                    motorcycle: Math.floor(Math.random() * 50) + 10,
                    passenger_car: Math.floor(Math.random() * 150) + 50,
                    emergency_vehicle: Math.floor(Math.random() * 5)
                },
                sumulong_highway: {
                    motorcycle: Math.floor(Math.random() * 40) + 10,
                    passenger_car: Math.floor(Math.random() * 120) + 40,
                    emergency_vehicle: Math.floor(Math.random() * 3)
                }
            });
        }
    } else if (view === 'weekly') {
        // Generate daily data for a week
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            data.push({
                date: d.toISOString().split('T')[0],
                time: '00:00',
                mayor_gil_fernando: {
                    motorcycle: Math.floor(Math.random() * 800) + 200,
                    passenger_car: Math.floor(Math.random() * 2400) + 800,
                    emergency_vehicle: Math.floor(Math.random() * 80) + 10
                },
                sumulong_highway: {
                    motorcycle: Math.floor(Math.random() * 600) + 200,
                    passenger_car: Math.floor(Math.random() * 2000) + 600,
                    emergency_vehicle: Math.floor(Math.random() * 50) + 10
                }
            });
        }
    } else if (view === 'monthly') {
        // Generate weekly data for a month
        const weeks = Math.ceil((end - start) / (7 * 24 * 60 * 60 * 1000));
        for (let i = 0; i < weeks; i++) {
            const weekStart = new Date(start);
            weekStart.setDate(weekStart.getDate() + (i * 7));
            data.push({
                date: weekStart.toISOString().split('T')[0],
                time: '00:00',
                mayor_gil_fernando: {
                    motorcycle: Math.floor(Math.random() * 5000) + 1500,
                    passenger_car: Math.floor(Math.random() * 15000) + 5000,
                    emergency_vehicle: Math.floor(Math.random() * 500) + 50
                },
                sumulong_highway: {
                    motorcycle: Math.floor(Math.random() * 4000) + 1000,
                    passenger_car: Math.floor(Math.random() * 12000) + 4000,
                    emergency_vehicle: Math.floor(Math.random() * 300) + 30
                }
            });
        }
    }
    
    return data;
}

// ============================================
// INITIALIZE PAGE
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Set initial dates
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    document.getElementById('startDate').valueAsDate = yesterday;
    document.getElementById('endDate').valueAsDate = today;
    
    // Generate initial data
    updateData();
    
    // Initialize charts
    initializeCharts();
    
    // Set up event listeners
    setupEventListeners();
});

// ============================================
// CHART INITIALIZATION
// ============================================

function initializeCharts() {
    // Bar Chart
    const barCtx = document.getElementById('vehicleBarChart').getContext('2d');
    barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Mayor Gil Fernando Ave',
                    data: [],
                    backgroundColor: '#a78bfa',
                    borderRadius: 6
                },
                {
                    label: 'Sumulong Highway',
                    data: [],
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
    
    // Pie Chart
    const pieCtx = document.getElementById('vehiclePieChart').getContext('2d');
    pieChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Motorcycle', 'Passenger Car', 'Emergency Vehicle'],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#10b981',
                    '#6366f1',
                    '#f59e0b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// ============================================
// EVENT LISTENERS
// ============================================

function setupEventListeners() {
    // Date navigation
    document.getElementById('prevPeriod').addEventListener('click', function() {
        adjustDateRange(-1);
    });
    
    document.getElementById('nextPeriod').addEventListener('click', function() {
        adjustDateRange(1);
    });
    
    // Date inputs
    document.getElementById('startDate').addEventListener('change', updateData);
    document.getElementById('endDate').addEventListener('change', updateData);
    
    // View buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentView = this.dataset.view;
            updateData();
        });
    });
    
    // Vehicle type filters
    document.querySelectorAll('.filter-checkbox input').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const vehicleType = this.dataset.vehicle;
            if (this.checked) {
                selectedVehicleTypes.push(vehicleType);
            } else {
                selectedVehicleTypes = selectedVehicleTypes.filter(t => t !== vehicleType);
            }
            updatePieChart();
        });
    });
    
    // Download button
    document.getElementById('downloadData').addEventListener('click', downloadData);
}

// ============================================
// DATA UPDATES
// ============================================

function adjustDateRange(direction) {
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    const start = new Date(startDate.value);
    const end = new Date(endDate.value);
    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    
    if (currentView === 'daily') {
        start.setDate(start.getDate() + direction);
        end.setDate(end.getDate() + direction);
    } else if (currentView === 'weekly') {
        start.setDate(start.getDate() + (7 * direction));
        end.setDate(end.getDate() + (7 * direction));
    } else if (currentView === 'monthly') {
        start.setMonth(start.getMonth() + direction);
        end.setMonth(end.getMonth() + direction);
    }
    
    startDate.valueAsDate = start;
    endDate.valueAsDate = end;
    
    updateData();
}

function updateData() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) return;
    
    // Generate new data
    currentData = generateSampleData(startDate, endDate, currentView);
    
    // Update all displays
    updateStatistics();
    updateBarChart();
    updatePieChart();
}

function updateStatistics() {
    let mayorGilTotal = 0;
    let sumulongTotal = 0;
    let peakHour = '';
    let peakCount = 0;
    
    currentData.forEach(entry => {
        const mayorGil = Object.values(entry.mayor_gil_fernando).reduce((a, b) => a + b, 0);
        const sumulong = Object.values(entry.sumulong_highway).reduce((a, b) => a + b, 0);
        const total = mayorGil + sumulong;
        
        mayorGilTotal += mayorGil;
        sumulongTotal += sumulong;
        
        if (total > peakCount) {
            peakCount = total;
            peakHour = entry.time;
        }
    });
    
    const average = Math.round((mayorGilTotal + sumulongTotal) / currentData.length);
    
    // Animate numbers
    animateValue('mayorGilTotal', 0, mayorGilTotal, 1000);
    animateValue('sumulongTotal', 0, sumulongTotal, 1000);
    animateValue('averageCount', 0, average, 1000);
    
    document.getElementById('peakTime').textContent = peakHour || '--:--';
}

function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.round(current).toLocaleString();
    }, 16);
}

function updateBarChart() {
    const labels = [];
    const mayorGilData = [];
    const sumulongData = [];
    
    currentData.forEach(entry => {
        if (currentView === 'daily') {
            labels.push(entry.time);
        } else {
            labels.push(new Date(entry.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            }));
        }
        
        mayorGilData.push(Object.values(entry.mayor_gil_fernando).reduce((a, b) => a + b, 0));
        sumulongData.push(Object.values(entry.sumulong_highway).reduce((a, b) => a + b, 0));
    });
    
    barChart.data.labels = labels;
    barChart.data.datasets[0].data = mayorGilData;
    barChart.data.datasets[1].data = sumulongData;
    barChart.update();
}

function updatePieChart() {
    const totals = {
        motorcycle: 0,
        passenger_car: 0,
        emergency_vehicle: 0
    };
    
    currentData.forEach(entry => {
        Object.keys(totals).forEach(type => {
            if (selectedVehicleTypes.includes(type)) {
                totals[type] += entry.mayor_gil_fernando[type] + entry.sumulong_highway[type];
            }
        });
    });
    
    const labels = [];
    const data = [];
    const colors = [];
    
    const colorMap = {
        motorcycle: '#10b981',
        passenger_car: '#6366f1',
        emergency_vehicle: '#f59e0b'
    };
    
    const labelMap = {
        motorcycle: 'Motorcycle',
        passenger_car: 'Passenger Car',
        emergency_vehicle: 'Emergency Vehicle'
    };
    
    selectedVehicleTypes.forEach(type => {
        if (totals[type] > 0) {
            labels.push(labelMap[type]);
            data.push(totals[type]);
            colors.push(colorMap[type]);
        }
    });
    
    pieChart.data.labels = labels;
    pieChart.data.datasets[0].data = data;
    pieChart.data.datasets[0].backgroundColor = colors;
    pieChart.update();
}

// ============================================
// DOWNLOAD FUNCTIONALITY
// ============================================

function downloadData() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Create CSV content
    let csv = 'Date,Time,Location,Motorcycle,Passenger Car,Emergency Vehicle,Total\n';
    
    currentData.forEach(entry => {
        // Mayor Gil Fernando Ave
        const mayorGilTotal = Object.values(entry.mayor_gil_fernando).reduce((a, b) => a + b, 0);
        csv += `${entry.date},${entry.time},Mayor Gil Fernando Ave,`;
        csv += `${entry.mayor_gil_fernando.motorcycle},`;
        csv += `${entry.mayor_gil_fernando.passenger_car},`;
        csv += `${entry.mayor_gil_fernando.emergency_vehicle},`;
        csv += `${mayorGilTotal}\n`;
        
        // Sumulong Highway
        const sumulongTotal = Object.values(entry.sumulong_highway).reduce((a, b) => a + b, 0);
        csv += `${entry.date},${entry.time},Sumulong Highway,`;
        csv += `${entry.sumulong_highway.motorcycle},`;
        csv += `${entry.sumulong_highway.passenger_car},`;
        csv += `${entry.sumulong_highway.emergency_vehicle},`;
        csv += `${sumulongTotal}\n`;
    });
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `vehicle-count-${startDate}-to-${endDate}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}