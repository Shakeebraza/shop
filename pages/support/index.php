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

    <div id="support" class="">
    <h2>Support</h2>

    <!-- Always show the ticket form, but disable if there is an open ticket -->
    <div class="ticket-form">
        <h3>Open a New Ticket</h3>
        <form method="POST" action="submit_ticket.php">
            <textarea name="message" id="ticket-message" placeholder="Describe your issue..." rows="4" maxlength="500" required <?php echo $hasOpenTicket ? 'disabled' : ''; ?>></textarea>
            <small id="ticket-char-count">0/500</small>
            <button type="submit" <?php echo $hasOpenTicket ? 'disabled' : ''; ?> id="submit-ticket-btn">Submit Ticket</button>
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
                    <div class="ticket-summary" onclick="toggleConversation(<?php echo htmlspecialchars($ticket['id']); ?>)">
                        <span>Ticket #<?php echo htmlspecialchars($ticket['id']); ?> - <?php echo htmlspecialchars($ticket['created_at']); ?></span>
                        <small>Status: <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?></small>
                    </div>

                    <div id="conversation-<?php echo htmlspecialchars($ticket['id']); ?>" class="conversation-details" style="display: none;">
                        <p><?php echo htmlspecialchars($ticket['message']); ?></p>

                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM support_replies WHERE ticket_id = ? ORDER BY created_at ASC");
                        $stmt->execute([$ticket['id']]);
                        $replies = $stmt->fetchAll();

                        $userReplyCount = 0; // Track consecutive user replies
                        foreach ($replies as $reply) {
                            $messageClass = ($reply['sender'] === 'user') ? 'user-message' : 'admin-message';
                            $senderName = ($reply['sender'] === 'user') ? htmlspecialchars($username) : 'Admin';

                            if ($reply['sender'] === 'user') {
                                $userReplyCount++;
                            } else {
                                $userReplyCount = 0; // Reset after admin reply
                            }
                        ?>
                            <div class="<?php echo $messageClass; ?>">
                                <p class="message-tag"><strong><?php echo htmlspecialchars($senderName); ?>:</strong></p>
                                <p><?php echo htmlspecialchars($reply['message']); ?></p>
                                <small><?php echo htmlspecialchars($reply['created_at']); ?></small>
                            </div>
                        <?php } ?>

                        <?php if ($ticket['status'] === 'open' && $userReplyCount < 3): ?>
    <form method="POST" action="submit_reply.php" class="reply-section" onsubmit="submitReply(event, <?php echo htmlspecialchars($ticket['id']); ?>)">
        <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
        <textarea name="message" id="reply-message-<?php echo htmlspecialchars($ticket['id']); ?>" placeholder="Reply..." rows="2" maxlength="500" required></textarea>
        <small id="reply-char-count-<?php echo htmlspecialchars($ticket['id']); ?>">0/500</small>
        <button type="submit" id="reply-btn-<?php echo htmlspecialchars($ticket['id']); ?>">Send</button>
    </form>
<?php elseif ($userReplyCount >= 3): ?>
    <p class="disabled-message">The conversation has been closed by the Admin. You may proceed by opening a new ticket if further assistance is required.</p>
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