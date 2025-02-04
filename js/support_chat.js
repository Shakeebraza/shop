document.addEventListener("DOMContentLoaded", function() {
    const ticketItems = document.querySelectorAll(".ticket-item");

    // Toggle ticket conversation display on click
    ticketItems.forEach(item => {
        item.addEventListener("click", function() {
            const ticketId = this.dataset.ticketId;
            toggleConversation(ticketId);
        });
    });
});

// Function to toggle the display of a ticket conversation
function toggleConversation(ticketId) {
    console.log(`Toggling conversation for ticket ID: ${ticketId}`); // Debug log
    const conversation = document.getElementById(`conversation-${ticketId}`);

    if (conversation) {
        // Close all other open conversations first
        document.querySelectorAll(".ticket-conversation").forEach(conv => {
            if (conv.id !== `conversation-${ticketId}`) {
                conv.style.display = "none";
            }
        });

        // Toggle current conversation
        if (conversation.style.display === "none" || conversation.style.display === "") {
            conversation.style.display = "block";
            markAdminTicketAsRead(ticketId); // Mark ticket as read when opened
        } else {
            conversation.style.display = "none";
        }
    } else {
        console.error(`Conversation element not found for ticket ID: ${ticketId}`);
    }
}


// Function to validate input before submission
function validateInput(inputText) {
    const validPattern = /^[a-zA-Z0-9\s.,!?()'"\-]+$/;
    if (!validPattern.test(inputText)) {
        alert("Please use only letters, numbers, spaces, and basic punctuation.");
        return false;
    }
    return true;
}

// Function to mark a ticket as read
function markAdminTicketAsRead(ticketId) {
    fetch(`mark_admin_read.php?ticket_id=${ticketId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const unreadBadge = document.querySelector(`.ticket-item[data-ticket-id="${ticketId}"] .unread-badge`);
                if (unreadBadge) {
                    unreadBadge.style.display = 'none';
                }
                console.log(`Marked ticket ID ${ticketId} as read`);
            } else {
                console.error('Failed to mark ticket as read:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to close a ticket and mark it as read for both admin and user
function closeTicket(ticketId) {
    if (confirm("Are you sure you want to close this ticket?")) {
        fetch('close_ticket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ticket_id: ticketId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || "The ticket has been successfully closed.");

                // Mark the ticket as read for admin (this will also set it as read for the user in close_ticket.php)
                markAdminTicketAsRead(ticketId);

                // Refresh the page to ensure the ticket list and notifications are updated
                location.reload();
            } else {
                alert(data.message || "There was an error closing the ticket.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("There was an error processing your request.");
        });
    }
}

// Function to delete a ticket
function deleteTicket(ticketId) {
    if (confirm("Are you sure you want to delete this ticket?")) {
        fetch('delete_ticket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ticket_id: ticketId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || "The ticket has been successfully deleted.");
                location.reload(); // Refresh the page
            } else {
                alert(data.message || "There was an error deleting the ticket.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("There was an error processing your request.");
        });
    }
}

// Function to submit a reply with input validation
function submitReply(event, ticketId) {
    event.preventDefault(); // Prevent default form submission

    const form = event.target;
    const textArea = form.querySelector('textarea');
    const messageText = textArea.value;

    // Validate input before sending it to the server
    if (!validateInput(messageText)) {
        return; // Stop submission if validation fails
    }

    const formData = new FormData(form);

    // Send AJAX request to submit the reply
    fetch(form.action, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const conversation = document.getElementById(`conversation-${ticketId}`);
            
            // Create a new message div with user message styling
            const newMessage = document.createElement('div');
            newMessage.className = 'user-message';
            newMessage.innerHTML = `
                <p class="message-tag"><strong>${data.username}:</strong></p>
                <p>${data.message}</p>
                <small>${data.created_at}</small>
            `;

            // Insert the new message before the form
            conversation.insertBefore(newMessage, form);
            conversation.style.display = "block";
            textArea.value = ''; // Clear the textarea
        } else {
            alert("There was an error submitting your reply. Please try again.");
        }
    })
    .catch(error => console.error('Error:', error));
}
document.addEventListener("DOMContentLoaded", function () {
    const ticketList = document.querySelector(".ticket-list");

    window.goBackToDashboard = function () {
        const conversations = document.querySelectorAll(".ticket-conversation");
        conversations.forEach(conversation => {
            conversation.style.display = "none"; // Hide all conversations
        });
        ticketList.style.display = "block"; // Show ticket list
    };
});
