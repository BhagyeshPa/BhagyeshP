<?php
// Fetch 4 Random Tips
$conn_tips = getDBConnection();
$tips_stmt = $conn_tips->query("SELECT category, tip_content FROM knowledge_tips ORDER BY RAND() LIMIT 4");
$tips_list = $tips_stmt->fetchAll(PDO::FETCH_ASSOC);

function getIcon($cat)
{
    switch ($cat) {
        case 'GMP':
            return 'fa-flask';
        case '21CFR':
            return 'fa-gavel';
        case 'SOP':
            return 'fa-file-alt';
        case 'Incident':
            return 'fa-exclamation-triangle';
        default:
            return 'fa-lightbulb';
    }
}
?>
<aside class="right-panel">
    <div class="knowledge-header">
        <h3>Knowledge Sharing</h3>
        <small class="text-muted">Always Upgrade Yourself</small>
    </div>

    <!-- Static Navigation Links (Based on screenshot) -->


    <!-- Dynamic Tips -->
    <?php foreach ($tips_list as $tip):
        $cat = htmlspecialchars($tip['category']);
        $content = htmlspecialchars($tip['tip_content']);
        ?>
        <div class="knowledge-card cat-<?php echo str_replace(' ', '', $cat); ?>">
            <div class="card-icon"><i class="fas <?php echo getIcon($cat); ?>"></i></div>
            <div class="card-content">
                <h4>
                    <?php echo $cat; ?> Tip
                </h4>
                <p>
                    <?php echo substr($content, 0, 80) . (strlen($content) > 80 ? '...' : ''); ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>

</aside>