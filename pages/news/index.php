<?php
include_once('../../header.php');
?>
<!-- Main Content Area -->
<div class="main-content">

    <div id="news" class="uuper">
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

<div id="rules-popup" class="popup-modal" style="display: none;">
    <div class="popup-content" style="position: absolute;top: 50%;right: 20%;">
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <p class="message"></p>
    </div>
</div>
<?php
include_once('../../footer.php');
?>
<script>
function checkAccountStatus() {
    $.ajax({
        url: 'check_status.php',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'inactive') {
                $('.message').html(response.message);
                $('#rules-popup').fadeIn();
            }
        },
        error: function() {
            console.error('Failed to check account status.');
        }
    });
}

function closeRulesPopup() {
    $('#rules-popup').fadeOut();
}

$(document).ready(function() {
    checkAccountStatus();
});
</script>