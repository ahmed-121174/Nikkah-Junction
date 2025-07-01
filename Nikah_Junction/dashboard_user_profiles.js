document.addEventListener('DOMContentLoaded', function() {
    // Initialize the user profiles system
    const userProfileSystem = {
        // Store current state
        currentState: {
            page: 1,
            totalPages: 10,
            selectedUser: null,
            filters: {
                search: '',
                gender: 'all',
                ageMin: null,
                ageMax: null,
                location: ''
            }
        },
        
        // Mock data for demonstration
        users: [
            {
                id: 1,
                name: 'Ahmed Khan',
                email: 'ahmed.khan@example.com',
                age: 28,
                gender: 'Male',
                location: 'Karachi, Pakistan',
                status: 'Active',
                image: 'user1.jpg',
                about: 'Looking for a kind and understanding life partner.'
            },
            {
                id: 2,
                name: 'Haram Ali',
                email: 'Haram.ali@example.com',
                age: 25,
                gender: 'Female',
                location: 'Lahore, Pakistan',
                status: 'Pending',
                image: 'user2.jpg',
                about: 'Educated professional looking for someone with similar values.'
            },
            {
                id: 3,
                name: 'Bilal Qureshi',
                email: 'bilal.qureshi@example.com',
                age: 30,
                gender: 'Male',
                location: 'Islamabad, Pakistan',
                status: 'Active',
                image: 'user3.jpg',
                about: 'Software engineer who enjoys traveling and reading.'
            },
            {
                id: 4,
                name: 'Sara Ahmed',
                email: 'sara.ahmed@example.com',
                age: 27,
                gender: 'Female',
                location: 'Karachi, Pakistan',
                status: 'Active',
                image: 'user4.jpg',
                about: 'Doctor looking for a compatible life partner.'
            },
            {
                id: 5,
                name: 'Usman Malik',
                email: 'usman.malik@example.com',
                age: 32,
                gender: 'Male',
                location: 'Peshawar, Pakistan',
                status: 'Pending',
                image: 'user5.jpg',
                about: 'Business owner who values family traditions.'
            }
        ],
        
        // Initialize event listeners
        init: function() {
            // Add CSS for animations
            this.addAnimationStyles();
            
            this.setupEventListeners();
            this.renderUserTable();
            this.updatePagination();
            
            
            
            // Add initial page load animation
            document.querySelectorAll('.dashboard-section').forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        },
        
        // Add CSS for animations
        addAnimationStyles: function() {
            const styleElement = document.createElement('style');
            styleElement.textContent = `
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                    100% { transform: scale(1); }
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes slideIn {
                    from { transform: translateY(20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                
                @keyframes glow {
                    0% { box-shadow: 0 0 5px rgba(139, 69, 19, 0.5); }
                    50% { box-shadow: 0 0 15px rgba(139, 69, 19, 0.8); }
                    100% { box-shadow: 0 0 5px rgba(139, 69, 19, 0.5); }
                }
                
                .table-row-animation {
                    transition: all 0.3s ease;
                }
                
                .btn {
                    transition: all 0.3s ease;
                }
                
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                }
                
                .btn:active {
                    transform: translateY(1px);
                }
                
                .profile-preview {
                    transition: all 0.4s ease;
                }
                
                .form-group input, .form-group select {
                    transition: border 0.3s ease, box-shadow 0.3s ease;
                }
                
                .form-group input:focus, .form-group select:focus {
                    border-color: var(--primary-color);
                    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.25);
                }
                
                .animate-pulse {
                    animation: pulse 0.6s ease;
                }
                
                .animate-fadeIn {
                    animation: fadeIn 0.5s ease;
                }
                
                .animate-slideIn {
                    animation: slideIn 0.5s ease;
                }
                
                .animate-glow {
                    animation: glow 1.5s infinite;
                }
                
                .data-table tr {
                    transition: background-color 0.3s ease, transform 0.2s ease;
                }
                
                .data-table tr:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    z-index: 1;
                    position: relative;
                }
                
                .notification {
                    animation: slideIn 0.3s ease;
                }
            `;
            document.head.appendChild(styleElement);
        },
        
        setupEventListeners: function() {
            // Filter form submission
            const filterForm = document.querySelector('.form-container');
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
            
            // Add animation to form inputs
            const formInputs = document.querySelectorAll('.form-group input, .form-group select');
            formInputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('animate-glow');
                });
                
                input.addEventListener('blur', () => {
                    input.parentElement.classList.remove('animate-glow');
                });
            });
            
            // Pagination controls
            const prevBtn = document.querySelector('.pagination button:first-child');
            const nextBtn = document.querySelector('.pagination button:last-child');
            
            prevBtn.addEventListener('click', () => this.changePage('prev'));
            nextBtn.addEventListener('click', () => this.changePage('next'));
            
            // Add animation to pagination buttons
            [prevBtn, nextBtn].forEach(btn => {
                btn.addEventListener('mousedown', () => {
                    btn.classList.add('animate-pulse');
                    setTimeout(() => {
                        btn.classList.remove('animate-pulse');
                    }, 600);
                });
            });
            
            // Table row hover effect for better UX
            const tableRows = document.querySelectorAll('.data-table tr');
            tableRows.forEach(row => {
                if (row.cells && row.cells.length > 1) { // Skip header row
                    row.classList.add('table-row-animation');
                }
            });
        },
        
        // Apply filters from the form
        applyFilters: function() {
            const searchInput = document.querySelector('input[type="text"]');
            const genderSelect = document.getElementById('gender');
            const ageMinInput = document.getElementById('age_min');
            const ageMaxInput = document.getElementById('age_max');
            const locationInput = document.getElementById('location');
            
            this.currentState.filters = {
                search: searchInput.value.toLowerCase(),
                gender: genderSelect.value,
                ageMin: ageMinInput.value ? parseInt(ageMinInput.value) : null,
                ageMax: ageMaxInput.value ? parseInt(ageMaxInput.value) : null,
                location: locationInput.value.toLowerCase()
            };
            
            this.currentState.page = 1; // Reset to first page
            
            // Add animation to table before filtering
            const tableSection = document.querySelector('.data-table').parentNode;
            tableSection.style.transition = 'opacity 0.3s ease';
            tableSection.style.opacity = '0.5';
            
            setTimeout(() => {
                this.filterAndRenderUsers();
                tableSection.style.opacity = '1';
                tableSection.classList.add('animate-fadeIn');
                setTimeout(() => {
                    tableSection.classList.remove('animate-fadeIn');
                }, 500);
            }, 300);
        },
        
        // Filter users based on current filters and render
        filterAndRenderUsers: function() {
            const filteredUsers = this.users.filter(user => {
                const matchesSearch = !this.currentState.filters.search || 
                    user.name.toLowerCase().includes(this.currentState.filters.search) || 
                    user.email.toLowerCase().includes(this.currentState.filters.search);
                
                const matchesGender = this.currentState.filters.gender === 'all' || 
                    user.gender.toLowerCase() === this.currentState.filters.gender.toLowerCase();
                
                const matchesAgeMin = !this.currentState.filters.ageMin || 
                    user.age >= this.currentState.filters.ageMin;
                
                const matchesAgeMax = !this.currentState.filters.ageMax || 
                    user.age <= this.currentState.filters.ageMax;
                
                const matchesLocation = !this.currentState.filters.location || 
                    user.location.toLowerCase().includes(this.currentState.filters.location);
                
                return matchesSearch && matchesGender && matchesAgeMin && 
                       matchesAgeMax && matchesLocation;
            });
            
            // Update total pages based on filtered results
            this.currentState.totalPages = Math.max(1, Math.ceil(filteredUsers.length / 3));
            
            // Render table with filtered users
            this.renderUserTable(filteredUsers);
            this.updatePagination();
        },
        
        // Handle pagination
        changePage: function(direction) {
            if (direction === 'prev' && this.currentState.page > 1) {
                this.currentState.page--;
            } else if (direction === 'next' && this.currentState.page < this.currentState.totalPages) {
                this.currentState.page++;
            }
            
            // Add slide animation during page change
            const dataTable = document.querySelector('.data-table');
            dataTable.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            
            if (direction === 'next') {
                dataTable.style.transform = 'translateX(-20px)';
            } else {
                dataTable.style.transform = 'translateX(20px)';
            }
            
            dataTable.style.opacity = '0';
            
            setTimeout(() => {
                this.renderUserTable();
                
                // Reset position before fading in
                dataTable.style.transform = direction === 'next' ? 'translateX(20px)' : 'translateX(-20px)';
                
                setTimeout(() => {
                    dataTable.style.opacity = '1';
                    dataTable.style.transform = 'translateX(0)';
                }, 50);
            }, 300);
            
            this.updatePagination();
        },
        
        // Update pagination display
        updatePagination: function() {
            const paginationText = document.querySelector('.pagination span');
            paginationText.textContent = `Page ${this.currentState.page} of ${this.currentState.totalPages}`;
            
            // Enable/disable buttons based on current page
            const prevBtn = document.querySelector('.pagination button:first-child');
            const nextBtn = document.querySelector('.pagination button:last-child');
            
            prevBtn.disabled = this.currentState.page === 1;
            nextBtn.disabled = this.currentState.page === this.currentState.totalPages;
            
            prevBtn.style.opacity = this.currentState.page === 1 ? '0.5' : '1';
            nextBtn.style.opacity = this.currentState.page === this.currentState.totalPages ? '0.5' : '1';
        },
        
        // Render user table with pagination
        renderUserTable: function(filteredUsers = null) {
            const dataTable = document.querySelector('.data-table');
            const usersToShow = filteredUsers || this.users;
            
            // Keep the header row
            const headerRow = dataTable.rows[0];
            dataTable.innerHTML = '';
            dataTable.appendChild(headerRow);
            
            // Calculate pagination
            const startIndex = (this.currentState.page - 1) * 3;
            const endIndex = startIndex + 3;
            const paginatedUsers = usersToShow.slice(startIndex, endIndex);
            
            // Add user rows with staggered animation
            paginatedUsers.forEach((user, index) => {
                const row = document.createElement('tr');
                row.classList.add('table-row-animation');
                
                // Set initial state for animation
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                
                row.innerHTML = `
                    <td><img src="${user.image}" alt="${user.name}" width="50"></td>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.age}</td>
                    <td>${user.gender}</td>
                    <td>${user.location}</td>
                    <td>${user.status}</td>
                    <td class="table-actions">
                        <a href="#" class="view-profile" data-id="${user.id}">View</a>
                        <a href="#" class="approve-profile" data-id="${user.id}">Approve</a>
                        <a href="#" class="reject-profile" data-id="${user.id}">Reject</a>
                        <a href="#" class="message-profile" data-id="${user.id}">Message</a>
                    </td>
                `;
                
                dataTable.appendChild(row);
                
                // Animate row entry with delay based on index
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 100 * index);
            });
            
            // Add event listeners to the newly created buttons
            setTimeout(() => {
                this.setupProfileActionButtons();
            }, 300);
        },
        
        // Setup event listeners for profile action buttons
        setupProfileActionButtons: function() {
            // View profile buttons
            const viewButtons = document.querySelectorAll('.view-profile');
            viewButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const userId = parseInt(button.getAttribute('data-id'));
                    const user = this.users.find(u => u.id === userId);
                    if (user) {
                        this.showUserPreview(user);
                    }
                });
                
                // Add hover effect
                button.addEventListener('mouseenter', () => {
                    button.style.transition = 'color 0.3s ease';
                    button.style.color = '#8B4513';
                    button.style.fontWeight = 'bold';
                });
                
                button.addEventListener('mouseleave', () => {
                    button.style.color = '';
                    button.style.fontWeight = '';
                });
            });
            
            // Approve profile buttons
            const approveButtons = document.querySelectorAll('.approve-profile');
            approveButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    button.classList.add('animate-pulse');
                    const userId = parseInt(button.getAttribute('data-id'));
                    this.approveUser(userId);
                });
                
                // Add hover effect
                button.addEventListener('mouseenter', () => {
                    button.style.transition = 'color 0.3s ease';
                    button.style.color = '#4CAF50';
                });
                
                button.addEventListener('mouseleave', () => {
                    button.style.color = '';
                });
            });
            
            // Reject profile buttons
            const rejectButtons = document.querySelectorAll('.reject-profile');
            rejectButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    button.classList.add('animate-pulse');
                    const userId = parseInt(button.getAttribute('data-id'));
                    this.rejectUser(userId);
                });
                
                // Add hover effect
                button.addEventListener('mouseenter', () => {
                    button.style.transition = 'color 0.3s ease';
                    button.style.color = '#F44336';
                });
                
                button.addEventListener('mouseleave', () => {
                    button.style.color = '';
                });
            });
            
            // Message profile buttons
            const messageButtons = document.querySelectorAll('.message-profile');
            messageButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    button.classList.add('animate-pulse');
                    const userId = parseInt(button.getAttribute('data-id'));
                    this.messageUser(userId);
                });
                
                // Add hover effect
                button.addEventListener('mouseenter', () => {
                    button.style.transition = 'color 0.3s ease';
                    button.style.color = '#2196F3';
                });
                
                button.addEventListener('mouseleave', () => {
                    button.style.color = '';
                });
            });
        },
        
        // Show user preview in the profile preview section
        showUserPreview: function(user) {
            const previewSection = document.querySelector('.profile-preview');
            
            // Fade out first
            previewSection.style.opacity = '0';
            previewSection.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                previewSection.innerHTML = `
                    <h3>${user.name}</h3>
                    <p><strong>Email:</strong> ${user.email}</p>
                    <p><strong>Age:</strong> ${user.age}</p>
                    <p><strong>Gender:</strong> ${user.gender}</p>
                    <p><strong>Location:</strong> ${user.location}</p>
                    <p><strong>Status:</strong> ${user.status}</p>
                    <p><strong>About:</strong> ${user.about}</p>
                    
                    <div class="profile-actions">
                        <button class="btn btn-accent approve-btn" data-id="${user.id}">Approve</button>
                        <button class="btn reject-btn" data-id="${user.id}">Reject</button>
                        <button class="btn btn-primary message-btn" data-id="${user.id}">Message</button>
                    </div>
                `;
                
                // Fade in with transform
                previewSection.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                previewSection.style.opacity = '1';
                previewSection.style.transform = 'scale(1)';
                
                // Add event listeners to buttons in preview
                const approveBtn = previewSection.querySelector('.approve-btn');
                const rejectBtn = previewSection.querySelector('.reject-btn');
                const messageBtn = previewSection.querySelector('.message-btn');
                
                approveBtn.addEventListener('click', () => {
                    approveBtn.classList.add('animate-pulse');
                    this.approveUser(user.id);
                });
                
                rejectBtn.addEventListener('click', () => {
                    rejectBtn.classList.add('animate-pulse');
                    this.rejectUser(user.id);
                });
                
                messageBtn.addEventListener('click', () => {
                    messageBtn.classList.add('animate-pulse');
                    this.messageUser(user.id);
                });
            }, 300);
            
            // Update current selected user
            this.currentState.selectedUser = user.id;
        },
        
        // Approve a user profile
        approveUser: function(userId) {
            const user = this.users.find(u => u.id === userId);
            if (user) {
                user.status = 'Active';
                
                // Update UI
                this.renderUserTable();
                
                // If this is the currently previewed user, update preview
                if (this.currentState.selectedUser === userId) {
                    this.showUserPreview(user);
                }
                
                // Show notification
                this.showNotification(`${user.name}'s profile has been approved.`, 'success');
            }
        },
        
        // Reject a user profile
        rejectUser: function(userId) {
            const user = this.users.find(u => u.id === userId);
            if (user) {
                user.status = 'Rejected';
                
                // Update UI
                this.renderUserTable();
                
                // If this is the currently previewed user, update preview
                if (this.currentState.selectedUser === userId) {
                    this.showUserPreview(user);
                }
                
                // Show notification
                this.showNotification(`${user.name}'s profile has been rejected.`, 'error');
            }
        },
        
        // Message a user
        messageUser: function(userId) {
            const user = this.users.find(u => u.id === userId);
            if (user) {
                // In a real application, this would open a messaging interface
                // For now, just show a notification
                this.showNotification(`Opening message interface for ${user.name}...`, 'info');
                
                // Create a small animation before showing the alert
                const overlay = document.createElement('div');
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.backgroundColor = 'rgba(0,0,0,0.3)';
                overlay.style.zIndex = '999';
                overlay.style.opacity = '0';
                overlay.style.transition = 'opacity 0.3s ease';
                document.body.appendChild(overlay);
                
                setTimeout(() => {
                    overlay.style.opacity = '1';
                    
                    setTimeout(() => {
                        overlay.style.opacity = '0';
                        setTimeout(() => {
                            overlay.remove();
                            alert(`This would open a messaging interface to contact ${user.name}.`);
                        }, 300);
                    }, 500);
                }, 10);
            }
        },
        
        // Show notification to the user
        showNotification: function(message, type = 'info') {
            // Create notification element if it doesn't exist
            let notification = document.querySelector('.notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'notification';
                document.body.appendChild(notification);
                
                // Style the notification
                notification.style.position = 'fixed';
                notification.style.bottom = '20px';
                notification.style.right = '20px';
                notification.style.padding = '15px 20px';
                notification.style.borderRadius = '8px';
                notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
                notification.style.zIndex = '1000';
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(20px)';
            }
            
            // Set type-specific styles
            switch(type) {
                case 'success':
                    notification.style.backgroundColor = '#4CAF50';
                    notification.style.color = 'white';
                    break;
                case 'error':
                    notification.style.backgroundColor = '#F44336';
                    notification.style.color = 'white';
                    break;
                case 'info':
                default:
                    notification.style.backgroundColor = '#2196F3';
                    notification.style.color = 'white';
            }
            
            // Set message and show notification with animation
            notification.textContent = message;
            notification.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
            
            // Add subtle animation to notification
            setTimeout(() => {
                notification.style.animation = 'pulse 2s infinite';
            }, 300);
            
            // Hide after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    };
    
    // Initialize the user profile system
    userProfileSystem.init();
});