<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Count - STAP Hub</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/vehicle-count.css') }}">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo" class="logo">
        
        <ul class="nav-links">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="{{ route('traffic.footage') }}">Traffic Footage</a></li>
            <li><a href="{{ route('traffic.archive') }}">Traffic Data Archive</a></li>
            <li><a href="{{ route('vehicle.count') }}" class="active">Vehicle Count</a></li>
        </ul>
    </nav>

    <!-- Main Container -->
    <main class="container">

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stat-card total">
                <div class="stat-header">
                    <h3>Total Vehicles</h3>
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5H6.5C5.84 5 5.28 5.42 5.08 6.01L3 12V20C3 20.55 3.45 21 4 21H5C5.55 21 6 20.55 6 20V19H18V20C18 20.55 18.45 21 19 21H20C20.55 21 21 20.55 21 20V12L18.92 6.01Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">
                    <span class="number" id="totalVehicles">0</span>
                    <span class="label">vehicles</span>
                </div>
            </div>

            <div class="stat-card average">
                <div class="stat-header">
                    <h3>Average per Period</h3>
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM9 17H7V10H9V17ZM13 17H11V7H13V17ZM17 17H15V13H17V17Z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">
                    <span class="number" id="averageCount">0</span>
                    <span class="label">vehicles</span>
                </div>
            </div>
        </section>

        <!-- Search and Filters Section -->
        <section class="search-filter-section">
            <div class="search-filter-controls">
                <div class="date-controls-wrapper">
                    <button class="nav-btn" id="prevPeriod">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.41 7.41L14 6L8 12L14 18L15.41 16.59L10.83 12Z"/>
                        </svg>
                    </button>
                    
                    <div class="date-picker-wrapper">
                        <input type="date" id="startDate" class="date-input">
                        <span class="date-separator">-</span>
                        <input type="date" id="endDate" class="date-input">
                    </div>
                    
                    <button class="nav-btn" id="nextPeriod">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8.59 16.59L10 18L16 12L10 6L8.59 7.41L13.17 12Z"/>
                        </svg>
                    </button>
                </div>
                
                <button id="filterBtn" class="filter-button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                    <span id="filterCount" class="filter-badge hidden">0</span>
                </button>
            </div>
        </section>

        <!-- Filter Panel -->
        <section id="filterPanel" class="filter-panel hidden">
            <div class="filter-grid-3">
                <div>
                    <label class="filter-label-block">View Type</label>
                    <div class="view-button-group">
                        <button class="view-btn-small" data-view="daily">Daily</button>
                        <button class="view-btn-small active" data-view="weekly">Weekly</button>
                        <button class="view-btn-small" data-view="monthly">Monthly</button>
                    </div>
                </div>
                
                <div>
                    <label class="filter-label-block">Vehicle Types</label>
                    <div class="checkbox-group-vertical">
                        <label class="checkbox-label-inline">
                            <input type="checkbox" checked data-vehicle="motorcycle" class="checkbox-input">
                            <span>Motorcycle</span>
                        </label>
                        <label class="checkbox-label-inline">
                            <input type="checkbox" checked data-vehicle="passenger_car" class="checkbox-input">
                            <span>Passenger Car</span>
                        </label>
                        <label class="checkbox-label-inline">
                            <input type="checkbox" checked data-vehicle="emergency_vehicle" class="checkbox-input">
                            <span>Emergency Vehicle</span>
                        </label>
                    </div>
                </div>
                
                <div class="filter-actions-column">
                    <button id="clearFilters" class="clear-filters-btn">
                        Clear All
                    </button>
                </div>
            </div>
        </section>


        <!-- Charts Section -->
        <section class="charts-section">
            <!-- Vehicle Count Table -->
            <div class="chart-container table-container">
                <div class="chart-header">
                    <h2>Vehicle Count Records</h2>
                    <button class="download-btn" id="downloadData">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 12V19H5V12H3V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V12H19ZM13 12.67L15.59 10.09L17 11.5L12 16.5L7 11.5L8.41 10.09L11 12.67V3H13V12.67Z"/>
                        </svg>
                        Download Data
                    </button>
                </div>
                
                <div class="table-wrapper">
                    <table class="vehicle-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Motorcycle</th>
                                <th>Passenger Car</th>
                                <th>Emergency Vehicle</th>
                                <th>Total Vehicles</th>
                            </tr>
                        </thead>
                        <tbody id="vehicleTableBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="chart-container pie-chart-container">
                <div class="chart-header">
                    <h2>Vehicle Type Distribution</h2>
                    <div class="filter-controls">
                        <label class="filter-checkbox">
                            <input type="checkbox" checked data-vehicle="motorcycle">
                            <span>Motorcycle</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" checked data-vehicle="passenger_car">
                            <span>Passenger Car</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" checked data-vehicle="emergency_vehicle">
                            <span>Emergency Vehicle</span>
                        </label>
                    </div>
                </div>
                <div class="chart-wrapper pie-wrapper">
                    <canvas id="vehiclePieChart"></canvas>
                </div>
            </div>
        </section>
    </main>

    <!-- Load Chart.js first, then our custom script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/vehicle-count.js') }}"></script>
</body>
</html>