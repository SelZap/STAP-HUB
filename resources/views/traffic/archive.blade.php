<! DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Data Archive - STAP Hub</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP. ico') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/archive.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo" class="logo">
        
        <ul class="nav-links">
            <li><a href="/">Home</a></li>
            <li><a href="/#traffic-footage">Traffic Footage</a></li>
            <li><a href="/traffic/archive" class="active">Traffic Data Archive</a></li>
            <li><a href="/#vehicle-count">Vehicle Count</a></li>
            <li><a href="/#feedbacks">Feedbacks</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="archive-container">
        <section class="archive-header">
            <h1>Traffic Data Archive</h1>
            <p>View and analyze historical traffic data from your intersection</p>
        </section>

        <!-- Chart Section -->
        <section class="chart-section">
            <div class="chart-wrapper">
                <h2>Traffic Trends (Last 7 Days)</h2>
                <canvas id="trafficChart" width="400" height="100"></canvas>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="filters-section">
            <h2>Filters</h2>
            <form id="filterForm" class="filter-form" method="GET" action="{{ route('traffic.archive') }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] ??  '' }}">
                    </div>

                    <div class="filter-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                    </div>

                    <div class="filter-group">
                        <label for="road">Road</label>
                        <select id="road" name="road">
                            <option value="">All Roads</option>
                            @if(!empty($roads))
                                @foreach($roads as $r)
                                    <option value="{{ $r }}" @if((isset($filters['road']) && $filters['road'] === $r)) selected @endif>
                                        {{ $r }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="level_of_service">Level of Service</label>
                        <select id="level_of_service" name="level_of_service">
                            <option value="">All Levels</option>
                            <option value="light" @if((isset($filters['level_of_service']) && $filters['level_of_service'] === 'light')) selected @endif>Light</option>
                            <option value="medium" @if((isset($filters['level_of_service']) && $filters['level_of_service'] === 'medium')) selected @endif>Medium</option>
                            <option value="heavy" @if((isset($filters['level_of_service']) && $filters['level_of_service'] === 'heavy')) selected @endif>Heavy</option>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('traffic.archive') }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </section>

        <!-- Export Section -->
        <section class="export-section">
            <h2>Export Data</h2>
            <div class="export-buttons">
                <a href="{{ route('traffic.export-csv', request()->query()) }}" class="btn btn-export btn-csv">
                    📥 Download as CSV
                </a>
                <a href="{{ route('traffic. export-pdf', request()->query()) }}" class="btn btn-export btn-pdf">
                    📄 Download as PDF
                </a>
            </div>
        </section>

        <!-- Table Section -->
        <section class="table-section">
            <h2>Traffic Records</h2>
            
            @if($trafficData->count() > 0)
                <div class="table-wrapper">
                    <table class="data-table" id="trafficTable">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('traffic.archive', array_merge(request()->query(), ['sort_by' => 'date', 'sort_order' => request()->get('sort_order') === 'asc' ? 'desc' :  'asc'])) }}">
                                        Date
                                        @if(request()->get('sort_by') === 'date')
                                            <span class="sort-indicator">{{ request()->get('sort_order') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>Time</th>
                                <th>Road</th>
                                <th>
                                    <a href="{{ route('traffic.archive', array_merge(request()->query(), ['sort_by' => 'level_of_service', 'sort_order' => request()->get('sort_order') === 'asc' ? 'desc' :  'asc'])) }}">
                                        Level of Service
                                        @if(request()->get('sort_by') === 'level_of_service')
                                            <span class="sort-indicator">{{ request()->get('sort_order') === 'asc' ? '↑' :  '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>Weather</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trafficData as $record)
                                <tr class="los-{{ $record->level_of_service }}">
                                    <td>{{ $record->date->format('M d, Y') }}</td>
                                    <td>{{ $record->time }}</td>
                                    <td>{{ $record->road }}</td>
                                    <td>
                                        <span class="badge badge-{{ $record->level_of_service }}">
                                            {{ ucfirst($record->level_of_service) }}
                                        </span>
                                    </td>
                                    <td>{{ $record->weather }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $trafficData->links('pagination:: bootstrap-4') }}
                </div>
            @else
                <div class="no-data">
                    <p>No traffic data found.  Try adjusting your filters.</p>
                </div>
            @endif
        </section>
    </main>

    <script src="{{ asset('js/landing.js') }}"></script>
    <script src="{{ asset('js/archive.js') }}"></script>
    <script>
        // Initialize chart with data from controller
        const chartData = @json($chartData ??  []);
        initTrafficChart(chartData);
    </script>
</body>
</html>