<?php
include_once('../../header.php');
?>
<style>
    .credit-card-item {
    transition: transform 0.6s ease-in-out; /* Apply a smooth transition to the transform */
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 16px;
    text-align: left;
}

thead tr {
    background-color:#0c182f !important; /* Nice blue color */
    color: white;
    text-align: left;
    font-weight: bold;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px 15px;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:hover {
    background-color: #f1f1f1;
}

table button {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.copy-button {
    background-color: #0c182f;
    color: white;
}

.copy-button:hover {
    background-color: #218838;
}

.check-card-button {
    background-color:#0c182f;
    color: white;
}

.check-card-button:hover {
    background-color: #e0a800;
}
.activity-log-table th{
    background-color:#0c182f !important;
}
.copy-button {
    margin-bottom: 5px !important;
}

@media (max-width: 768px) {
    table {
        font-size: 14px;
    }
    td, th {
        padding: 8px 15px;
        text-wrap:nowrap !important;
    }
    
    .main-tbl321{
    width: 100% !important;
    overflow-x:scroll !important;}
    a.buy-button {
    height: 30px !important;
 
}
}
</style>
    <!-- Main Content Area -->
    <div class="main-content">
    <div id="my-cards" class="uuper">
    <h2>My Cards Section</h2>
 <?php if (empty($soldCards)): ?>
        <p>No purchased cards available.</p>
    <?php else: ?>
        <div class="main-tbl321">
    <table>
        <thead>
            <tr>
                <th>Card Number</th>
                <th>Expiration</th>
                <th>CVV</th>
                <th>Name on Card</th>
                <th>Address</th>
                <th>City</th>
                <th>ZIP</th>
                <th>Country</th>
                <th>Phone Number</th>
                <th>Date of Birth</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($soldCards as $card): ?>
                <tr id="card-<?php echo htmlspecialchars($card['id']); ?>">
                    <td><?php echo htmlspecialchars($card['card_number']); ?></td>
                    <td><?php echo htmlspecialchars($card['mm_exp'] . '/' . $card['yyyy_exp']); ?></td>
                    <td><?php echo htmlspecialchars($card['cvv']); ?></td>
                    <td><?php echo htmlspecialchars($card['name_on_card']); ?></td>
                    <td><?php echo htmlspecialchars($card['address']); ?></td>
                    <td><?php echo htmlspecialchars($card['city']); ?></td>
                    <td><?php echo htmlspecialchars($card['zip']); ?></td>
                    <td><?php echo htmlspecialchars($card['country']); ?></td>
                    <td><?php echo htmlspecialchars($card['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($card['date_of_birth']); ?></td>
                    <td>
                        <button class="copy-button" onclick="copyCardInfo(<?php echo htmlspecialchars($card['id']); ?>)">Copy</button>
                        <button class="check-card-button" onclick="checkCard(<?php echo htmlspecialchars($card['id']); ?>)">Check</button>
                        <button class="delete-card-button" onclick="deleteRow(<?php echo htmlspecialchars($card['id']); ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

    <?php endif; ?>  
    <div style="display: none; gap: 20px; padding: 20px; flex-wrap: wrap; justify-content: center;">

<?php if (empty($soldCards)): ?>
    <p>No purchased cards available.</p>
<?php else: ?>
    <?php foreach ($soldCards as $card):
        
        if ($card['card_type'] == 'Mastercard') {
            $background = 'background-image: url(https://wallpapers.com/images/hd/mastercard-logo-black-background-6ud73xlg936woct6.jpg); background-size: cover; background-position: center;';
        } elseif ($card['card_type'] == 'Amex') {
           
            $background = 'background: linear-gradient(45deg, #b8860b, #000000);';
        } elseif ($card['card_type'] == 'Visa') {
            $background = 'background: linear-gradient(45deg, #2E4053, #000000);';
        }else{
            $background = 'background: linear-gradient(45deg,rgb(48, 46, 83),rgb(46, 43, 43));';
            
        }
        ?>
        <div class="card-container" style="perspective: 1000px; display: inline-block; margin: 10px;">
            <div id="card-<?php echo htmlspecialchars($card['id']); ?>" class="credit-card-item" style="width: 420px; height: 265px; position: relative; transform-style: preserve-3d; transition: transform 0.6s; cursor: pointer; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.2);" onclick="flipCard(<?php echo htmlspecialchars($card['id']); ?>)">
                
                <!-- Front of the Card -->
                <div class="card-front" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; <?= $background ?> border-radius: 15px; padding: 25px; display: flex; flex-direction: column; justify-content: space-between; color: white; transform: rotateY(0deg); backface-visibility: hidden;">
                    <!-- Card Info Front -->
                    <h1 style="color: white; font-size: 24px; margin: 0;"><?php echo $card['card_type']; ?></h1>
                        <div style="color: white; font-size: 23px; letter-spacing: 2px; margin-bottom: 20px; font-family: 'Courier New', monospace;">
                            <?php echo $card['card_number']; ?>
                        </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div style="font-size: 12px;">
                            STATE<br>
                            <span style="color: white;"><?php echo $card['country']; ?>/<?php echo $card['city']; ?></span>
                        </div>
                        <div style="font-size: 12px;">
                            EXPIRES END<br>
                            <span><?php echo htmlspecialchars($card['mm_exp'] . '/' . $card['yyyy_exp']); ?></span>
                        </div>
                    </div>

                    <div style="font-size: 16px; margin-bottom: 5px;"><?php echo htmlspecialchars($card['name_on_card']); ?></div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    <div style="font-size: 16px;">Zip <?php echo htmlspecialchars($card['zip']); ?></div>
                    <div style="font-size: 16px;">Cvv <?php echo htmlspecialchars($card['cvv']); ?></div>
                    </div>
                </div>

            
                <div class="card-back" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #0c182f; border-radius: 15px; padding: 25px; color: white; transform: rotateY(180deg); backface-visibility: hidden;">
               

                <button class="copy-button" onclick="copyCardInfo(<?php echo htmlspecialchars($card['id']); ?>)">Copy</button>
                <button class="check-card-button" onclick="checkCard(<?php echo htmlspecialchars($card['id']); ?>)">Check</button>

                <!-- Address, Phone Number, and Date of Birth -->
                <div style="margin-top: 30px;">
                    <div style="font-size: 16px; margin-bottom: 10px;">
                        <strong>Address:</strong> <?php echo htmlspecialchars($card['address']); ?>
                    </div>

                    <div style="font-size: 16px; margin-bottom: 10px;">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($card['phone_number']); ?>
                    </div>

                    <div style="font-size: 16px;">
                        <strong>Date of Birth:</strong> <?php echo htmlspecialchars($card['date_of_birth']); ?>
                    </div>
                </div>
            </div>

            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>


</div>

    <!-- Card Activity Log Section -->
    <div id="card-activity-log">
        <h2>Card Activity Log</h2>
        <div class="main-tbl321">
        <table id="activity-log-table" class="activity-log-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Card Number</th>
                    <th>Date Checked</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($checkedHistory)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No activity logged yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($checkedHistory as $history): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($history['id']); ?></td>
                            <td><?php echo htmlspecialchars($history['card_number']); ?></td>
                            <td><?php echo htmlspecialchars($history['date_checked']); ?></td>
                            <td><?php echo htmlspecialchars($history['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

    </div>
</div>
<?php
include_once('../../footer.php');
?>

<!-- <script>
    const cards = document.querySelectorAll('.credit-card-item');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'rotateY(180deg)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'rotateY(0deg)';
        });
    });
</script> -->

<script>
    // Check for hidden rows on page load
    document.addEventListener('DOMContentLoaded', function () {
        // Get deleted IDs from localStorage
        const deletedIds = JSON.parse(localStorage.getItem('deletedRows')) || [];

        // Hide rows that are marked as deleted
        deletedIds.forEach(id => {
            const row = document.getElementById(`card-${id}`);
            if (row) {
                row.style.display = 'none';
            }
        });
    });

    // Delete row function
    function deleteRow(cardId) {
        if (confirm('Are you sure you want to delete this row?')) {
            // Hide the row
            const row = document.getElementById(`card-${cardId}`);
            if (row) {
                row.style.display = 'none';
            }

            // Save the deleted ID to localStorage
            const deletedIds = JSON.parse(localStorage.getItem('deletedRows')) || [];
            if (!deletedIds.includes(cardId)) {
                deletedIds.push(cardId);
                localStorage.setItem('deletedRows', JSON.stringify(deletedIds));
            }
        }
    }
</script>


