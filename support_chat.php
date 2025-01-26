<?php
session_start();
require 'config.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php?redirect=panel.php");
    exit();
}


$stmt = $pdo->prepare("
    SELECT t.id AS ticket_id, t.created_at AS ticket_created_at,unread, t.status, t.admin_unread,
           u.username AS user_username, m.sender, m.message AS content, m.created_at AS message_created_at
    FROM support_tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN support_replies m ON t.id = m.ticket_id
    ORDER BY t.created_at DESC, m.created_at ASC
");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC); 

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Chat</title>
    <link rel="stylesheet" href="css/support_chat.css">
</head>

<body>
    <div id="support-chat">
        <h2>Support Tickets</h2>

        <div class="ticket-list">
            <?php if (!empty($tickets)): ?>
            <?php foreach ($tickets as $ticketId => $messages): ?>
            <!-- Individual ticket-summary div for each ticket -->
            <div class="ticket-summary" data-ticket-id="<?php echo htmlspecialchars($ticketId); ?>"
                onclick="toggleConversation(<?php echo htmlspecialchars($ticketId); ?>)">
                <div class="ticket-item">
                    <span>
                        Ticket #<?php echo htmlspecialchars($ticketId); ?> -
                        <?php echo htmlspecialchars($messages[0]['ticket_created_at']); ?> -
                        User: <?php echo htmlspecialchars($messages[0]['user_username']); ?>
                        <?php if (strtolower($messages[0]['status']) === 'closed'): ?>
                        <span style="color: red; font-weight: bold;"> - CLOSED</span>
                        <?php endif; ?>
                        <?php if (!empty($messages[0]['admin_unread']) && $messages[0]['admin_unread'] == 1): ?>
                        <span style="color: red; font-weight: bold;">(1)</span>
                        <?php endif; ?>
                        <?php
                        if($messages[0]['unread'] == 0){
                            echo'<span style="color: red; font-weight: bold;">(1)</span>';
                        }
                        ?>
                    </span>
                </div>
            </div>

            <!-- Conversation section for each ticket (initially hidden) -->
            <div class="ticket-conversation" id="conversation-<?php echo htmlspecialchars($ticketId); ?>"
                style="display: none;">
                <?php foreach ($messages as $message): ?>
                <div class="<?php echo $message['sender'] === 'admin' ? 'admin-message' : 'user-message'; ?>">
                    <p class="message-tag">
                        <strong>
                            <?php echo ucfirst($message['sender'] === 'user' ? htmlspecialchars($messages[0]['user_username']) : 'Admin'); ?>:
                        </strong>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                    <small><?php echo htmlspecialchars($message['message_created_at']); ?></small>
                </div>
                <?php endforeach; ?>



                <!-- Reply and Control Row -->
                <div class="reply-control-row">
                    <?php if ($messages[0]['status'] === 'open'): ?>
                    <!-- Left Side: Reply Form (only if ticket is open) -->
                    <form method="POST" action="admin_reply.php" class="reply-form">
                        <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticketId); ?>">
                        <textarea name="message" placeholder="Type your reply here..." rows="3" maxlength="500"
                            required></textarea>
                        <button type="submit">Send Reply</button>
                    </form>
                    <?php endif; ?>

                    <!-- Right Side: Admin Controls -->
                    <div class="admin-controls">
                        <?php if ($messages[0]['status'] === 'open'): ?>
                        <button onclick="closeTicket(<?php echo htmlspecialchars($ticketId); ?>)"
                            class="close-ticket">Close Ticket</button>
                        <?php endif; ?>
                        <button onclick="deleteTicket(<?php echo htmlspecialchars($ticketId); ?>)"
                            class="delete-ticket">Delete Ticket</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <center>
                <p>No support tickets found.</p>
            </center>
            <?php endif; ?>
        </div>

        <!-- Back Button to go to panel.php, placed after the last ticket -->
        <center><a href="panel.php" class="back-button">Back to Selection</a></center>
    </div>

    <script src="js/support_chat.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function() {

        $.ajax({
            url: 'ajax/update_unread.php', // The PHP file to handle the request
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Set unread count to 0 in the UI
                    $('#unread-count').text(0);
                } else {
                    console.error('Error:', response.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });



    });
    </script>
</body>

</html>