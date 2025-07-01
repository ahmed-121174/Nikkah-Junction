document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    // Initialize FAQ items - hide all answers initially
    faqItems.forEach(item => {
        const answer = item.querySelector('.faq-answer');
        const question = item.querySelector('.faq-question');
        
        // Set initial state
        answer.style.display = 'none';
        
        // Add accessibility attributes
        question.setAttribute('role', 'button');
        question.setAttribute('aria-expanded', 'false');
        question.setAttribute('tabindex', '0');
        question.setAttribute('aria-controls', `answer-${generateUniqueId()}`);
        answer.setAttribute('id', question.getAttribute('aria-controls'));
        
        // Add visual indicator
        const indicator = document.createElement('span');
        indicator.classList.add('faq-indicator');
        indicator.innerHTML = '+';
        question.appendChild(indicator);
    });
    
    // Add event listeners - both click and keyboard
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const indicator = question.querySelector('.faq-indicator');
        
        // Click event
        question.addEventListener('click', () => toggleFaq(question, answer, indicator));
        
        // Keyboard event for accessibility
        question.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleFaq(question, answer, indicator);
            }
        });
    });
    
    // Function to toggle FAQ items with animation
    function toggleFaq(question, answer, indicator) {
        const isOpen = question.getAttribute('aria-expanded') === 'true';
        
        // Close all other FAQs (accordion behavior)
        faqItems.forEach(otherItem => {
            const otherQuestion = otherItem.querySelector('.faq-question');
            const otherAnswer = otherItem.querySelector('.faq-answer');
            const otherIndicator = otherQuestion.querySelector('.faq-indicator');
            
            if (otherQuestion !== question && otherQuestion.getAttribute('aria-expanded') === 'true') {
                otherAnswer.style.maxHeight = '0px';
                setTimeout(() => {
                    otherAnswer.style.display = 'none';
                }, 300);
                otherQuestion.setAttribute('aria-expanded', 'false');
                otherIndicator.innerHTML = '+';
            }
        });
        
        // Toggle current FAQ
        if (isOpen) {
            answer.style.maxHeight = '0px';
            setTimeout(() => {
                answer.style.display = 'none';
            }, 300);
            question.setAttribute('aria-expanded', 'false');
            indicator.innerHTML = '+';
        } else {
            answer.style.display = 'block';
            answer.style.maxHeight = '0px';
            setTimeout(() => {
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }, 10);
            question.setAttribute('aria-expanded', 'true');
            indicator.innerHTML = '−';
        }
    }
    
    // Generate a unique ID for accessibility attributes
    function generateUniqueId() {
        return 'faq-' + Math.random().toString(36).substring(2, 9);
    }
    
    // Add search functionality for FAQs
    const searchContainer = document.createElement('div');
    searchContainer.classList.add('faq-search-container');
    searchContainer.innerHTML = `
        <input type="text" id="faqSearch" class="faq-search" placeholder="Search FAQs...">
        <button id="faqSearchButton" class="btn btn-primary">Search</button>
        <button id="faqResetButton" class="btn btn-secondary">Reset</button>
    `;
    
    // Insert search before FAQ container
    const faqContainer = document.querySelector('.faq-container');
    faqContainer.parentNode.insertBefore(searchContainer, faqContainer);
    
    // Add search event listeners
    const searchInput = document.getElementById('faqSearch');
    const searchButton = document.getElementById('faqSearchButton');
    const resetButton = document.getElementById('faqResetButton');
    
    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') performSearch();
    });
    resetButton.addEventListener('click', resetSearch);
    
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        if (searchTerm === '') return;
        
        let matchFound = false;
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question').textContent.toLowerCase();
            const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
            
            if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                item.style.display = 'block';
                item.classList.add('search-highlight');
                matchFound = true;
                
                // Automatically open matching items
                const questionEl = item.querySelector('.faq-question');
                const answerEl = item.querySelector('.faq-answer');
                const indicator = questionEl.querySelector('.faq-indicator');
                
                answerEl.style.display = 'block';
                answerEl.style.maxHeight = answerEl.scrollHeight + 'px';
                questionEl.setAttribute('aria-expanded', 'true');
                indicator.innerHTML = '−';
                
                // Highlight matching text
                const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
                item.innerHTML = item.innerHTML.replace(regex, '<mark>$1</mark>');
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show message if no results found
        let noResultsMsg = document.getElementById('noResultsMsg');
        if (!matchFound) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMsg';
                noResultsMsg.classList.add('no-results-message');
                noResultsMsg.textContent = 'No matching FAQs found. Please try another search term.';
                faqContainer.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
    
    function resetSearch() {
        searchInput.value = '';
        
        // Reset all items to default state
        faqItems.forEach(item => {
            item.style.display = 'block';
            item.classList.remove('search-highlight');
            
            // Close all items
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            const indicator = question.querySelector('.faq-indicator');
            
            answer.style.maxHeight = '0px';
            setTimeout(() => {
                answer.style.display = 'none';
            }, 300);
            question.setAttribute('aria-expanded', 'false');
            indicator.innerHTML = '+';
            
            // Remove highlighting
            item.innerHTML = item.innerHTML.replace(/<mark>(.*?)<\/mark>/g, '$1');
        });
        
        // Hide no results message if present
        const noResultsMsg = document.getElementById('noResultsMsg');
        if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
    
    // Helper function to escape special characters in search term for regex
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    // Add CSS styles for new features
    const styleEl = document.createElement('style');
    styleEl.textContent = `
        .faq-question {
            position: relative;
            transition: background-color 0.3s ease;
            padding-right: 40px;
        }
        
        .faq-question:hover {
            background-color: #f0c27b;
        }
        
        .faq-indicator {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            font-weight: bold;
        }
        
        .faq-answer {
            transition: max-height 0.3s ease;
            overflow: hidden;
        }
        
        .faq-search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .faq-search {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
        }
        
        .search-highlight {
            box-shadow: 0 0 8px rgba(139, 69, 19, 0.5);
        }
        
        mark {
            background-color: #ffe082;
            padding: 0 3px;
            border-radius: 3px;
        }
        
        .no-results-message {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            margin-top: 20px;
            color: #666;
        }
    `;
    document.head.appendChild(styleEl);
    
    // Add URL hash functionality to directly open specific FAQs
    if (window.location.hash) {
        const hash = window.location.hash.substring(1); // Remove the # character
        const targetQuestion = document.getElementById(hash);
        if (targetQuestion && targetQuestion.classList.contains('faq-question')) {
            const targetItem = targetQuestion.closest('.faq-item');
            const answer = targetItem.querySelector('.faq-answer');
            const indicator = targetQuestion.querySelector('.faq-indicator');
            
            // Open the targeted FAQ
            answer.style.display = 'block';
            answer.style.maxHeight = answer.scrollHeight + 'px';
            targetQuestion.setAttribute('aria-expanded', 'true');
            indicator.innerHTML = '−';
            
            // Scroll to the FAQ
            setTimeout(() => {
                targetItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 500);
        }
    }
    
    // Help bot feature for common questions
    const helpBotContainer = document.createElement('div');
    helpBotContainer.classList.add('help-bot-container');
    helpBotContainer.innerHTML = `
        <div class="help-bot-icon">
            <span class="bot-icon">💬</span>
        </div>
        <div class="help-bot-chat">
            <div class="help-bot-header">
                <h3>Nikkah Junction Assistant</h3>
                <span class="close-bot">×</span>
            </div>
            <div class="help-bot-messages">
                <div class="bot-message">
                    Assalam-o-Alaikum! I'm here to help you with any questions about Nikkah Junction. What can I assist you with today?
                </div>
                <div class="quick-questions">
                    <button class="quick-question">How do I get verified?</button>
                    <button class="quick-question">Is this service free?</button>
                    <button class="quick-question">How secure is my data?</button>
                </div>
            </div>
            <div class="help-bot-input">
                <input type="text" placeholder="Type your question...">
                <button class="send-question">Send</button>
            </div>
        </div>
    `;
    document.body.appendChild(helpBotContainer);
    
    // Add bot styles
    const botStyleEl = document.createElement('style');
    botStyleEl.textContent = `
        .help-bot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .help-bot-icon {
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .help-bot-icon:hover {
            transform: scale(1.1);
        }
        
        .bot-icon {
            font-size: 30px;
            color: white;
        }
        
        .help-bot-chat {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 320px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            display: none;
            overflow: hidden;
        }
        
        .help-bot-header {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .help-bot-header h3 {
            margin: 0;
            color: white;
        }
        
        .close-bot {
            font-size: 24px;
            cursor: pointer;
        }
        
        .help-bot-messages {
            height: 300px;
            overflow-y: auto;
            padding: 15px;
        }
        
        .bot-message, .user-message {
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            max-width: 80%;
        }
        
        .bot-message {
            background-color: #f1f1f1;
            align-self: flex-start;
        }
        
        .user-message {
            background-color: var(--secondary-color);
            color: var(--dark-color);
            margin-left: auto;
            text-align: right;
        }
        
        .quick-questions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 15px;
        }
        
        .quick-question {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            text-align: left;
            transition: background-color 0.2s ease;
        }
        
        .quick-question:hover {
            background-color: #e8e8e8;
        }
        
        .help-bot-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
        
        .help-bot-input input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 20px;
            margin-right: 10px;
        }
        
        .send-question {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            cursor: pointer;
        }
    `;
    document.head.appendChild(botStyleEl);
    
    // Help bot functionality
    const botIcon = document.querySelector('.help-bot-icon');
    const botChat = document.querySelector('.help-bot-chat');
    const closeBot = document.querySelector('.close-bot');
    const botMessages = document.querySelector('.help-bot-messages');
    const questionInput = document.querySelector('.help-bot-input input');
    const sendButton = document.querySelector('.send-question');
    const quickQuestions = document.querySelectorAll('.quick-question');
    
    botIcon.addEventListener('click', () => {
        botChat.style.display = botChat.style.display === 'block' ? 'none' : 'block';
    });
    
    closeBot.addEventListener('click', () => {
        botChat.style.display = 'none';
    });
    
    // Process questions and generate responses
    function processQuestion(question) {
        // Add user message
        const userMessageEl = document.createElement('div');
        userMessageEl.classList.add('user-message');
        userMessageEl.textContent = question;
        botMessages.appendChild(userMessageEl);
        
        // Simulate typing
        setTimeout(() => {
            const botMessageEl = document.createElement('div');
            botMessageEl.classList.add('bot-message');
            
            // Simple response logic based on keywords
            const response = getBotResponse(question);
            botMessageEl.textContent = response;
            
            botMessages.appendChild(botMessageEl);
            botMessages.scrollTop = botMessages.scrollHeight;
        }, 1000);
    }
    
    function getBotResponse(question) {
        question = question.toLowerCase();
        
        // Basic response mapping
        if (question.includes('verify') || question.includes('verification') || question.includes('verified')) {
            return "To get verified, upload your government-issued ID card in your profile section. Our team will verify your profile within 24-48 hours.";
        } else if (question.includes('free') || question.includes('cost') || question.includes('price')) {
            return "Yes, Nikkah Junction is completely free to use. You can create your profile, browse matches, and send connection requests at no cost.";
        } else if (question.includes('secure') || question.includes('privacy') || question.includes('data')) {
            return "Your data security is our priority. We use encryption to protect your personal information, and we never share your details with third parties without your consent.";
        } else if (question.includes('profile') && question.includes('edit')) {
            return "To edit your profile, log in and go to your dashboard. Click on 'Edit Profile' and update the information you want to change.";
        } else if (question.includes('picture') || question.includes('photo')) {
            return "While we recommend uploading a profile picture for better responses, it's not mandatory. You can still create and use your profile without one.";
        } else if (question.includes('matches')) {
            return "You can browse matches based on your preferences. We show profiles that are compatible with your requirements and preferences.";
        } else if (question.includes('salamalaikum') || question.includes('salam') || question.includes('hello') || question.includes('hi')) {
            return "Walaikum Assalam! How can I assist you today with Nikkah Junction?";
        } else {
            return "Thank you for your question. For specific information, please check our FAQ section above or contact our support team through the Contact Us page.";
        }
    }
    
    // Handle send button click
    sendButton.addEventListener('click', () => {
        const question = questionInput.value.trim();
        if (question !== '') {
            processQuestion(question);
            questionInput.value = '';
        }
    });
    
    // Handle enter key in input
    questionInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const question = questionInput.value.trim();
            if (question !== '') {
                processQuestion(question);
                questionInput.value = '';
            }
        }
    });
    
    // Handle quick questions
    quickQuestions.forEach(button => {
        button.addEventListener('click', () => {
            processQuestion(button.textContent);
        });
    });
    
    // Add auto-suggestion feature to search
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase().trim();
        if (searchTerm.length < 2) return;
        
        // Collect all FAQ questions for auto-suggestions
        const questions = Array.from(document.querySelectorAll('.faq-question'))
            .map(q => q.textContent.replace(/[+−]/, '').trim());
        
        // Filter questions that match the search term
        const matches = questions.filter(q => 
            q.toLowerCase().includes(searchTerm)
        ).slice(0, 5); // Limit to 5 suggestions
        
        // Update or create suggestions list
        let suggestionsEl = document.querySelector('.search-suggestions');
        if (!matches.length) {
            if (suggestionsEl) suggestionsEl.remove();
            return;
        }
        
        if (!suggestionsEl) {
            suggestionsEl = document.createElement('div');
            suggestionsEl.classList.add('search-suggestions');
            searchContainer.appendChild(suggestionsEl);
        }
        
        // Update suggestions content
        suggestionsEl.innerHTML = '';
        matches.forEach(match => {
            const suggestionItem = document.createElement('div');
            suggestionItem.classList.add('suggestion-item');
            suggestionItem.textContent = match;
            suggestionItem.addEventListener('click', () => {
                searchInput.value = match;
                performSearch();
                suggestionsEl.remove();
            });
            suggestionsEl.appendChild(suggestionItem);
        });
    });
    
    // Close suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.faq-search-container')) {
            const suggestionsEl = document.querySelector('.search-suggestions');
            if (suggestionsEl) suggestionsEl.remove();
        }
    });
    
    // Add suggestion styles
    const suggestionStyleEl = document.createElement('style');
    suggestionStyleEl.textContent = `
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 10;
        }
        
        .faq-search-container {
            position: relative;
        }
        
        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        .suggestion-item:hover {
            background-color: #f5f5f5;
        }
    `;
    document.head.appendChild(suggestionStyleEl);
    
    // Add print functionality for FAQs
    const printButton = document.createElement('button');
    printButton.classList.add('btn', 'btn-secondary', 'print-faq-btn');
    printButton.textContent = 'Print FAQs';
    searchContainer.appendChild(printButton);
    
    printButton.addEventListener('click', () => {
        // Prepare print-friendly version
        const printWindow = window.open('', '_blank');
        
        // Get site info
        const siteTitle = document.querySelector('.header h1').textContent;
        const siteTagline = document.querySelector('.tagline').textContent;
        
        // Create print content
        let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>FAQs - ${siteTitle}</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .print-header { text-align: center; margin-bottom: 30px; }
                    .print-header h1 { color: #8B4513; margin-bottom: 5px; }
                    .print-tagline { font-style: italic; color: #8B4513; }
                    .faq-item { margin-bottom: 20px; }
                    .faq-question { font-weight: bold; margin-bottom: 10px; color: #8B4513; }
                    .print-footer { margin-top: 40px; text-align: center; font-size: 0.9em; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
                    @media print {
                        .print-btn { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h1>${siteTitle}</h1>
                    <p class="print-tagline">${siteTagline}</p>
                    <h2>Frequently Asked Questions</h2>
                </div>
                <div class="print-btn" style="text-align: center; margin-bottom: 20px;">
                    <button onclick="window.print();" style="padding: 10px 20px; background-color: #8B4513; color: white; border: none; border-radius: 5px; cursor: pointer;">Print this page</button>
                </div>
        `;
        
        // Add all FAQs
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question').textContent.replace(/[+−]/, '').trim();
            const answer = item.querySelector('.faq-answer').textContent.trim();
            
            printContent += `
                <div class="faq-item">
                    <div class="faq-question">${question}</div>
                    <div class="faq-answer">${answer}</div>
                </div>
            `;
        });
        
        // Add footer and close tags
        printContent += `
                <div class="print-footer">
                    <p>&copy; ${new Date().getFullYear()} ${siteTitle}. All rights reserved.</p>
                    <p>Generated on ${new Date().toLocaleDateString()}</p>
                </div>
            </body>
            </html>
        `;
        
        // Write to new window and trigger print
        printWindow.document.write(printContent);
        printWindow.document.close();
    });
    
    // Add style for print button
    const printStyleEl = document.createElement('style');
    printStyleEl.textContent = `
        .print-faq-btn {
            white-space: nowrap;
        }
    `;
    document.head.appendChild(printStyleEl);
});