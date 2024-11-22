// Assuming username is set correctly from PHP
const username = "<?php echo htmlspecialchars($username); ?>";

// Function to submit a reply via AJAX
function submitReply(event, ticketId) {
    event.preventDefault();

    const form = event.target;
    const messageField = form.querySelector("textarea");
    const message = messageField.value.trim();

    if (message.length === 0) {
        alert("Message cannot be empty.");
        return;
    }

    if (message.length > 500) {
        alert("Message cannot exceed 500 characters.");
        return;
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add the new message to the conversation
                const conversation = document.getElementById(`conversation-${ticketId}`);
                const newMessage = document.createElement('div');
                newMessage.className = 'user-message';
                newMessage.innerHTML = `
                    <p class="message-tag"><strong>${data.username || username}:</strong></p>
                    <p>${data.message}</p>
                    <small>${data.created_at}</small>
                `;
                conversation.insertBefore(newMessage, form);

                // Clear input and reset the character counter
                messageField.value = '';
                const charCount = document.getElementById(`reply-char-count-${ticketId}`);
                if (charCount) charCount.textContent = "0/500";

                conversation.style.display = "block";
            } else {
                // Handle errors, including closed ticket
                alert(data.message || 'Unable to send your message.');
                if (data.message === 'This ticket is closed. You cannot send further replies.') {
                    // Hide the reply form for closed tickets
                    form.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('There was an issue processing your request. Please try again.');
        });
}

// Function to toggle the visibility of a conversation
function toggleConversation(ticketId) {
    const conversation = document.getElementById(`conversation-${ticketId}`);
    if (conversation) {
        conversation.style.display = (conversation.style.display === "none") ? "block" : "none";
    } else {
        console.error(`Conversation element not found for ticket ID: ${ticketId}`);
    }
}

// Function to show a specific section and hide others
function showSection(sectionId) {
    // Hide all conversations
    const conversations = document.querySelectorAll('.conversation-details');
    conversations.forEach(conversation => {
        conversation.style.display = 'none';
    });

    // Hide all sections and show only the selected one
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => section.style.display = 'none');
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    } else {
        console.error(`Section element not found for ID: ${sectionId}`);
    }
}

// Function to validate issue input before form submission
function validateInput() {
    const issueField = document.getElementById("issue");
    const issueText = issueField.value.trim();

    // Regular expression to allow only safe characters (alphanumeric, spaces, basic punctuation)
    const validPattern = /^[a-zA-Z0-9\s.,!?()'"\-]+$/;

    if (!validPattern.test(issueText)) {
        alert("Please use only letters, numbers, spaces, and basic punctuation in your issue description.");
        return false;
    }

    if (issueText.length > 500) {
        alert("Issue description cannot exceed 500 characters.");
        return false;
    }

    return true;
}

// Live character counter for ticket and reply forms
document.addEventListener("input", function (event) {
    const target = event.target;

    if (target.id === "ticket-message") {
        const count = target.value.length;
        document.getElementById("ticket-char-count").textContent = `${count}/500`;
    }

    if (target.id.startsWith("reply-message-")) {
        const ticketId = target.id.split("-")[2];
        const count = target.value.length;
        document.getElementById(`reply-char-count-${ticketId}`).textContent = `${count}/500`;
    }
});
