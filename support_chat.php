<?php
session_start();
require 'config.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php?redirect=panel.php");
    exit();
}


$stmt = $pdo->prepare("
    SELECT t.id AS ticket_id, t.created_at AS ticket_created_at, t.unread, t.status, t.admin_unread,
           u.username AS user_username, m.sender,m.is_read, m.message AS content, m.created_at AS message_created_at
    FROM support_tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN support_replies m ON t.id = m.ticket_id
    ORDER BY m.created_at DESC
");
$stmt->execute();
$rawTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tickets = [];
foreach ($rawTickets as $row) {
    $ticketId = $row['ticket_id'];

    if (!isset($tickets[$ticketId])) {
        $tickets[$ticketId] = [];
    }
    
    $tickets[$ticketId][] = $row;
}





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Chat</title>
    <link rel="stylesheet" href="css/support_chat.css">
</head>
<style>
/* Ticket grid - 3 columns layout */
.ticket-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    padding: 20px;
    background-color: #f5f5f5;
    width: 100%;
}

/* Ticket wrapper */
.ticket-wrapper {
    position: relative;
    transition: transform 0.3s ease-in-out;
    cursor: pointer;
}

.ticket-wrapper:hover {
    transform: translateY(-2px);
}

/* Ticket card */
.card {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 15px;
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: scale(1.01);
}

/* Ticket conversation */
.ticket-conversation {
    display: none;
    grid-column: span 3;
    /* Ensure conversation spans full width */
    position: relative;
    width: 94%;
    margin-top: 5px;
    background: #fff;
    border: 1px solid #e0e0e0;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    max-height: 300px;
    overflow-y: auto;
    animation: fadeIn 0.3s ease-in-out;
}

/* Fade-in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .ticket-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .ticket-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<body>
    <div id="support-chat">
        <h2>Support Tickets</h2>

        <div class="ticket-grid">
            <?php if (!empty($tickets)): ?>
            <?php
// // Sort the tickets array
// usort($tickets, function ($a, $b) {
//     // Sort by admin_unread (1 comes first)
//     return $b[0]['admin_unread'] <=> $a[0]['admin_unread'];
// });
// var_dump($tickets);
// Now loop through the sorted tickets
foreach ($tickets as $ticketId => $messages): ?>
            <div class="ticket-wrapper">
                <!-- Ticket Card -->
                <div class="ticket-summary card" data-ticket-id="<?php echo htmlspecialchars($ticketId); ?>"
                    onclick="toggleConversation(<?php echo htmlspecialchars($ticketId); ?>)">
                    <div class="card-content">
                        <?php if (strtolower($messages[0]['status']) === 'closed'): ?>
                        <span class="status-closed">CLOSED</span>
                        <?php endif; ?>


                        <?php if (!empty($messages[0]['admin_unread']) && $messages[0]['admin_unread'] == 0): ?>
                        <span class="unread-notification" style="color:red;">(1) Unreadd</span>
                        <?php endif; ?>
                        <?php
                         
                            if (!empty($messages)) {
                            
                                $lastMessage = $messages[1];

                                if ($lastMessage['sender'] == "user" && $lastMessage['is_read'] == 0) {
                                    ?>
                        <span class="unread-notification" style="color:red;">(1) Unread</span>
                        <?php
                                }
                            }
                            ?>

                        <h3 class="ticket-title">
                            Ticket #<?php echo htmlspecialchars($ticketId); ?>
                        </h3>
                        <p class="ticket-user">
                            <strong>User Name:</strong> <span
                                style="color:green;"><?php echo htmlspecialchars($messages[0]['user_username']); ?>
                            </span>
                        </p>
                        <p class="ticket-date">
                            <?php echo htmlspecialchars($messages[0]['ticket_created_at']); ?>
                        </p>
                    </div>
                </div>

                <!-- Ticket Conversation -->
                <div class="ticket-conversation card" id="conversation-<?php echo htmlspecialchars($ticketId); ?>"
                    style="display: none;">
                    <div class="card-content">
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

                        <div class="reply-control-row">
                            <?php if ($messages[0]['status'] === 'open'): ?>
                            <form method="POST" action="admin_reply.php" class="reply-form">
                                <input type="hidden" name="ticket_id"
                                    value="<?php echo htmlspecialchars($ticketId); ?>">
                                <textarea name="message" placeholder="Type your reply here..." rows="3" maxlength="500"
                                    required></textarea>
                                <button type="submit">Send Reply</button>
                            </form>
                            <?php endif; ?>

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
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <center>
                <p>No support tickets found.</p>
            </center>
            <?php endif; ?>
        </div>

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