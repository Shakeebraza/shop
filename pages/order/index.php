<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">

    <div id="my-orders" class="uuper">
            <h2>My Orders</h2>
            <?php if (empty($orders)): ?>
                <p>You haven't made any purchases yet.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($orders as $order): ?>
                        <div class="tool-item">
                            <h3><?php echo htmlspecialchars($order['name']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($order['description'])); ?></p>
                            <p>Price: $<?php echo number_format($order['price'], 2); ?></p>
                            <a href="download_tool.php?tool_id=<?php echo $order['tool_id']; ?>" class="download-button">Download</a>
                            <a href="delete_order.php?tool_id=<?php echo $order['tool_id']; ?>&section=my-orders" 
                               onclick="return confirm('Are you sure you want to delete this item?');" 
                               class="delete-button">Delete</a>
                        </div>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include_once('../../footer.php');
?>