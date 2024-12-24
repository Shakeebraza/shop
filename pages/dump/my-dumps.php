<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">
    <div id="my-dumps" class="">
    <h2>My Dumps Section</h2>
    <?php if (empty($soldDumps)): ?>
        <p>No purchased dumps available.</p>
    <?php else: ?>
        <?php foreach ($soldDumps as $dump): ?>
            <div id="dump-<?php echo htmlspecialchars($dump['id']); ?>" class="dump-item">
                <div class="info-field"><strong>Track 1:</strong> <?php echo htmlspecialchars($dump['track1']); ?></div>
                <div class="info-field"><strong>Track 2:</strong> <?php echo htmlspecialchars($dump['track2']); ?></div>
                <div class="info-field"><strong>PIN:</strong> <?php echo htmlspecialchars($dump['pin'] ?: 'No'); ?></div>
                <div class="info-field"><strong>Country:</strong> <?php echo htmlspecialchars($dump['country']); ?></div>
                <button class="copy-button" onclick="copyDumpInfo(<?php echo htmlspecialchars($dump['id']); ?>)">Copy</button>
                <button class="check-dump-button" onclick="checkDump(<?php echo htmlspecialchars($dump['id']); ?>)">Check</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Dumps Activity Log Section -->
    <div id="dumps-activity-log">
        <h2>Dumps Activity Log</h2>
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
                <?php if (empty($checkedDumpsHistory)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No activity logged yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($checkedDumpsHistory as $history): ?>
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