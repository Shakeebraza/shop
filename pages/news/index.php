<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">

        <!-- Display success message if a purchase was successful -->
        <?php if ($successMessage): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <!-- Group 1: News, Tools, Leads, Pages, My Orders -->
        <div id="news" class="section">
            <h2>News Section</h2>
            <?php foreach ($newsItems as $news): ?>
                <div class="news-item">
                    <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($news['content'])); ?></p>
                    <small>Published on: <?php echo $news['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>


    </div>
</div>
<?php
include_once('../../footer.php');
?>