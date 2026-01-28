// Mock data structure for footage archive
// In production, this would come from backend/database
const footageArchive = [
    {
        id: 1,
        filename: 'CCTV Angle.MOV',
        date: '2026-01-27',
        time: '14:30:00',
        thumbnail: '/videos/thumbnails/IMG_5839.jpg', // or extract from video
        videoPath: '/videos/CCTV Angle.mp4',
        location: 'Mayor Gil Fernando Ave & Sumulong Highway'
    },
    {
        id: 2,
        filename: 'D1 drone shot.mp4',
        date: '2026-01-27',
        time: '15:45:00',
        thumbnail: '/videos/thumbnails/IMG_5840.jpg',
        videoPath: '/videos/D1 drone shot (1).mp4',
        location: 'Mayor Gil Fernando Ave & Sumulong Highway'
    },
];

// State management
let currentVideo = null;
let filteredFootage = [...footageArchive];

// DOM Elements
const dateInput = document.getElementById('footage-date');
const searchBtn = document.getElementById('searchFootage');
const videoPlayer = document.getElementById('mainVideo');
const noVideoMessage = document.getElementById('noVideoMessage');
const videoInfo = document.getElementById('videoInfo');
const archiveGrid = document.getElementById('archiveGrid');
const noArchiveMessage = document.getElementById('noArchiveMessage');
const downloadBtn = document.getElementById('downloadBtn');
const videoDateSpan = document.getElementById('videoDate');
const videoTimeSpan = document.getElementById('videoTime');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.value = today;
    
    // Load initial footage
    loadFootage(today);
    
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
    if (!selectedDate) {
        alert('Please select a date');
        return;
    }
    
    loadFootage(selectedDate);
}

// Load footage for selected date
function loadFootage(date) {
    // Filter footage by date
    filteredFootage = footageArchive.filter(item => item.date === date);
    
    // Update archive grid
    renderArchiveGrid();
    
    // If footage exists, load the first one by default
    if (filteredFootage.length > 0) {
        loadVideo(filteredFootage[0]);
    } else {
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
    
    // Create thumbnail (use placeholder if image doesn't exist)
    const thumbnailSrc = item.thumbnail || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23e0e0e0" width="400" height="300"/%3E%3Ctext fill="%235a6c7d" font-family="Arial" font-size="24" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3EVideo Thumbnail%3C/text%3E%3C/svg%3E';
    
    div.innerHTML = `
        <img src="${thumbnailSrc}" alt="${item.filename}" class="archive-thumbnail" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23e0e0e0%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%235a6c7d%22 font-family=%22Arial%22 font-size=%2224%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EVideo%3C/text%3E%3C/svg%3E'">
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
    currentVideo = item;
    
    // Update video source
    videoPlayer.src = item.videoPath;
    videoPlayer.load();
    
    // Hide no video message
    noVideoMessage.classList.add('hidden');
    
    // Update video info
    videoDateSpan.textContent = formatDate(item.date);
    videoTimeSpan.textContent = formatTime(item.time);
    
    // Enable download button
    downloadBtn.disabled = false;
    
    // Play video (optional - remove if you don't want autoplay)
    // videoPlayer.play();
}

// Clear video player
function clearVideo() {
    currentVideo = null;
    videoPlayer.src = '';
    noVideoMessage.classList.remove('hidden');
    videoDateSpan.textContent = '-';
    videoTimeSpan.textContent = '-';
    downloadBtn.disabled = true;
}

// Handle video download
function handleDownload() {
    if (!currentVideo) return;
    
    // Create a temporary anchor element to trigger download
    const link = document.createElement('a');
    link.href = currentVideo.videoPath;
    link.download = currentVideo.filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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

// Utility: Generate thumbnail from video (optional advanced feature)
// This would require canvas manipulation - can be added later if needed
function generateThumbnail(videoPath, callback) {
    const video = document.createElement('video');
    video.src = videoPath;
    video.currentTime = 1; // Capture frame at 1 second
    
    video.addEventListener('loadeddata', function() {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const thumbnailUrl = canvas.toDataURL('image/jpeg');
        callback(thumbnailUrl);
    });
}