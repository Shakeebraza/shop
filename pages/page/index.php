<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">


    <div id="pages" class="">
            <h2>Pages Section</h2>
            <?php if (empty($files['Pages'])): ?>
                <p>No files available in the Pages section.</p>
            <?php else: ?>
                <?php foreach ($files['Pages'] as $file): ?>
                    <div class="tool-item">
                        <h3><?php echo htmlspecialchars($file['name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>
                        <p>Price: $<?php echo number_format($file['price'], 2); ?></p>
                        <a href="buy_tool.php?tool_id=<?php echo $file['id']; ?>&section=pages" 
                           onclick="return confirm('Are you sure you want to buy this item?');" 
                           style="background-color: #28a745; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;" >Buy </a>
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