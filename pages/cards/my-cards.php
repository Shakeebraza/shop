<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">
    <div id="my-cards" class="">
    <h2>My Cards Section</h2>
    <?php if (empty($soldCards)): ?>
        <p>No purchased cards available.</p>
    <?php else: ?>
        <?php foreach ($soldCards as $card): ?>
            <div id="card-<?php echo htmlspecialchars($card['id']); ?>" class="credit-card-item">
                <div class="info-field"><strong>Card Number:</strong> <?php echo htmlspecialchars($card['card_number']); ?></div>
                <div class="info-field"><strong>Expiration:</strong> <?php echo htmlspecialchars($card['mm_exp'] . '/' . $card['yyyy_exp']); ?></div>
                <div class="info-field"><strong>CVV:</strong> <?php echo htmlspecialchars($card['cvv']); ?></div>
                <div class="info-field"><strong>Name on Card:</strong> <?php echo htmlspecialchars($card['name_on_card']); ?></div>
                <div class="info-field"><strong>Address:</strong> <?php echo htmlspecialchars($card['address']); ?></div>
                <div class="info-field"><strong>City:</strong> <?php echo htmlspecialchars($card['city']); ?></div>
                <div class="info-field"><strong>ZIP:</strong> <?php echo htmlspecialchars($card['zip']); ?></div>
                <div class="info-field"><strong>Country:</strong> <?php echo htmlspecialchars($card['country']); ?></div>
                <div class="info-field"><strong>Phone Number:</strong> <?php echo htmlspecialchars($card['phone_number']); ?></div>
                <div class="info-field"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($card['date_of_birth']); ?></div>
                <button class="copy-button" onclick="copyCardInfo(<?php echo htmlspecialchars($card['id']); ?>)">Copy</button>
                <button class="check-card-button" onclick="checkCard(<?php echo htmlspecialchars($card['id']); ?>)">Check</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Card Activity Log Section -->
    <div id="card-activity-log">
        <h2>Card Activity Log</h2>
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
<?php
include_once('../../footer.php');
?>