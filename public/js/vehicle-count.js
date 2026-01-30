// ============================================
// VEHICLE COUNT PAGE - JAVASCRIPT (FIXED)
// ============================================

// Global variables
let barChart, pieChart;
let currentView = 'daily';
let selectedVehicleTypes = ['motorcycle', 'passenger_car', 'emergency_vehicle'];
let currentData = [];

// ============================================
// HISTORICAL DATA (Jan 25-30, 2026)
// ============================================
const historicalData = {
    '2026-01-25': {
        total: 124,
        motorcycle: 58,
        passenger_car: 63,
        emergency_vehicle: 3
    },
    '2026-01-26': {
        total: 142,
        motorcycle: 68,
        passenger_car: 70,
        emergency_vehicle: 4
    },
    '2026-01-27': {
        total: 118,
        motorcycle: 52,
        passenger_car: 62,
        emergency_vehicle: 4
    },
    '2026-01-28': {
        total: 156,
        motorcycle: 75,
        passenger_car: 78,
        emergency_vehicle: 3
    },
    '2026-01-29': {
        total: 135,
        motorcycle: 61,
        passenger_car: 71,
        emergency_vehicle: 3
    },
    '2026-01-30': {
        total: 148,
        motorcycle: 70,
        passenger_car: 74,
        emergency_vehicle: 4
    }
};

// ============================================
// DATA GENERATION FUNCTIONS
// ============================================

function generateHourlyData(date) {
    const dayData = historicalData[date];
    if (!dayData) return [];
    
    const hourlyData = [];
    const hours = 24;
    
    // Distribution pattern - peak hours at 7-9am and 5-7pm
    const peakHours = [7, 8, 17, 18];
    const normalHours = [6, 9, 10, 11, 12, 13, 14, 15, 16, 19, 20];
    const lowHours = [0, 1, 2, 3, 4, 5, 21, 22, 23];
    
    for (let hour = 0; hour < hours; hour++) {
        let multiplier;
        if (peakHours.includes(hour)) {
            multiplier = 0.08; // 8% of daily total during peak hours
        } else if (normalHours.includes(hour)) {
            multiplier = 0.045; // 4.5% during normal hours
        } else {
            multiplier = 0.01; // 1% during low hours
        }
        
        const hourTotal = Math.round(dayData.total * multiplier);
        const motorcycleRatio = dayData.motorcycle / dayData.total;
        const passengerRatio = dayData.passenger_car / dayData.total;
        const emergencyRatio = dayData.emergency_vehicle / dayData.total;
        
        hourlyData.push({
            time: `${hour.toString().padStart(2, '0')}:00`,
            total: hourTotal,
            motorcycle: Math.round(hourTotal * motorcycleRatio),
            passenger_car: Math.round(hourTotal * passengerRatio),
            emergency_vehicle: Math.round(hourTotal * emergencyRatio)
        });
    }
    
    return hourlyData;
}

function generateDataForView(startDate, endDate, view) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const data = [];
    
    if (view === 'daily') {
        // For daily view, use the exact date
        const dateStr = startDate;
        if (historicalData[dateStr]) {
            return generateHourlyData(dateStr);
        }
    } else if (view === 'weekly' || view === 'monthly') {
        // For weekly/monthly view, aggregate by day
        let current = new Date(start);
        while (current <= end) {
            const dateStr = current.toISOString().split('T')[0];
            if (historicalData[dateStr]) {
                data.push({
                    date: dateStr,
                    time: '00:00',
                    ...historicalData[dateStr]
                });
            }
            current.setDate(current.getDate() + 1);
        }
    }
    
    return data;
}

// ============================================
// INITIALIZE PAGE
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Checking Chart.js...');
    console.log('Chart available?', typeof Chart !== 'undefined');
    
    // Set initial dates to Jan 25, 2026
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    startDate.value = '2026-01-25';
    endDate.value = '2026-01-25';
    
    // Wait a bit for Chart.js to fully load
    setTimeout(() => {
        console.log('Initializing charts after delay...');
        initializeCharts();
        updateData();
        setupEventListeners();
    }, 100);
});

// ============================================
// CHART INITIALIZATION
// ============================================

function initializeCharts() {
    console.log('Initializing charts...');
    
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded!');
        return;
    }
    
    try {
        // Bar Chart
        const barCtx = document.getElementById('vehicleBarChart');
        if (!barCtx) {
            console.error('Bar chart canvas not found');
            return;
        }
        
        barChart = new Chart(barCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Total Vehicles (Both Intersections)',
                    data: [],
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Vehicles: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    }
                }
            }
        });
        
        console.log('Bar chart created successfully');
        
        // Pie Chart
        const pieCtx = document.getElementById('vehiclePieChart');
        if (!pieCtx) {
            console.error('Pie chart canvas not found');
            return;
        }
        
        pieChart = new Chart(pieCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Motorcycle', 'Passenger Car', 'Emergency Vehicle'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#10b981', '#6366f1', '#f59e0b'],
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
                            font: { size: 13 }
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
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Pie chart created successfully');
    } catch (error) {
        console.error('Error creating charts:', error);
    }
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
                if (!selectedVehicleTypes.includes(vehicleType)) {
                    selectedVehicleTypes.push(vehicleType);
                }
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
    
    startDate.value = start.toISOString().split('T')[0];
    endDate.value = end.toISOString().split('T')[0];
    
    updateData();
}

function updateData() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) return;
    
    console.log('Updating data for:', startDate, 'to', endDate, 'View:', currentView);
    
    // Generate new data
    currentData = generateDataForView(startDate, endDate, currentView);
    
    console.log('Generated data points:', currentData.length);
    
    // Update all displays
    updateStatistics();
    updateBarChart();
    updatePieChart();
}

function updateStatistics() {
    let totalVehicles = 0;
    
    currentData.forEach(entry => {
        totalVehicles += entry.total || 0;
    });
    
    const average = currentData.length > 0 ? Math.round(totalVehicles / currentData.length) : 0;
    
    // Animate numbers
    animateValue('totalVehicles', 0, totalVehicles, 800);
    animateValue('averageCount', 0, average, 800);
}

function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    if (!element) return;
    
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
    if (!barChart) {
        console.error('Bar chart not initialized');
        return;
    }
    
    const labels = [];
    const data = [];
    
    currentData.forEach(entry => {
        if (currentView === 'daily') {
            labels.push(entry.time);
        } else {
            labels.push(new Date(entry.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            }));
        }
        data.push(entry.total);
    });
    
    console.log('Updating bar chart with', data.length, 'data points');
    
    barChart.data.labels = labels;
    barChart.data.datasets[0].data = data;
    barChart.update();
}

function updatePieChart() {
    if (!pieChart) {
        console.error('Pie chart not initialized');
        return;
    }
    
    const totals = {
        motorcycle: 0,
        passenger_car: 0,
        emergency_vehicle: 0
    };
    
    currentData.forEach(entry => {
        if (selectedVehicleTypes.includes('motorcycle')) {
            totals.motorcycle += entry.motorcycle || 0;
        }
        if (selectedVehicleTypes.includes('passenger_car')) {
            totals.passenger_car += entry.passenger_car || 0;
        }
        if (selectedVehicleTypes.includes('emergency_vehicle')) {
            totals.emergency_vehicle += entry.emergency_vehicle || 0;
        }
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
    
    console.log('Updating pie chart with data:', data);
    
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
    let csv = 'Date,Time,Motorcycle,Passenger Car,Emergency Vehicle,Total\n';
    
    currentData.forEach(entry => {
        const date = entry.date || startDate;
        const time = entry.time || '00:00';
        csv += `${date},${time},${entry.motorcycle},${entry.passenger_car},${entry.emergency_vehicle},${entry.total}\n`;
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