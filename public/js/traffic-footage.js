// Mock data structure for footage archive
// Using YouTube embed for video playback
const footageArchive = [
    {
        id: 1,
        filename: 'Traffic Intersection - CCTV Footage',
        date: '2026-01-31',
        time: '14:30:00',
        thumbnail: 'https://img.youtube.com/vi/WK5_vUGQIv0/maxresdefault.jpg',
        videoPath: 'https://www.youtube.com/embed/WK5_vUGQIv0',
        location: 'Mayor Gil Fernando Ave & Sumulong Highway'
    },
    {
        id: 2,
        filename: 'Traffic Intersection - Drone Footage',
        date: '2026-01-31',  
        time: '10:15:00',
        thumbnail: 'https://img.youtube.com/vi/GMHGfoy9yqY/maxresdefault.jpg',  
        videoPath: 'https://www.youtube.com/embed/GMHGfoy9yqY',  
        location: 'Mayor Gil Fernando Ave & Sumulong Highway'
    },
];

// State management
let currentVideo = null;
let filteredFootage = [...footageArchive];

// DOM Elements
const dateInput = document.getElementById('footage-date');
const searchBtn = document.getElementById('searchFootage');
const videoContainer = document.querySelector('.video-container');
const noVideoMessage = document.getElementById('noVideoMessage');
const videoInfo = document.getElementById('videoInfo');
const archiveGrid = document.getElementById('archiveGrid');
const noArchiveMessage = document.getElementById('noArchiveMessage');
const downloadBtn = document.getElementById('downloadBtn');
const videoDateSpan = document.getElementById('videoDate');
const videoTimeSpan = document.getElementById('videoTime');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Traffic Footage page loaded');
    console.log('Available footage dates:', footageArchive.map(f => f.date));
    
    // Set default date to January 31, 2026 (where we have footage)
    dateInput.value = '2026-01-31';
    
    console.log('Initial date set to:', dateInput.value);
    
    // Load initial footage
    loadFootage(dateInput.value);
    
    // Event listeners
    searchBtn.addEventListener('click', handleSearch);
    dateInput.addEventListener('change', handleSearch);
    downloadBtn.addEventListener('click', handleDownload);
    
    // Keyboard shortcut for search (Enter key)
    dateInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
});

// Handle search functionality
function handleSearch() {
    const selectedDate = dateInput.value;
    console.log('Search clicked for date:', selectedDate);
    
    if (!selectedDate) {
        alert('Please select a date');
        return;
    }
    
    loadFootage(selectedDate);
}

// Load footage for selected date
function loadFootage(date) {
    console.log('Loading footage for date:', date);
    console.log('Available footage:', footageArchive);
    
    // Filter footage by date
    filteredFootage = footageArchive.filter(item => item.date === date);
    
    console.log('Filtered footage count:', filteredFootage.length);
    console.log('Filtered footage:', filteredFootage);
    
    // Update archive grid
    renderArchiveGrid();
    
    // If footage exists, load the first one by default
    if (filteredFootage.length > 0) {
        console.log('Loading first video');
        loadVideo(filteredFootage[0]);
    } else {
        console.log('No footage found for this date');
        clearVideo();
    }
}

// Render archive grid
function renderArchiveGrid() {
    archiveGrid.innerHTML = '';
    
    if (filteredFootage.length === 0) {
        noArchiveMessage.classList.add('show');
        return;
    }
    
    noArchiveMessage.classList.remove('show');
    
    filteredFootage.forEach((item, index) => {
        const archiveItem = createArchiveItem(item, index === 0);
        archiveGrid.appendChild(archiveItem);
    });
}

// Create archive item element
function createArchiveItem(item, isActive = false) {
    const div = document.createElement('div');
    div.className = `archive-item ${isActive ? 'active' : ''}`;
    div.dataset.id = item.id;
    
    div.innerHTML = `
        <img src="${item.thumbnail}" alt="${item.filename}" class="archive-thumbnail" onerror="this.src='https://img.youtube.com/vi/WK5_vUGQIv0/default.jpg'">
        <div class="archive-info">
            <h3>${item.filename}</h3>
            <div class="timestamp">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                ${formatTime(item.time)}
            </div>
        </div>
    `;
    
    div.addEventListener('click', () => {
        console.log('Archive item clicked:', item);
        
        // Remove active class from all items
        document.querySelectorAll('.archive-item').forEach(el => {
            el.classList.remove('active');
        });
        
        // Add active class to clicked item
        div.classList.add('active');
        
        // Load the video
        loadVideo(item);
    });
    
    return div;
}

// Load video into player
function loadVideo(item) {
    console.log('Loading video:', item);
    currentVideo = item;
    
    // Remove existing video/iframe
    const existingVideo = videoContainer.querySelector('video');
    const existingIframe = videoContainer.querySelector('iframe');
    if (existingVideo) {
        console.log('Removing existing video element');
        existingVideo.remove();
    }
    if (existingIframe) {
        console.log('Removing existing iframe');
        existingIframe.remove();
    }
    
    // Create YouTube iframe
    const iframe = document.createElement('iframe');
    iframe.src = item.videoPath;
    iframe.width = "100%";
    iframe.height = "600";
    iframe.frameBorder = "0";
    iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
    iframe.allowFullscreen = true;
    iframe.style.maxHeight = "600px";
    iframe.style.borderRadius = "8px";
    
    console.log('Created iframe with src:', iframe.src);
    
    // Insert iframe before the no-video message
    videoContainer.insertBefore(iframe, noVideoMessage);
    
    // Hide no video message
    noVideoMessage.classList.add('hidden');
    
    // Update video info
    videoDateSpan.textContent = formatDate(item.date);
    videoTimeSpan.textContent = formatTime(item.time);
    
    // Enable download button
    downloadBtn.disabled = false;
    
    console.log('Video loaded successfully');
}

// Clear video player
function clearVideo() {
    console.log('Clearing video player');
    currentVideo = null;
    
    // Remove iframe if exists
    const existingIframe = videoContainer.querySelector('iframe');
    if (existingIframe) existingIframe.remove();
    
    noVideoMessage.classList.remove('hidden');
    videoDateSpan.textContent = '-';
    videoTimeSpan.textContent = '-';
    downloadBtn.disabled = true;
}

// Handle video download
function handleDownload() {
    if (!currentVideo) return;
    
    // Open YouTube video in new tab (since we can't directly download YouTube videos)
    const youtubeUrl = currentVideo.videoPath.replace('/embed/', '/watch?v=');
    window.open(youtubeUrl, '_blank');
    
    // You can also show a message to the user
    alert('Opening YouTube video. You can use YouTube\'s download options or third-party tools to download the video.');
}

// Format date (YYYY-MM-DD to readable format)
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Format time (HH:MM:SS to readable format)
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}