<?php
include_once('../../header.php');
$hasOpenTicket = false;
foreach ($tickets as $ticket) {
    if ($ticket['status'] === 'open') {
        $hasOpenTicket = true;
        break;
    }
}



?>
<!-- Main Content Area -->
<div class="main-content">

    <div id="support" class="uuper">
        <h2>Support</h2>

        <!-- Always show the ticket form, but disable if there is an open ticket -->
        <div class="ticket-form">
            <h3>Open a New Ticket</h3>
            <form id="ticket-form">
                <textarea name="message" id="ticket-message" placeholder="Describe your issue..." rows="4"
                    maxlength="500" required <?php echo $hasOpenTicket ? 'disabled' : ''; ?>></textarea>
                <small id="ticket-char-count">0/500</small>
                <button type="submit" <?php echo $hasOpenTicket ? 'disabled' : ''; ?> id="submit-ticket-btn">Submit
                    Ticket</button>
            </form>
            <?php if ($hasOpenTicket): ?>
            <p class="disabled-message">Please have an admin close this ticket before opening a new one.</p>
            <?php endif; ?>
        </div>

        <!-- Check if there are tickets available -->
        <?php if (!empty($tickets)): ?>
        <div class="ticket-list">
            <?php foreach ($tickets as $ticket): ?>
            <div class="ticket-item">
                <div class="ticket-summary"
                    onclick="handleTicketClick(event, <?php echo htmlspecialchars($ticket['id']); ?>)">
                    <span>Ticket #<?php echo htmlspecialchars($ticket['id']); ?> -
                        <?php echo htmlspecialchars($ticket['created_at']); ?></span>
                    <small>Status: <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?></small>
                </div>

                <div id="conversation-<?php echo htmlspecialchars($ticket['id']); ?>" class="conversation-details"
                    style="display: none;">
                    <p><?php echo htmlspecialchars($ticket['message']); ?></p>

                    <?php
        $stmt = $pdo->prepare("SELECT * FROM support_replies WHERE ticket_id = ? ORDER BY created_at ASC");
        $stmt->execute([$ticket['id']]);
        $replies = $stmt->fetchAll();

        $userReplyCount = 0; // Initialize the userReplyCount

        foreach ($replies as $reply) {
            $messageClass = ($reply['sender'] === 'user') ? 'user-message' : 'admin-message';
            $senderName = ($reply['sender'] === 'user') ? htmlspecialchars($username) : 'Admin';

            if ($reply['sender'] === 'user') {
                $userReplyCount++;
            } else {
                $userReplyCount = 0; // Reset after admin reply
            }
            ?>
                    <div class="<?php echo $messageClass; ?>"
                        data-reply-id="<?php echo htmlspecialchars($reply['id']); ?>">
                        <p class="message-tag"><strong><?php echo htmlspecialchars($senderName); ?>:</strong></p>
                        <p><?php echo htmlspecialchars($reply['message']); ?></p>
                        <small><?php echo htmlspecialchars($reply['created_at']); ?></small>
                    </div>
                    <?php } ?>

                    <?php if ($ticket['status'] === 'open' && $userReplyCount < 3): ?>
                    <form method="POST" action="submit_reply.php" class="reply-section"
                        onsubmit="submitReplyy(event, <?php echo htmlspecialchars($ticket['id']); ?>)">
                        <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                        <textarea name="message" id="reply-message-<?php echo htmlspecialchars($ticket['id']); ?>"
                            placeholder="Reply..." rows="2" maxlength="500" required></textarea>
                        <small id="reply-char-count-<?php echo htmlspecialchars($ticket['id']); ?>">0/500</small>
                        <button type="submit"
                            id="reply-btn-<?php echo htmlspecialchars($ticket['id']); ?>">Send</button>
                    </form>
                    <?php elseif ($userReplyCount >= 3 && $ticket['status'] === 'open'): ?>
                    <p class="disabled-message">You cannot send more than 3 consecutive messages without an admin reply.
                    </p>
                    <?php elseif ($ticket['status'] === 'closed'): ?>
                    <p class="disabled-message">The conversation has been closed by the Admin. You may proceed by
                        opening a new
                        ticket if further assistance is required.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="ticket-list">
            <p>No open tickets at the moment.</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>
<?php
include_once('../../footer.php');
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ticketForm = document.getElementById("ticket-form");
    const submitBtn = document.getElementById("submit-ticket-btn");
    const messageInput = document.getElementById("ticket-message");

    ticketForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const message = messageInput.value.trim();

        if (message.length === 0) {

            return;
        }

        submitBtn.disabled = true;

        fetch("submit_ticket.php", {
                method: "POST",
                body: new FormData(ticketForm),
            })
            .then(response => response.json())
            .then(data => {
                // showPopupMessage(data.message);
                if (data.success) {
                    window.location.reload();
                    messageInput.value = "";
                }
            })
            .catch(error => {
                showPopupMessage("An error occurred. Please try again.");
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
    });
});

function submitReplyy(event, ticketId) {
    event.preventDefault();

    const form = event.target;
    const textArea = form.querySelector('textarea');
    const messageText = textArea.value.trim();
    const replyBtn = form.querySelector('button[type="submit"]');




    const formData = new FormData(form);


    replyBtn.disabled = true;
    replyBtn.innerText = "Sending...";

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

                // Clear the textarea
                textArea.value = '';

                // Show success popup
                // showPopupMessage("Reply submitted successfully!", "success");
            } else {


                showPopupMessage(data.message || "Error submitting reply.", "error");
                setTimeout(function() {
                    location.reload();
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPopupMessage("A network error occurred. Please try again.", "error");
        })
        .finally(() => {
            // Re-enable button
            replyBtn.disabled = false;
            replyBtn.innerText = "Send";
        });
}

function handleTicketClick(event, ticketId) {
    // Prevent the default action if needed
    event.preventDefault();

    // Call toggleConversation
    toggleConversation(ticketId);

    // Call markAsRead with the ticketId
    markAsRead(ticketId);
}
// Function to mark a reply as read
function markAsRead(reply) {
    console.log(reply)
    const replyId = reply;

    // Send AJAX request to mark the message as read
    fetch('mark_as_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reply_id: replyId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Optionally change the style of the message to indicate it has been read
                reply.style.backgroundColor = '#f0f0f0'; // Change background to indicate it's been read
            }
        })
        .catch(error => console.error('Error:', error));
}

function closeRulesPopup() {
    const popup = document.getElementById('rules-popup');
    popup.style.display = 'none';
}
</script>