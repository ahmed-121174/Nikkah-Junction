(function() {
    'use strict';
    
    // Cache DOM elements for better performance
    const dashboardSection = document.querySelectorAll('.dashboard-section');
    const statsCards = document.querySelectorAll('.stat-card');
    const activityTable = document.querySelector('.data-table');
    const quickActions = document.querySelector('.action-buttons');
    const searchResultsContainer = document.getElementById('searchResultsContainer');
    const searchForm = document.querySelector('.dashboard-search');
    const searchInput = document.getElementById('searchInput');
    const exportBtn = document.getElementById('exportBtn');
    
    // Configuration object for animation timing
    const config = {
        animationDelay: 150,
        refreshInterval: 60000, // 1 minute
        chartColors: {
            primary: '#8B4513',
            secondary: '#E6A847',
            accent: '#228B22',
            light: '#FFF5E1'
        }
    };
    
    // Initialize the dashboard
    function initDashboard() {
        animateContent();
        setupEventListeners();
        startDashboardTimer();
        initNotifications();
        fetchAndRenderCharts();
        initializeChatbot();
        
        // Hide search results container initially
        if (searchResultsContainer) {
            searchResultsContainer.style.display = 'none';
        }
    }
    
    // Progressive animation for dashboard sections
    function animateContent() {
        dashboardSection.forEach((section, index) => {
            setTimeout(() => {
                section.classList.add('animate-fadeIn');
            }, index * config.animationDelay);
        });
        
        // Counter animation for stats
        statsCards.forEach(card => {
            const statValue = card.querySelector('p');
            if (statValue) {
                const targetValue = parseInt(statValue.textContent);
                if (!isNaN(targetValue)) {
                    animateCounter(statValue, 0, targetValue);
                }
            }
        });
    }
    
    // Animate counter from start to end value
    function animateCounter(element, start, end) {
        let current = start;
        const increment = Math.max(1, Math.ceil(end / 30)); // Divide animation into 30 steps
        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                clearInterval(timer);
                element.textContent = end;
            } else {
                element.textContent = current;
            }
        }, 40);
    }
    
    // Set up event listeners for various dashboard interactions
    function setupEventListeners() {
        // Add hover state to table rows
        if (activityTable) {
            const rows = activityTable.querySelectorAll('tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', () => {
                    row.style.backgroundColor = '#f0f0f0';
                });
                
                row.addEventListener('mouseleave', () => {
                    row.style.backgroundColor = '';
                });
            });
        }
        
        // Set up quick action buttons
        if (quickActions) {
            const buttons = quickActions.querySelectorAll('.btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', handleActionClick);
            });
        }
        
        // Setup search form event listener
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const searchQuery = searchInput.value.toLowerCase();
                
                if (searchQuery.length > 2) {
                    searchUsers(searchQuery);
                }
            });
        }
        
        // Setup live search on keyup
        if (searchInput) {
            searchInput.addEventListener('keyup', (e) => {
                const searchQuery = e.target.value.toLowerCase();
                
                // Only search if at least 3 characters have been typed
                if (searchQuery.length >= 3) {
                    searchUsers(searchQuery);
                } else if (searchQuery.length === 0) {
                    // Hide results when search is cleared
                    if (searchResultsContainer) {
                        searchResultsContainer.style.display = 'none';
                    }
                }
            });
        }
        
        // Setup export button
        if (exportBtn) {
            exportBtn.addEventListener('click', exportTableData);
        }
        
        // Add dark mode toggle button and functionality
        addDarkModeToggle();
    }

    // Add dark mode toggle
    function addDarkModeToggle() {
        const darkModeToggle = document.createElement('button');
        darkModeToggle.className = 'dark-mode-toggle';
        darkModeToggle.innerHTML = '🌙';
        darkModeToggle.title = 'Toggle Dark Mode';
        document.body.appendChild(darkModeToggle);
        
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            darkModeToggle.innerHTML = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
            
            // Save preference to server
            const darkModeEnabled = document.body.classList.contains('dark-mode');
            
            fetch('save_preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `darkMode=${darkModeEnabled ? 1 : 0}`
            })
            .catch(error => {
                console.error('Error saving dark mode preference:', error);
            });
            
            // Also save to localStorage as fallback
            localStorage.setItem('darkMode', darkModeEnabled);
        });
        
        // Check for saved preference in localStorage as fallback
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '☀️';
        }
    }
    
    // Handle clicks on quick action buttons
    function handleActionClick(event) {
        // Add ripple effect
        const btn = event.currentTarget;
        const ripple = document.createElement('span');
        const rect = btn.getBoundingClientRect();
        
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = `${Math.max(rect.width, rect.height)}px`;
        ripple.style.left = `${event.clientX - rect.left - ripple.offsetWidth / 2}px`;
        ripple.style.top = `${event.clientY - rect.top - ripple.offsetHeight / 2}px`;
        
        btn.appendChild(ripple);
        
        // Remove ripple after animation completes
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
    
    // Start dashboard timer for real-time updates
    function startDashboardTimer() {
        const updateTimeDisplay = () => {
            const now = new Date();
            const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const dateString = now.toLocaleDateString([], { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // Use existing time display if it exists
            let timeDisplay = document.querySelector('.dashboard-time');
            if (timeDisplay) {
                timeDisplay.innerHTML = `<p>Current Time: <strong>${timeString}</strong> | ${dateString}</p>`;
            }
        };
        
        // Update immediately
        updateTimeDisplay();
        
        // Then update every minute
        setInterval(updateTimeDisplay, 60000);
        
        // Refresh dashboard data periodically
        setInterval(() => {
            refreshDashboardData();
        }, config.refreshInterval);
    }
    
    // Refresh dashboard data from server
    function refreshDashboardData() {
        // Add visual indication that refresh is happening
        statsCards.forEach(card => {
            card.classList.add('updating');
        });
        
        // Make AJAX request to refresh data
        fetch('dashboard.php?refresh=true')
            .then(response => response.json())
            .then(data => {
                // Update statistics
                if (data.total_users) {
                    updateStatCard('Total Registered Users', data.total_users, `New users this week: ${data.new_users}`);
                }
                
                if (data.total_matches) {
                    updateStatCard('New Matches', data.total_matches, `Pending Requests: ${data.pending_requests}`);
                }
                
                if (data.total_messages) {
                    updateStatCard('Messages', data.total_messages, `Unread Messages: ${data.unread_messages}`);
                }
                
                if (data.pending_approvals) {
                    updateStatCard('Pending Approvals', data.pending_approvals, `Profiles Under Review: ${data.profiles_review}`);
                }
                
                // Update activities table if new data is available
                if (data.activities && data.activities.length > 0) {
                    updateActivitiesTable(data.activities);
                }
                
                // Remove updating class from all cards
                statsCards.forEach(card => {
                    card.classList.remove('updating');
                });
                
                console.log('Dashboard data refreshed at:', new Date().toLocaleTimeString());
            })
            .catch(error => {
                console.error('Error refreshing dashboard data:', error);
                // Remove updating class even if there's an error
                statsCards.forEach(card => {
                    card.classList.remove('updating');
                });
            });
    }
    
    // Update a specific stat card with new data
    function updateStatCard(title, value, subtitle) {
        statsCards.forEach(card => {
            const cardTitle = card.querySelector('h3');
            if (cardTitle && cardTitle.textContent === title) {
                const valueElem = card.querySelector('p');
                const subtitleElem = card.querySelector('small');
                
                if (valueElem) {
                    const oldValue = parseInt(valueElem.textContent);
                    if (oldValue !== value) {
                        animateCounter(valueElem, oldValue, value);
                    }
                }
                
                if (subtitleElem) {
                    subtitleElem.textContent = subtitle;
                }
            }
        });
    }
    
    // Update activities table with new data
    function updateActivitiesTable(activities) {
        if (!activityTable) return;
        
        // Get all rows except the header
        const rows = activityTable.querySelectorAll('tr:not(:first-child)');
        
        // Remove all existing data rows
        rows.forEach(row => row.remove());
        
        // Add new rows
        activities.forEach(activity => {
            const newRow = document.createElement('tr');
            
            newRow.innerHTML = `
                <td>${activity.user}</td>
                <td>${activity.activity}</td>
                <td>${activity.date}</td>
                <td>${activity.details}</td>
            `;
            
            activityTable.appendChild(newRow);
        });
        
        // Re-attach event listeners
        setupEventListeners();
    }
    
    // Notification system
    function initNotifications() {
        // Make AJAX request to get notifications
        fetch('dashboard.php?notifications=true')
            .then(response => response.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    createNotificationPanel(data.notifications);
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    }
    
    // Create notification panel
    function createNotificationPanel(notifications) {
        const notificationCount = notifications.length;
        
        if (notificationCount > 0) {
            const notificationArea = document.createElement('div');
            notificationArea.className = 'notification-area';
            notificationArea.innerHTML = `
                <div class="notification-badge">${notificationCount}</div>
                <div class="notification-panel">
                    <h3>Notifications</h3>
                    <ul id="notification-list"></ul>
                </div>
            `;
            
            document.body.appendChild(notificationArea);
            
            const notificationList = document.getElementById('notification-list');
            
            // Add notifications
            notifications.forEach(notification => {
                const li = document.createElement('li');
                li.textContent = notification.message;
                li.dataset.id = notification.id;
                
                // Add mark as read button
                const markReadBtn = document.createElement('button');
                markReadBtn.className = 'mark-read-btn';
                markReadBtn.textContent = 'Mark as Read';
                markReadBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    markNotificationAsRead(notification.id, li);
                });
                
                li.appendChild(markReadBtn);
                notificationList.appendChild(li);
            });
            
            // Toggle notification panel on click
            const badge = document.querySelector('.notification-badge');
            const panel = document.querySelector('.notification-panel');
            
            badge.addEventListener('click', () => {
                panel.classList.toggle('show');
            });
            
            // Close when clicking outside
            document.addEventListener('click', (event) => {
                if (!notificationArea.contains(event.target)) {
                    panel.classList.remove('show');
                }
            });
        }
    }
    
    // Mark notification as read
    function markNotificationAsRead(notificationId, element) {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove notification from list
                element.remove();
                
                // Update notification count
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    const currentCount = parseInt(badge.textContent);
                    badge.textContent = currentCount - 1;
                    
                    if (currentCount - 1 <= 0) {
                        document.querySelector('.notification-area').remove();
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    
    // Fetch and render charts
    function fetchAndRenderCharts() {
        // Fetch chart data from server
        fetch('dashboard.php?chart_data=true')
            .then(response => response.json())
            .then(data => {
                // Draw chart with the data
                const canvas = document.getElementById('userActivityChart');
                if (canvas && canvas.getContext) {
                    const ctx = canvas.getContext('2d');
                    drawActivityChart(ctx, data.labels, data.values);
                }
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
                
                // Draw fallback chart with default data
                const canvas = document.getElementById('userActivityChart');
                if (canvas && canvas.getContext) {
                    const ctx = canvas.getContext('2d');
                    drawActivityChart(ctx);
                }
            });
    }
    
    // Draw a simple bar chart
    function drawActivityChart(ctx, labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], data = [65, 59, 80, 81, 56, 55, 40]) {
        const maxValue = Math.max(...data);
        const canvasHeight = ctx.canvas.height;
        const canvasWidth = ctx.canvas.width;
        const barWidth = (canvasWidth - 40) / data.length;
        
        // Clear canvas
        ctx.clearRect(0, 0, canvasWidth, canvasHeight);
        
        // Draw title
        ctx.fillStyle = config.chartColors.primary;
        ctx.font = '16px Arial';
        ctx.fillText('Weekly User Activity', 20, 20);
        
        // Draw bars
        data.forEach((value, index) => {
            const barHeight = (value / maxValue) * (canvasHeight - 60);
            const x = 20 + (index * barWidth);
            const y = canvasHeight - barHeight - 30;
            
            // Draw bar
            ctx.fillStyle = config.chartColors.secondary;
            ctx.fillRect(x, y, barWidth - 5, barHeight);
            
            // Draw label
            ctx.fillStyle = config.chartColors.primary;
            ctx.font = '12px Arial';
            ctx.fillText(labels[index], x + barWidth/2 - 10, canvasHeight - 10);
            
            // Draw value
            ctx.fillText(value.toString(), x + barWidth/2 - 10, y - 5);
        });
    }
    
    // Search users via AJAX
    function searchUsers(query) {
        // Show loading indicator
        if (searchResultsContainer) {
            searchResultsContainer.style.display = 'block';
            document.getElementById('searchResults').innerHTML = '<p>Searching...</p>';
        }
        
        // Make AJAX request to search users
        fetch(`dashboard.php?search=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data, query);
            })
            .catch(error => {
                console.error('Error searching users:', error);
                document.getElementById('searchResults').innerHTML = '<p>Error searching users. Please try again.</p>';
            });
    }
    
    // Display search results
    function displaySearchResults(results, query) {
        const searchResultsDiv = document.getElementById('searchResults');
        
        if (!searchResultsDiv) return;
        
        if (results.length === 0) {
            searchResultsDiv.innerHTML = `<p>No users found matching "${query}"</p>`;
            return;
        }
        
        let html = '<ul class="user-search-results">';
        
        results.forEach(user => {
            html += `
                <li>
                    <div class="user-search-item">
                        <img src="${user.profile_pic || 'default-avatar.png'}" alt="${user.full_name}" class="user-avatar">
                        <div class="user-info">
                            <h4>${user.full_name}</h4>
                            <p>${user.age} years, ${user.city}</p>
                        </div>
                        <a href="view_profile.php?id=${user.user_id}" class="btn btn-small">View Profile</a>
                    </div>
                </li>
            `;
        });
        
        html += '</ul>';
        searchResultsDiv.innerHTML = html;
    }
    
    // Export table data to CSV
    function exportTableData() {
        if (!activityTable) return;
        
        const rows = activityTable.querySelectorAll('tr');
        let csvContent = "data:text/csv;charset=utf-8,";
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const rowData = Array.from(cells).map(cell => cell.textContent);
            csvContent += rowData.join(',') + '\r\n';
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'nikkah_junction_data_' + new Date().toISOString().split('T')[0] + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
 /**
 * AI Chatbot implementation with dynamic responses from API
 */
function initializeChatbot() {
    // Create chatbot button
    const chatbotButton = document.createElement('div');
    chatbotButton.classList.add('chatbot-button');
    chatbotButton.innerHTML = `
        <div class="chatbot-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
    `;
    
    // Create chatbot container
    const chatbotContainer = document.createElement('div');
    chatbotContainer.classList.add('chatbot-container', 'hidden');
    chatbotContainer.innerHTML = `
        <div class="chatbot-header">
            <h3>Virtual AI Assistant</h3>
            <button class="close-chatbot">×</button>
        </div>
        <div class="chatbot-messages" id="messages">
            <div class="message-received">
                <small>Virtual Assistant</small>
                <p>I'm Ahmed Ali. How can I assist you today? If you have any questions, please feel free to share them with me.</p>
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="inputPrompt" placeholder="Type your message here...">
            <button class="send-message" id="sendPromptBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    `;
    
    // Add chatbot to the body
    document.body.appendChild(chatbotButton);
    document.body.appendChild(chatbotContainer);
    
    // Add event listeners
    chatbotButton.addEventListener('click', function() {
        chatbotContainer.classList.remove('hidden');
    });

    const closeButton = chatbotContainer.querySelector('.close-chatbot');
    closeButton.addEventListener('click', function() {
        chatbotContainer.classList.add('hidden');
    });
    
    // Chatbot functionality
    const inputPrompt = document.getElementById('inputPrompt');
    const sendPromptBtn = document.getElementById('sendPromptBtn');
    const messages = document.getElementById('messages');
    
    // Function to get response from API
    function GetResponse() {
        // Prevent empty messages
        const promptValue = inputPrompt.value.trim();
        if (promptValue === '') return;
        
        // Update send button to show loading state
        sendPromptBtn.innerHTML = `
            <span class="spinner" role="status">
                <span class="visually-hidden">Loading...</span>
            </span>
        `;
        
        // Add user message to chat
        messages.innerHTML += `
            <div class="message-sent">
                <small>You</small>
                <p>${promptValue}</p>
            </div>
        `;
        
        // Auto-scroll to the latest message
        messages.scrollTop = messages.scrollHeight;
        
        // Create typing indicator
        const typingIndicator = document.createElement('div');
        typingIndicator.classList.add('typing-indicator');
        typingIndicator.innerHTML = '<span></span><span></span><span></span>';
        messages.appendChild(typingIndicator);
        
        // Define the role for the AI
        const roleValue = "You act as Consltant whose name is Ahmed Ali";
        
        // Make API request
        fetch(`http://localhost/js/api.php?role=${encodeURIComponent(roleValue)}&prompt=${encodeURIComponent(promptValue)}`)
            .then(res => {
                if (res.ok) {
                    return res.json();
                } else {
                    throw new Error('API request failed');
                }
            })
            .then(data => {
                // Remove typing indicator
                messages.removeChild(typingIndicator);
                
                // Add AI response to chat
                messages.innerHTML += `
                    <div class="message-received">
                        <small>Virtual Assistant</small>
                        <p>${data.choices[0].message.content}</p>
                    </div>
                `;
                
                // Clear input and reset button
                inputPrompt.value = "";
                sendPromptBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                `;
                
                // Auto-scroll to the latest message
                messages.scrollTop = messages.scrollHeight;
            })
            .catch(error => {
                // Remove typing indicator
                if (typingIndicator.parentNode === messages) {
                    messages.removeChild(typingIndicator);
                }
                
                // Add error message
                messages.innerHTML += `
                    <div class="message-received">
                        <small>Virtual Assistant</small>
                        <p>I'm sorry, I'm having trouble connecting right now. Please try again later.</p>
                    </div>
                `;
                
                // Reset button
                sendPromptBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                `;
                
                console.error('Error:', error);
            });
    }
    
    // Add event listeners for sending messages
    sendPromptBtn.addEventListener('click', GetResponse);
    
    inputPrompt.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            GetResponse();
        }
    });
}
    
    // Initialize the dashboard when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        initDashboard();
    });
})();