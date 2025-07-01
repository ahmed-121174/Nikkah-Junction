document.addEventListener('DOMContentLoaded', function() {
    // Scroll to bottom of chat container when page loads
    const chatContainer = document.getElementById('chat-container');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Auto-refresh messages every 10 seconds
    if (document.querySelector('.message-form')) {
        setInterval(function() {
            // Get current conversation ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            const conversationId = urlParams.get('conversation_id');
            
            if (conversationId) {
                // Fetch new messages
                fetch(`get_messages.php?conversation_id=${conversationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.messages.length > 0) {
                            updateChatContainer(data.messages);
                        }
                    })
                    .catch(error => console.error('Error fetching messages:', error));
            }
        }, 10000);
    }
    
    // Handle message form submission
    const messageForm = document.querySelector('.message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', function(event) {
            const messageText = messageForm.querySelector('textarea[name="message_text"]').value.trim();
            
            if (!messageText) {
                event.preventDefault();
                alert('Message cannot be empty');
                return;
            }
            
            // Disable the submit button to prevent multiple submissions
            const submitButton = messageForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = 'Sending...';
            }
        });
    }
    
    // Handle friend request status changes
    const friendRequestButtons = document.querySelectorAll('.friend-request-btn');
    friendRequestButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.dataset.requestId;
            const action = this.dataset.action;
            
            // Send AJAX request to update friend request status
            fetch('update_friend_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `request_id=${requestId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page to show updated status
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update request status');
                }
            })
            .catch(error => {
                console.error('Error updating friend request:', error);
                alert('Failed to update request status. Please try again.');
            });
        });
    });
    
    // Handle new conversation forms
    const startConversationForms = document.querySelectorAll('form[name="start_conversation"]');
    startConversationForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = 'Starting...';
            }
        });
    });
    
    // Handle conversation search
    const searchForm = document.querySelector('form[action="dashboard_msgs.php"]');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="search"]');
        if (searchInput) {
            // Add debounce to search input
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length > 2 || this.value.length === 0) {
                        searchForm.submit();
                    }
                }, 500);
            });
        }
    }

    function updateChatContainer(messages) {
        const chatContainer = document.getElementById('chat-container');
        if (!chatContainer) return;
    
        // Maintain global maps of messages by content and sender
        window.messageContentMap = window.messageContentMap || new Map();
    
        // Only add messages not already displayed
        let hasNewMessages = false;
    
        // Check if user was scrolled to bottom
        const wasAtBottom = isScrolledToBottom(chatContainer);
    
        // Identify current user ID
        let currentUserId = null;
        const existingMessages = chatContainer.querySelectorAll('.message');
        if (existingMessages.length > 0) {
            const first = existingMessages[0];
            if (first.classList.contains('message-sent')) {
                currentUserId = first.dataset.senderId;
            } else if (existingMessages.length > 1) {
                const second = existingMessages[1];
                if (second.classList.contains('message-sent')) {
                    currentUserId = second.dataset.senderId;
                }
            }
        }
    
        messages.forEach(message => {
            const messageType = message.sender_id === currentUserId ? 'sent' : 'received';
            const sender = messageType === 'sent' ? 'You' : message.sender_name || 'Other User';
            const messageKey = `${message.sender_id}_${message.message_text}`;
            
            // Check if we already have this message content
            if (window.messageContentMap.has(messageKey)) {
                // Update the timestamp of the existing message
                const existingMessageDiv = window.messageContentMap.get(messageKey);
                const timeDiv = existingMessageDiv.querySelector('.message-time');
                if (timeDiv) {
                    timeDiv.textContent = formatTime(message.created_at);
                }
            } else {
                // This is a new message, create it
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.dataset.messageId = message.message_id;
                messageDiv.dataset.senderId = message.sender_id;
                messageDiv.dataset.messageKey = messageKey;
                messageDiv.classList.add(`message-${messageType}`);
        
                const contentP = document.createElement('p');
                contentP.innerHTML = `<b>${escapeHtml(sender)}:</b> ${escapeHtml(message.message_text)}`;
                messageDiv.appendChild(contentP);
        
                const timeDiv = document.createElement('div');
                timeDiv.classList.add('message-time');
                timeDiv.textContent = formatTime(message.created_at);
                messageDiv.appendChild(timeDiv);
        
                chatContainer.appendChild(messageDiv);
                
                // Add to the map
                window.messageContentMap.set(messageKey, messageDiv);
                
                hasNewMessages = true;
            }
        });
    
        if (wasAtBottom && hasNewMessages) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }
    
    // Function to check if scrolled to bottom
    function isScrolledToBottom(element) {
        return element.scrollHeight - element.clientHeight <= element.scrollTop + 30; // Within 30px of bottom
    }
    
    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});