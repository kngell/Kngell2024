<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Custom CSS-------->
<style>
/* Results Page Styles */
.results-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    height: 80vh;
    overflow-y: auto;

}

.results-header {
    background: <?=$dry_run ? '#17a2b8': '#28a745'?>;
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
    margin-bottom: 0;
}

.results-header h2 {
    margin: 0;
    font-size: 20px;
}

.results-header h2 i {
    margin-right: 10px;
}

.results-body {
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 8px 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

}

.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid;
}

.alert-info {
    background: #d1ecf1;
    border-left-color: #17a2b8;
    color: #0c5460;
}

.alert-success {
    background: #d4edda;
    border-left-color: #28a745;
    color: #155724;
}

.alert-warning {
    background: #fff3cd;
    border-left-color: #ffc107;
    color: #856404;
}

.alert-danger {
    background: #f8d7da;
    border-left-color: #dc3545;
    color: #721c24;
}

.stats-row {
    display: flex;
    gap: 20px;
    margin: 30px 0;
    flex-wrap: wrap;
}

.stat-card {
    flex: 1;
    min-width: 200px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-card.info {
    border-top: 4px solid #17a2b8;
}

.stat-card.success {
    border-top: 4px solid #28a745;
}

.stat-card.danger {
    border-top: 4px solid #dc3545;
}

.stat-number {
    font-size: 36px;
    font-weight: bold;
    margin: 10px 0;
    color: #333;
}

.stat-label {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-size {
    color: #888;
    font-size: 13px;
}

.file-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin: 20px 0;
}

.file-item {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    background: #fafafa;
}

.file-item:hover {
    background: #f0f0f0;
}

.file-item:last-child {
    border-bottom: none;
}

.file-item .file-name {
    font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
    font-size: 14px;
    margin-bottom: 4px;
}

.file-item .file-name i {
    color: #666;
    margin-right: 8px;
    width: 16px;
}

.file-details {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: #666;
}

.file-more {
    text-align: center;
    padding: 15px;
    color: #888;
    font-style: italic;
    background: #f9f9f9;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
    margin-right: 10px;
    margin-bottom: 10px;
}

.btn:hover {
    background: #5a6268;
}

.btn-primary {
    background: #007bff;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-danger {
    background: #dc3545;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-outline-secondary {
    background: transparent;
    border: 1px solid #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 18px;
}

.alert i,
.btn i {
    margin-right: 8px;
}

.form-inline {
    display: inline-block;
}
</style>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">

    <div class="results-container card card-1">
        <div class="results-header">
            <h2>
                <i class="fas <?= $dry_run ? 'fa-eye' : 'fa-trash' ?>"></i>
                <?= $dry_run ? 'Dry Run Results' : 'Cleanup Results' ?>
            </h2>
        </div>

        <div class="results-body">
            <?php if ($dry_run): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Dry Run Completed:</strong> No files were actually deleted. Below is what would be deleted if
                you run the cleanup.
            </div>

            <div class="stats-row">
                <div class="stat-card info">
                    <div class="stat-label">Files to Delete</div>
                    <div class="stat-number"><?= $summary['candidate_count'] ?? 0 ?></div>
                    <div class="stat-size"><?= $summary['candidate_size_formatted'] ?? '0 B' ?></div>
                </div>
            </div>

            <?php if (!empty($candidates)): ?>
            <h4>Files That Would Be Deleted:</h4>
            <div class="file-list">
                <?php
                        // Sort by age (oldest first)
                        usort($candidates, function ($a, $b) {
                            return ($a['modified_at'] ?? 0) <=> ($b['modified_at'] ?? 0);
                        });

                $count = 0;
                foreach ($candidates as $file):
                    if ($count >= 50) {
                        break;
                    }
                    $filename = basename($file['path'] ?? '');
                    $size = $file['size'] ?? 0;
                    $age = isset($file['modified_at']) ?
                        round((time() - $file['modified_at']) / 86400, 1) :
                        'unknown';
                    ?>
                <div class="file-item">
                    <div class="file-name">
                        <i class="fas fa-file"></i>
                        <?= htmlspecialchars($filename) ?>
                    </div>
                    <div class="file-details">
                        <span class="file-size">
                            <?= $this->formatBytes($size) ?>
                        </span>
                        <span class="file-age">
                            <?= $age ?> days old
                        </span>
                    </div>
                </div>
                <?php
                        $count++;
                endforeach;

                if (count($candidates) > 50): ?>
                <div class="file-more">
                    ... and <?= count($candidates) - 50 ?> more files
                </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> These <?= $summary['candidate_count'] ?? 0 ?> files will be permanently
                deleted if you run the cleanup without dry run.
            </div>

            <div class="actions">
                <a href="/upload-cleanup/index" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Cleanup
                </a>

                <form method="POST" action="/upload-cleanup/index/cleanup" class="form-inline">
                    <input type="hidden" name="dry_run" value="0">
                    <input type="hidden" name="max_age_days" value="<?= $_POST['max_age_days'] ?? 7 ?>">
                    <input type="hidden" name="clean_temp" value="<?= $_POST['clean_temp'] ?? 1 ?>">
                    <input type="hidden" name="temp_max_age_hours" value="<?= $_POST['temp_max_age_hours'] ?? 1 ?>">
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to PERMANENTLY DELETE <?= $summary['candidate_count'] ?? 0 ?> files? This cannot be undone!')">
                        <i class="fas fa-trash"></i> Delete These Files
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>Great news!</strong> No files need to be cleaned up at this time.
            </div>

            <div class="actions">
                <a href="/upload-cleanup/index" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Cleanup
                </a>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>Cleanup Completed Successfully!</strong>
            </div>

            <div class="stats-row">
                <div class="stat-card success">
                    <div class="stat-label">Files Deleted</div>
                    <div class="stat-number"><?= $summary['deleted_count'] ?? 0 ?></div>
                    <div class="stat-size"><?= $summary['deleted_size_formatted'] ?? '0 B' ?></div>
                </div>

                <?php if (!empty($summary['failed_count'])): ?>
                <div class="stat-card danger">
                    <div class="stat-label">Failed Deletions</div>
                    <div class="stat-number"><?= $summary['failed_count'] ?? 0 ?></div>
                    <div class="stat-size">Permission issues</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="actions">
                <a href="/upload-cleanup/index" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Cleanup
                </a>
                <a href="/upload-cleanup/index/refresh" class="btn btn-outline-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh Stats
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<script>
// Add any JavaScript needed for results page
document.addEventListener('DOMContentLoaded', function() {
    // Make file list rows clickable for more details (optional)
    document.querySelectorAll('.file-item').forEach(function(item) {
        item.addEventListener('click', function() {
            this.style.backgroundColor = this.style.backgroundColor === 'rgb(240, 240, 240)' ?
                '#fafafa' : '#f0f0f0';
        });
    });
});
</script>
<?php $this->end(); ?>