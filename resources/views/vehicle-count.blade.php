<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Count - STAP Hub</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/vehicle-count.css') }}">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- Main Content -->
    <main class="container">
        <!-- Header Section -->
        <section class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5H6.5C5.84 5 5.28 5.42 5.08 6.01L3 12V20C3 20.55 3.45 21 4 21H5C5.55 21 6 20.55 6 20V19H18V20C18 20.55 18.45 21 19 21H20C20.55 21 21 20.55 21 20V12L18.92 6.01ZM6.5 16C5.67 16 5 15.33 5 14.5C5 13.67 5.67 13 6.5 13C7.33 13 8 13.67 8 14.5C8 15.33 7.33 16 6.5 16ZM17.5 16C16.67 16 16 15.33 16 14.5C16 13.67 16.67 13 17.5 13C18.33 13 19 13.67 19 14.5C19 15.33 18.33 16 17.5 16ZM5 11L6.5 6.5H17.5L19 11H5Z" fill="#6366f1"/>
                    </svg>
                </div>
                <div>
                    <h1>Vehicle Count</h1>
                    <p>View and analyze historical vehicle counting data</p>
                </div>
            </div>
        </section>

        <!-- Info Cards Section -->
        <section class="info-section">
            <div class="info-card">
                <div class="info-icon camera">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17 10.5V7C17 6.45 16.55 6 16 6H4C3.45 6 3 6.45 3 7V17C3 17.55 3.45 18 4 18H16C16.55 18 17 17.55 17 17V13.5L21 17.5V6.5L17 10.5Z"/>
                    </svg>
                </div>
                <div>
                    <p class="info-label">Intersection</p>
                    <p class="info-value">Mayor Gil Fernando Ave & Sumulong Highway</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon tracking">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12C2 17.52 6.47 22 11.99 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 11.99 2ZM12 20C7.58 20 4 16.42 4 12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20ZM12.5 7H11V13L16.25 16.15L17 14.92L12.5 12.25V7Z"/>
                    </svg>
                </div>
                <div>
                    <p class="info-label">Tracking Period</p>
                    <p class="info-value">24 Hours (Every Day)</p>
                </div>
            </div>
        </section>

        <!-- Controls Section -->
        <section class="controls-section">
            <div class="date-controls">
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

            <div class="view-controls">
                <button class="view-btn active" data-view="daily">Daily</button>
                <button class="view-btn" data-view="weekly">Weekly</button>
                <button class="view-btn" data-view="monthly">Monthly</button>
            </div>
        </section>

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stat-card mayor-gil">
                <div class="stat-header">
                    <h3>Mayor Gil Fernando Ave</h3>
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7.41 8.59L12 13.17L16.59 8.59L18 10L12 16L6 10L7.41 8.59Z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">
                    <span class="number" id="mayorGilTotal">0</span>
                    <span class="label">vehicles</span>
                </div>
            </div>

            <div class="stat-card sumulong">
                <div class="stat-header">
                    <h3>Sumulong Highway</h3>
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7.41 8.59L12 13.17L16.59 8.59L18 10L12 16L6 10L7.41 8.59Z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">
                    <span class="number" id="sumulongTotal">0</span>
                    <span class="label">vehicles</span>
                </div>
            </div>

            <div class="stat-card peak">
                <div class="stat-header">
                    <h3>Peak Traffic</h3>
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L4 7V13C4 17.55 7.16 21.74 12 23C16.84 21.74 20 17.55 20 13V7L12 2Z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">
                    <span class="number" id="peakTime">--:--</span>
                    <span class="label">time</span>
                </div>
            </div>

            <div class="stat-card average">
                <div class="stat-header">
                    <h3>Average per Hour</h3>
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

        <!-- Charts Section -->
        <section class="charts-section">
            <!-- Bar Chart -->
            <div class="chart-container bar-chart-container">
                <div class="chart-header">
                    <h2>Vehicle Counting Timeline</h2>
                    <button class="download-btn" id="downloadData">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 12V19H5V12H3V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V12H19ZM13 12.67L15.59 10.09L17 11.5L12 16.5L7 11.5L8.41 10.09L11 12.67V3H13V12.67Z"/>
                        </svg>
                        Download Data
                    </button>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color mayor-gil-color"></span>
                        <span>Mayor Gil Fernando Ave</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color sumulong-color"></span>
                        <span>Sumulong Highway</span>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="vehicleBarChart"></canvas>
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

    <script src="{{ asset('js/vehicle-count.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>