<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STAP Hub - Smart Traffic Automation Program</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo" class="logo">
        
        <ul class="nav-links">
            <li><a href="#" class="active">Home</a></li>
            <li><a href="#traffic-footage">Traffic Footage</a></li>
            <li><a href="#data-archive">Traffic Data Archive</a></li>
            <li><a href="#vehicle-count">Vehicle Count</a></li>
            <li><a href="#feedbacks">Feedbacks</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-logo">
                <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo">
            </div>
            <p>
                Welcome to STAP Hub, the website connected to the Smart Traffic Automation and Program hardware prototype. The website has a feed of the project intersection, traffic data archive, vehicle count data, and feedback page where users can suggest or report any problems.
            </p>
        </div>
        
        <div class="hero-image">
            <img src="{{ asset('images/traffic-illustration.png') }}" alt="Traffic Monitoring">
            <!-- If image not ready yet, use placeholder:
            <div class="image-placeholder">
                Add your traffic illustration here
            </div>
            -->
        </div>
    </section>

    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>