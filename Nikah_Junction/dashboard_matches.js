

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeFilters();
    initializeMatchActions();
    initializeProfilePreview();
    initializePagination();
    initializeSearchFunctionality();
});

// ==================== FILTER HANDLING ====================
function initializeFilters() {
    const filterForm = document.getElementById('matchFilterForm');
    
    if (!filterForm) return;
    
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });

    // Add event listeners to sync min/max age inputs
    const ageMinInput = document.getElementById('age_min');
    const ageMaxInput = document.getElementById('age_max');
    
    if (ageMinInput && ageMaxInput) {
        ageMinInput.addEventListener('change', function() {
            if (parseInt(ageMinInput.value) > parseInt(ageMaxInput.value) && ageMaxInput.value !== '') {
                ageMaxInput.value = ageMinInput.value;
            }
        });
        
        ageMaxInput.addEventListener('change', function() {
            if (parseInt(ageMaxInput.value) < parseInt(ageMinInput.value) && ageMinInput.value !== '') {
                ageMinInput.value = ageMaxInput.value;
            }
        });
    }
}

function applyFilters() {
    // Get filter values
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const gender = document.getElementById('gender').value;
    const ageMin = document.getElementById('age_min').value;
    const ageMax = document.getElementById('age_max').value;
    const location = document.getElementById('location').value.toLowerCase();
    const compatibility = document.getElementById('compatibility').value;
    const currentPage = document.getElementById('currentPage').value;
    
    // Show loading message
    showStatusMessage('Loading matches with filters...', 'info');
    
    // Prepare data for AJAX request
    const formData = new FormData();
    formData.append('action', 'filter_matches');
    formData.append('search', searchTerm);
    formData.append('gender', gender);
    formData.append('age_min', ageMin);
    formData.append('age_max', ageMax);
    formData.append('location', location);
    formData.append('compatibility', compatibility);
    formData.append('page', currentPage);
    
    // Send AJAX request to server
    fetch('ajax/match_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update matches table with filtered results
            updateMatchesTable(data.matches);
            // Update pagination if needed
            if (data.pagination) {
                updatePagination(data.pagination);
            }
            showStatusMessage(`Found ${data.matches.length} matches with current filters.`, 'success');
        } else {
            showStatusMessage(data.message || 'Error applying filters', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showStatusMessage('An error occurred while applying filters.', 'error');
    });
}

function updateMatchesTable(matches) {
    const matchesTable = document.getElementById('matchesTable');
    if (!matchesTable) return;
    
    // Keep the header row
    const headerRow = matchesTable.rows[0];
    matchesTable.innerHTML = '';
    matchesTable.appendChild(headerRow);
    
    if (matches.length === 0) {
        const row = matchesTable.insertRow();
        const cell = row.insertCell(0);
        cell.colSpan = 8;
        cell.className = 'text-center';
        cell.textContent = 'No matches found based on your preferences.';
        return;
    }
    
    // Add each match to the table
    matches.forEach(match => {
        const row = matchesTable.insertRow();
        row.dataset.id = match.id;
        
        // Profile picture cell
        const pictureCell = row.insertCell(0);
        const img = document.createElement('img');
        img.src = match.profile_picture ? `uploads/${match.profile_picture}` : 'assets/default-avatar.jpg';
        img.alt = 'Profile Picture';
        img.width = 50;
        pictureCell.appendChild(img);
        
        // Name cell
        row.insertCell(1).textContent = `${match.firstname} ${match.lastname}`;
        
        // Email cell
        row.insertCell(2).textContent = match.email;
        
        // Age cell
        row.insertCell(3).textContent = match.age;
        
        // Gender cell
        row.insertCell(4).textContent = match.gender;
        
        // Location cell
        row.insertCell(5).textContent = match.country;
        
        // Compatibility cell
        row.insertCell(6).textContent = `${match.compatibility_score}%`;
        
        // Actions cell
        const actionsCell = row.insertCell(7);
        actionsCell.className = 'table-actions';
        actionsCell.innerHTML = `
            <a href="#" class="action-view" data-id="${match.id}">View</a> | 
            <a href="#" class="action-accept" data-id="${match.id}">Accept</a> | 
            <a href="#" class="action-reject" data-id="${match.id}">Reject</a> | 
            <a href="#" class="action-message" data-id="${match.id}">Message</a>
        `;
    });
    
    // Re-attach event listeners
    initializeMatchActions();
}

// ==================== MATCH ACTIONS ====================
function initializeMatchActions() {
    // Add event listeners to all action links
    const viewLinks = document.querySelectorAll('.action-view');
    const acceptLinks = document.querySelectorAll('.action-accept');
    const rejectLinks = document.querySelectorAll('.action-reject');
    const messageLinks = document.querySelectorAll('.action-message');
    
    viewLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.id;
            viewProfile(userId);
        });
    });
    
    acceptLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.id;
            acceptMatch(userId, this.closest('tr'));
        });
    });
    
    rejectLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.id;
            rejectMatch(userId, this.closest('tr'));
        });
    });
    
    messageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.id;
            messageMatch(userId);
        });
    });
}

function viewProfile(userId) {
    showStatusMessage('Loading profile...', 'info');
    
    // Prepare data for AJAX request
    const formData = new FormData();
    formData.append('action', 'get_profile');
    formData.append('user_id', userId);
    
    // Send AJAX request to server
    fetch('ajax/match_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateProfilePreview(data.profile);
            showStatusMessage('Profile loaded successfully.', 'success');
        } else {
            showStatusMessage(data.message || 'Error loading profile', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showStatusMessage('An error occurred while loading the profile.', 'error');
    });
}

function updateProfilePreview(profile) {
    const previewSection = document.getElementById('profilePreview');
    
    if (!previewSection) return;
    
    // Create profile preview HTML
    const profileHTML = `
        <div class="profile-header">
            <img src="${profile.profile_picture ? 'uploads/' + profile.profile_picture : 'assets/default-avatar.jpg'}" 
                 alt="Profile Picture" class="preview-profile-pic">
            <h3>${profile.firstname} ${profile.lastname}</h3>
        </div>
        <div class="profile-details">
            <p><strong>Email:</strong> ${profile.email}</p>
            <p><strong>Age:</strong> ${profile.age}</p>
            <p><strong>Gender:</strong> ${profile.gender}</p>
            <p><strong>Location:</strong> ${profile.country}</p>
            <p><strong>Religion:</strong> ${profile.religion}</p>
            <p><strong>Education:</strong> ${profile.education || 'Not specified'}</p>
            <p><strong>Occupation:</strong> ${profile.occupation || 'Not specified'}</p>
            <p><strong>Compatibility Score:</strong> ${profile.compatibility_score}%</p>
        </div>
        <div class="profile-actions">
            <button class="btn btn-primary action-accept-btn" data-id="${profile.id}">Accept</button>
            <button class="btn action-reject-btn" data-id="${profile.id}">Reject</button>
            <button class="btn btn-accent action-message-btn" data-id="${profile.id}">Message</button>
        </div>
    `;
    
    // Update preview content
    previewSection.innerHTML = profileHTML;
    
    // Add event listeners to buttons
    const acceptBtn = previewSection.querySelector('.action-accept-btn');
    const rejectBtn = previewSection.querySelector('.action-reject-btn');
    const messageBtn = previewSection.querySelector('.action-message-btn');
    
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            acceptMatch(this.dataset.id);
        });
    }
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            rejectMatch(this.dataset.id);
        });
    }
    
    if (messageBtn) {
        messageBtn.addEventListener('click', function() {
            messageMatch(this.dataset.id);
        });
    }
    
    // Scroll to profile preview
    previewSection.scrollIntoView({ behavior: 'smooth' });
    
    // Add highlight effect
    previewSection.classList.add('highlight-preview');
    setTimeout(() => {
        previewSection.classList.remove('highlight-preview');
    }, 1000);
}

function acceptMatch(userId, row = null) {
    // Show confirmation dialog
    if (confirm('Are you sure you want to accept this match?')) {
        showStatusMessage('Processing match acceptance...', 'info');
        
        // Prepare data for AJAX request
        const formData = new FormData();
        formData.append('action', 'accept_match');
        formData.append('user_id', userId);
        formData.append('current_user_id', document.getElementById('userId').value);
        
        // Send AJAX request to server
        fetch('ajax/match_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatusMessage(data.message || 'Match accepted successfully!', 'success');
                
                // Update UI if row provided
                if (row) {
                    row.style.backgroundColor = '#d4edda';
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 2000);
                }
                
                // Refresh tables after a delay
                setTimeout(() => {
                    refreshMatchData();
                }, 1000);
            } else {
                showStatusMessage(data.message || 'Error accepting match', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showStatusMessage('An error occurred while accepting the match.', 'error');
        });
    }
}

function rejectMatch(userId, row = null) {
    // Show confirmation dialog
    if (confirm('Are you sure you want to reject this match?')) {
        showStatusMessage('Processing match rejection...', 'info');
        
        // Prepare data for AJAX request
        const formData = new FormData();
        formData.append('action', 'reject_match');
        formData.append('user_id', userId);
        formData.append('current_user_id', document.getElementById('userId').value);
        
        // Send AJAX request to server
        fetch('ajax/match_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatusMessage(data.message || 'Match rejected successfully!', 'success');
                
                // Update UI if row provided
                if (row) {
                    row.style.backgroundColor = '#f8d7da';
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                        row.classList.add('fade-out');
                        setTimeout(() => {
                            // Remove row after animation
                            row.remove();
                        }, 500);
                    }, 1000);
                }
                
                // Refresh tables after a delay
                setTimeout(() => {
                    refreshMatchData();
                }, 1500);
            } else {
                showStatusMessage(data.message || 'Error rejecting match', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showStatusMessage('An error occurred while rejecting the match.', 'error');
        });
    }
}

function messageMatch(userId) {
    // Redirect to messaging page with the selected user
    window.location.href = `dashboard_msgs.php?recipient=${userId}`;
}

// ==================== PROFILE PREVIEW HANDLING ====================
function initializeProfilePreview() {
    // This is already handled in the viewProfile and updateProfilePreview functions
    // Just making sure the profile preview section exists
    const previewSection = document.getElementById('profilePreview');
    if (!previewSection) {
        console.warn('Profile preview section not found in the DOM.');
    }
}

// ==================== PAGINATION HANDLING ====================
function initializePagination() {
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const currentPageInput = document.getElementById('currentPage');
    const totalPagesInput = document.getElementById('totalPages');
    
    if (!prevPageBtn || !nextPageBtn || !currentPageInput || !totalPagesInput) return;
    
    const currentPage = parseInt(currentPageInput.value);
    const totalPages = parseInt(totalPagesInput.value);
    
    // Disable previous button if on first page
    prevPageBtn.disabled = currentPage <= 1;
    
    // Disable next button if on last page
    nextPageBtn.disabled = currentPage >= totalPages;
    
    // Add event listeners
    prevPageBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            navigateToPage(currentPage - 1);
        }
    });
    
    nextPageBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage < totalPages) {
            navigateToPage(currentPage + 1);
        }
    });
}

function navigateToPage(pageNumber) {
    // Get current URL and parameters
    const url = new URL(window.location.href);
    
    // Update or add page parameter
    url.searchParams.set('page', pageNumber);
    
    // Get current filter values and add them to URL if they exist
    const searchTerm = document.getElementById('searchInput').value;
    const gender = document.getElementById('gender').value;
    const ageMin = document.getElementById('age_min').value;
    const ageMax = document.getElementById('age_max').value;
    const location = document.getElementById('location').value;
    const compatibility = document.getElementById('compatibility').value;
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    if (gender && gender !== 'all') url.searchParams.set('gender', gender);
    if (ageMin) url.searchParams.set('age_min', ageMin);
    if (ageMax) url.searchParams.set('age_max', ageMax);
    if (location) url.searchParams.set('location', location);
    if (compatibility) url.searchParams.set('compatibility', compatibility);
    
    // Navigate to new URL
    window.location.href = url.toString();
}

function updatePagination(paginationData) {
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const currentPageInput = document.getElementById('currentPage');
    const totalPagesInput = document.getElementById('totalPages');
    const paginationSpan = document.querySelector('.pagination span');
    
    if (!paginationData || !prevPageBtn || !nextPageBtn || !currentPageInput || !totalPagesInput || !paginationSpan) return;
    
    // Update current and total pages
    currentPageInput.value = paginationData.current_page;
    totalPagesInput.value = paginationData.total_pages;
    
    // Update pagination text
    paginationSpan.textContent = `Page ${paginationData.current_page} of ${paginationData.total_pages}`;
    
    // Update button states
    prevPageBtn.disabled = paginationData.current_page <= 1;
    nextPageBtn.disabled = paginationData.current_page >= paginationData.total_pages;
}

// ==================== SEARCH FUNCTIONALITY ====================
function initializeSearchFunctionality() {
    const searchInput = document.getElementById('searchInput');
    
    if (!searchInput) return;
    
    // Add debounce to search input to prevent too many requests
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            // Only auto-search if there are 3 or more characters
            if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                applyFilters();
            }
        }, 600); // Wait 600ms after user stops typing
    });
}

// ==================== UTILITY FUNCTIONS ====================
function showStatusMessage(message, type = 'info') {
    // Create status message element if it doesn't exist
    let statusElement = document.getElementById('statusMessage');
    
    if (!statusElement) {
        statusElement = document.createElement('div');
        statusElement.id = 'statusMessage';
        statusElement.className = 'status-message';
        document.body.appendChild(statusElement);
    }
    
    // Set message and class based on type
    statusElement.textContent = message;
    statusElement.className = `status-message status-${type}`;
    
    // Show the message
    statusElement.style.display = 'block';
    statusElement.style.opacity = '1';
    
    // Hide after a delay
    setTimeout(() => {
        statusElement.style.opacity = '0';
        setTimeout(() => {
            statusElement.style.display = 'none';
        }, 500); // Match transition duration
    }, 5000); // 5 seconds display time
}

function refreshMatchData() {
    // Get current page number
    const currentPage = document.getElementById('currentPage').value;
    
    // Prepare data for AJAX request
    const formData = new FormData();
    formData.append('action', 'refresh_matches');
    formData.append('page', currentPage);
    
    // Send AJAX request to server
    fetch('ajax/match_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update matches table
            if (data.matches) {
                updateMatchesTable(data.matches);
            }
            
            // Update pending requests table
            if (data.pending_requests) {
                updatePendingTable(data.pending_requests);
            }
            
            // Update pagination if needed
            if (data.pagination) {
                updatePagination(data.pagination);
            }
        } else {
            console.error('Error refreshing match data:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updatePendingTable(pendingRequests) {
    const pendingTable = document.getElementById('pendingTable');
    if (!pendingTable) return;
    
    // Keep the header row
    const headerRow = pendingTable.rows[0];
    pendingTable.innerHTML = '';
    pendingTable.appendChild(headerRow);
    
    if (pendingRequests.length === 0) {
        const row = pendingTable.insertRow();
        const cell = row.insertCell(0);
        cell.colSpan = 8;
        cell.className = 'text-center';
        cell.textContent = 'No pending match requests.';
        return;
    }
    
    // Add each pending request to the table
    pendingRequests.forEach(request => {
        const row = pendingTable.insertRow();
        row.dataset.id = request.id;
        
        // Profile picture cell
        const pictureCell = row.insertCell(0);
        const img = document.createElement('img');
        img.src = request.profile_picture ? `uploads/${request.profile_picture}` : 'assets/default-avatar.jpg';
        img.alt = 'Profile Picture';
        img.width = 50;
        pictureCell.appendChild(img);
        
        // Name cell
        row.insertCell(1).textContent = `${request.firstname} ${request.lastname}`;
        
        // Email cell
        row.insertCell(2).textContent = request.email;
        
        // Age cell
        row.insertCell(3).textContent = request.age;
        
        // Gender cell
        row.insertCell(4).textContent = request.gender;
        
        // Location cell
        row.insertCell(5).textContent = request.country;
        
        // Status cell
        row.insertCell(6).textContent = 'Awaiting Response';
        
        // Actions cell
        const actionsCell = row.insertCell(7);
        actionsCell.className = 'table-actions';
        actionsCell.innerHTML = `
            <a href="#" class="action-view" data-id="${request.id}">View</a> | 
            <a href="#" class="action-accept" data-id="${request.id}">Accept</a> | 
            <a href="#" class="action-reject" data-id="${request.id}">Reject</a>
        `;
    });
    
    initializeMatchActions();
}
