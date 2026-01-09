<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Traffic Data Archive - STAP Hub</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/traffic-archive.js'])
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-8 py-4">
            <div class="flex items-center justify-between">
                <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo" class="h-16">
                
                <ul class="flex gap-8 text-gray-700">
                    <li><a href="{{ url('/') }}" class="hover:text-red-600 transition">Home</a></li>
                    <li><a href="{{ route('traffic.footage') }}" class="hover:text-red-600 transition">Traffic Footage</a></li>
                    <li><a href="{{ route('traffic.archive') }}" class="text-red-600 font-semibold">Traffic Data Archive</a></li>
                    <li><a href="{{ route('vehicle.count') }}" class="hover:text-red-600 transition">Vehicle Count</a></li>
                    <li><a href="{{ route('feedbacks') }}" class="hover:text-red-600 transition">Feedbacks</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
                    <h1 class="text-3xl font-bold text-white">Traffic Data Archive</h1>
                    <p class="text-blue-100 mt-1">View and download historical traffic data records</p>
                </div>

                <div class="p-8">
                    <!-- Search and Filters -->
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="relative flex-1 max-w-md">
                            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Search by ID, date, or time..."
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            />
                        </div>
                        
                        <button id="filterBtn" class="flex items-center gap-2 px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filters
                            <span id="filterCount" class="hidden ml-2 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                        </button>
                    </div>

                    <!-- Filter Panel -->
                    <div id="filterPanel" class="hidden mb-6 p-6 bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl border border-gray-200 animate-slideDown">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="all">All Status</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Level of Service</label>
                                <select id="losFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="all">All Levels</option>
                                    <option value="A">Level A</option>
                                    <option value="B">Level B</option>
                                    <option value="C">Level C</option>
                                    <option value="D">Level D</option>
                                    <option value="E">Level E</option>
                                    <option value="F">Level F</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end gap-2">
                                <button id="clearFilters" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                                    Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div id="loadingState" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
                        <p class="mt-4 text-gray-600 font-medium">Loading archives...</p>
                    </div>

                    <!-- Table -->
                    <div id="tableContainer" class="hidden overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-slate-50">
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Archive ID</th>
                                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Date</th>
                                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Time</th>
                                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Mayor Gil Fernando Ave</th>
                                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Sumulong Highway</th>
                                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Status</th>
                                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer" class="hidden flex items-center justify-between mt-6">
                        <div id="entriesInfo" class="text-sm text-gray-600"></div>
                        <div id="paginationButtons" class="flex items-center gap-2"></div>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="hidden text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No archives found</h3>
                        <p class="mt-2 text-gray-600">Try adjusting your search or filter criteria</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/traffic-archive.js') }}"></script>
</body>
</html>