<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Footage - STAP Hub</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/traffic-footage.css') }}">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo" class="logo">
        
        <ul class="nav-links">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="{{ route('traffic.footage') }}" class="active">Traffic Footage</a></li>
            <li><a href="{{ route('traffic.archive') }}">Traffic Data Archive</a></li>
            <li><a href="{{ route('vehicle.count') }}">Vehicle Count</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">

        <!-- Video Player Section -->
        <div class="video-section">
            <div class="video-container">
                <!-- YouTube iframe will be inserted here by JavaScript -->
                
                <div class="no-video-message" id="noVideoMessage">
                    <div class="message-content">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M23 7l-7 5 7 5V7z"/>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                        </svg>
                        <p>No footage selected</p>
                        <p class="sub-message">Please select a date to view available footage</p>
                    </div>
                </div>
            </div>

            <div class="video-info" id="videoInfo">
                <div class="info-item">
                    <strong>Location:</strong>
                    <span id="videoLocation">Mayor Gil Fernando Ave & Sumulong Highway</span>
                </div>
                <div class="info-item">
                    <strong>Date:</strong>
                    <span id="videoDate">-</span>
                </div>
                <div class="info-item">
                    <strong>Time:</strong>
                    <span id="videoTime">-</span>
                </div>
            </div>

            <div class="video-actions">
                <button class="action-btn download-btn" id="downloadBtn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Open in YouTube
                </button>
            </div>
        </div>

        <!-- Date Selection -->
        <div class="controls-section">
            <div class="date-selector">
                <label for="footage-date">Select Date:</label>
                <input type="date" id="footage-date" name="footage-date">
            </div>
            
            <button class="search-btn" id="searchFootage">
                <span>Search Footage</span>
            </button>
        </div>

        <!-- Archive Grid -->
        <div class="archive-section">
            <h2>Available Footage Archive</h2>
            
            <div class="archive-grid" id="archiveGrid">
                <!-- Archive items will be populated here by JavaScript -->
            </div>

            <div class="no-archive-message" id="noArchiveMessage">
                <p>No footage available for the selected date</p>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/traffic-footage.js') }}"></script>
</body>
</html>