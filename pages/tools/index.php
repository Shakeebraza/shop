<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">


        <div id="tools" class="uuper">
            <h2>Tools Section</h2>
            <?php if (empty($files['Tools'])): ?>
                <p>No files available in the Tools section.</p>
            <?php else: ?>
                <?php foreach ($files['Tools'] as $file): ?>
                    <div class="tool-item">
                        <h3><?php echo htmlspecialchars($file['name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>
                        <p>Price: $<?php echo number_format($file['price'], 2); ?></p>
                        <a href="buy_tool.php?tool_id=<?php echo $file['id']; ?>&section=tools" onclick="return confirm('Are you sure you want to buy this item?');" style="background-color: #28a745; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; margin-top: 10px; display: inline-block;">Buy</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>


    </div>
</div>

<?php
include_once('../../footer.php');
?>
<script>
    console.log('hello');
</script>
</body>
</html>