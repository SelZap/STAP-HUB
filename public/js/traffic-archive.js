// public/js/traffic-archive.js
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentFilters = {
        search: '',
        status: 'all',
        los: 'all'
    };

    // Elements
    const searchInput = document.getElementById('searchInput');
    const filterBtn = document.getElementById('filterBtn');
    const filterPanel = document.getElementById('filterPanel');
    const filterCount = document.getElementById('filterCount');
    const statusFilter = document.getElementById('statusFilter');
    const losFilter = document.getElementById('losFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const loadingState = document.getElementById('loadingState');
    const tableContainer = document.getElementById('tableContainer');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const entriesInfo = document.getElementById('entriesInfo');
    const paginationButtons = document.getElementById('paginationButtons');
    const emptyState = document.getElementById('emptyState');

    // Initialize
    setTimeout(() => loadArchives(), 800);

    // Event Listeners
    searchInput.addEventListener('input', debounce(() => {
        currentFilters.search = searchInput.value;
        currentPage = 1;
        loadArchives();
    }, 500));

    filterBtn.addEventListener('click', () => {
        filterPanel.classList.toggle('hidden');
    });

    statusFilter.addEventListener('change', () => {
        currentFilters.status = statusFilter.value;
        currentPage = 1;
        updateFilterCount();
        loadArchives();
    });

    losFilter.addEventListener('change', () => {
        currentFilters.los = losFilter.value;
        currentPage = 1;
        updateFilterCount();
        loadArchives();
    });

    clearFiltersBtn.addEventListener('click', () => {
        searchInput.value = '';
        statusFilter.value = 'all';
        losFilter.value = 'all';
        currentFilters = { search: '', status: 'all', los: 'all' };
        currentPage = 1;
        updateFilterCount();
        loadArchives();
    });

    // Load archives from API
    async function loadArchives() {
        showLoading();

        try {
            const params = new URLSearchParams({
                page: currentPage,
                search: currentFilters.search,
                status: currentFilters.status,
                los: currentFilters.los
            });

            const response = await fetch(`/api/traffic-archives?${params}`);
            const data = await response.json();

            if (data.data && data.data.length > 0) {
                renderTable(data.data);
                renderPagination(data);
                showTable();
            } else {
                showEmpty();
            }
        } catch (error) {
            console.error('Error loading archives:', error);
            showEmpty();
        }
    }

    // Render table rows
    function renderTable(archives) {
        tableBody.innerHTML = archives.map(archive => `
            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="py-4 px-6 text-sm font-medium text-gray-900">${archive.archive_id}</td>
                <td class="py-4 px-6 text-sm text-gray-600">${formatDate(archive.date)}</td>
                <td class="py-4 px-6 text-sm text-gray-600">${formatTime(archive.time)}</td>
                <td class="py-4 px-6">
                    ${getLevelBadge(archive.gil_fernando_los)}
                </td>
                <td class="py-4 px-6">
                    ${getLevelBadge(archive.sumulong_los)}
                </td>
                <td class="py-4 px-6">
                    ${getStatusBadge(archive.status)}
                </td>
                <td class="py-4 px-6">
                    <button onclick="downloadArchive('${archive.archive_id}')" 
                            class="text-blue-600 hover:text-blue-800 transition-colors p-2 hover:bg-blue-50 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Render pagination
    function renderPagination(data) {
        const { current_page, last_page, from, to, total } = data;
        
        entriesInfo.textContent = `Showing ${from} to ${to} of ${total} entries`;
        
        let paginationHTML = `
            <button onclick="goToPage(${current_page - 1})" 
                    ${current_page === 1 ? 'disabled' : ''}
                    class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        `;

        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 1 && i <= current_page + 1)) {
                paginationHTML += `
                    <button onclick="goToPage(${i})" 
                            class="px-4 py-2 border rounded-lg transition-all ${
                                current_page === i
                                    ? 'bg-[#003366] text-white border-[#003366]'
                                    : 'border-gray-300 hover:bg-gray-50'
                            }">
                        ${i}
                    </button>
                `;
            } else if (i === current_page - 2 || i === current_page + 2) {
                paginationHTML += `<span class="px-2 text-gray-500">...</span>`;
            }
        }

        paginationHTML += `
            <button onclick="goToPage(${current_page + 1})" 
                    ${current_page === last_page ? 'disabled' : ''}
                    class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        `;

        paginationButtons.innerHTML = paginationHTML;
    }

    // Helper functions
    function getLevelBadge(level) {
        const colors = {
            'A': 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'B': 'bg-blue-100 text-blue-700 border-blue-200',
            'C': 'bg-amber-100 text-amber-700 border-amber-200',
            'D': 'bg-orange-100 text-orange-700 border-orange-200',
            'E': 'bg-rose-100 text-rose-700 border-rose-200',
            'F': 'bg-purple-100 text-purple-700 border-purple-200'
        };
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${colors[level] || 'bg-gray-100 text-gray-700 border-gray-200'}">Level ${level}</span>`;
    }

    function getStatusBadge(status) {
        const color = status === 'Completed'
            ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
            : 'bg-amber-100 text-amber-700 border-amber-200';
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${color}">${status}</span>`;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: '2-digit' });
    }

    function formatTime(timeString) {
        return timeString.substring(0, 5);
    }

    function updateFilterCount() {
        const count = [
            currentFilters.status !== 'all',
            currentFilters.los !== 'all'
        ].filter(Boolean).length;

        if (count > 0) {
            filterCount.textContent = count;
            filterCount.classList.remove('hidden');
        } else {
            filterCount.classList.add('hidden');
        }
    }

    function showLoading() {
        loadingState.classList.remove('hidden');
        tableContainer.classList.add('hidden');
        paginationContainer.classList.add('hidden');
        emptyState.classList.add('hidden');
    }

    function showTable() {
        loadingState.classList.add('hidden');
        tableContainer.classList.remove('hidden');
        paginationContainer.classList.remove('hidden');
        emptyState.classList.add('hidden');
    }

    function showEmpty() {
        loadingState.classList.add('hidden');
        tableContainer.classList.add('hidden');
        paginationContainer.classList.add('hidden');
        emptyState.classList.remove('hidden');
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Global functions
    window.goToPage = function(page) {
        currentPage = page;
        loadArchives();
    };

    window.downloadArchive = async function(archiveId) {
        try {
            const response = await fetch(`/api/traffic-archives/${archiveId}/download`);
            const archive = await response.json();
            
            const dataStr = JSON.stringify(archive, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `traffic-archive-${archiveId}.json`;
            link.click();
            URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Error downloading archive:', error);
            alert('Failed to download archive');
        }
    };
});