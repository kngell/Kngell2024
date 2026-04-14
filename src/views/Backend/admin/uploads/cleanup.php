<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Custom CSS-------->
<style>
/* Cleanup Page Styles */
.cleanup-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    height: 80vh;
    overflow-y: auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.header h1 {
    margin: 0;
    color: #333;
    font-size: 24px;
}

.header h1 i {
    color: #666;
    margin-right: 10px;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
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

.btn-danger {
    background: #dc3545;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-info {
    background: #17a2b8;
}

.btn-info:hover {
    background: #138496;
}

.btn-secondary {
    background: #6c757d;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-card.light {
    background: #f8f9fa;
}

.stat-card.warning {
    background: #fff3cd;
    border-color: #ffeaa7;
}

.stat-card.danger {
    background: #f8d7da;
    border-color: #f5c6cb;
}

.stat-card.success {
    background: #d4edda;
    border-color: #c3e6cb;
}

.stat-card.info {
    background: #d1ecf1;
    border-color: #bee5eb;
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
}

.stat-size {
    color: #888;
    font-size: 12px;
}

.form-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 30px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-header {
    background: #17a2b8;
    color: white;
    padding: 15px 20px;
    margin: 0;
}

.form-header h2 {
    margin: 0;
    font-size: 18px;
}

.form-header h2 i {
    margin-right: 10px;
}

.form-body {
    padding: 20px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
}

.form-column {
    flex: 1;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
}

.column-header {
    background: rgba(220, 53, 69, 0.1);
    padding: 12px 15px;
    border-bottom: 1px solid #dee2e6;
}

.column-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.column-header h3 i {
    margin-right: 8px;
    color: #dc3545;
}

.column-body {
    padding: 15px;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #495057;
}

.input-group {
    display: flex;
}

.input-group input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-right: none;
    border-radius: 4px 0 0 4px;
    font-size: 14px;
}

.input-group span {
    padding: 8px 12px;
    background: #e9ecef;
    border: 1px solid #ced4da;
    border-left: none;
    border-radius: 0 4px 4px 0;
    color: #495057;
    font-size: 14px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkbox-group input[type="checkbox"] {
    margin: 0;
}

.alert {
    padding: 12px 15px;
    border-radius: 4px;
    margin: 15px 0;
    font-size: 14px;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.code {
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
    margin-right: 10px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked+.slider {
    background-color: #2196F3;
}

input:checked+.slider:before {
    transform: translateX(26px);
}

.form-text {
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
}
</style>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <div class="card card-1">
        <div class="cleanup-container">
            <div class="header">
                <h1><i class="fas fa-trash-alt"></i>Uploads Cleanup</h1>
                <a href="/upload-cleanup/refresh" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh Stats
                </a>
            </div>

            <!-- Stats Section -->
            <div class="stats-grid">
                <div class="stat-card light">
                    <div class="stat-label">Total Files</div>
                    <div class="stat-number"><?= $stats['total_files'] ?? 0 ?></div>
                    <div class="stat-size"><?= $stats['total_size_formatted'] ?? '0 B' ?></div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-label">Temp Files</div>
                    <div class="stat-number"><?= $stats['temp_files'] ?? 0 ?></div>
                    <div class="stat-size"><?= $stats['temp_size_formatted'] ?? '0 B' ?></div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-label">Orphan Files</div>
                    <div class="stat-number"><?= $stats['orphan_candidates'] ?? 0 ?></div>
                    <div class="stat-size"><?= $stats['orphan_size_formatted'] ?? '0 B' ?></div>
                </div>

                <div class="stat-card success">
                    <div class="stat-label">Valid Files</div>
                    <div class="stat-number">
                        <?= ($stats['total_files'] ?? 0) - ($stats['orphan_candidates'] ?? 0) - ($stats['temp_files'] ?? 0) ?>
                    </div>
                    <div class="stat-size">In Database</div>
                </div>
            </div>

            <p><strong>Uploads Directory:</strong> <code
                    class="code"><?= htmlspecialchars($uploads_dir ?? 'Not set') ?></code></p>
            <p><small>Last updated: <?= date('Y-m-d H:i:s') ?></small></p>

            <!-- Cleanup Form -->
            <div class="form-section">
                <div class="form-header">
                    <h2><i class="fas fa-broom"></i>Cleanup Configuration</h2>
                </div>
                <div class="form-body">
                    <form method="POST" action="/upload-cleanup/cleanup" id="cleanup-form">
                        <!-- Dry Run Option -->
                        <div style="margin-bottom: 30px;">
                            <div class="checkbox-group">
                                <label class="switch">
                                    <input type="checkbox" name="dry_run" id="dry_run" checked>
                                    <span class="slider"></span>
                                </label>
                                <label for="dry_run" style="font-weight: bold; font-size: 16px;">
                                    <i class="fas fa-eye"></i> Dry Run Mode
                                </label>
                            </div>
                            <div class="form-text">
                                Preview what would be deleted without actually deleting files. Always run this first!
                            </div>
                        </div>

                        <div class="form-row">
                            <!-- Orphan Files Settings -->
                            <div class="form-column">
                                <div class="column-header">
                                    <h3><i class="fas fa-file-exclamation"></i>Orphan Files</h3>
                                </div>
                                <div class="column-body">
                                    <div class="form-group">
                                        <label for="max_age_days">Delete orphans older than (days):</label>
                                        <div class="input-group">
                                            <input type="number" id="max_age_days" name="max_age_days"
                                                value="<?= $max_age_days ?? 7 ?>" min="0" max="365">
                                            <span>days</span>
                                        </div>
                                        <div class="form-text">
                                            Files not in database will be deleted if older than this.
                                            Set to 0 to delete all orphans.
                                        </div>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Note:</strong> Orphan files are those not referenced in your database.
                                    </div>
                                </div>
                            </div>

                            <!-- Temp Files Settings -->
                            <div class="form-column">
                                <div class="column-header" style="background: rgba(255, 193, 7, 0.1);">
                                    <h3><i class="fas fa-clock"></i>Temporary Files</h3>
                                </div>
                                <div class="column-body">
                                    <div class="form-group">
                                        <div class="checkbox-group">
                                            <input type="checkbox" name="clean_temp" id="clean_temp" checked>
                                            <label for="clean_temp">Clean temporary files</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="temp_max_age_hours">Delete temp files older than:</label>
                                        <div class="input-group">
                                            <input type="number" id="temp_max_age_hours" name="temp_max_age_hours"
                                                value="<?= $temp_max_age_hours ?? 1 ?>" min="0" max="720"
                                                <?= (($clean_temp ?? true) ? '' : 'disabled') ?>>
                                            <span>hours</span>
                                        </div>
                                        <div class="form-text">
                                            Temp files in /temp/ directory older than this will be deleted.
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Info:</strong> Temp files are usually safe to delete.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg" id="run-cleanup">
                                <i class="fas fa-play"></i> Run Cleanup
                            </button>

                            <button type="button" class="btn btn-secondary" id="reset-form">
                                <i class="fas fa-undo"></i> Reset to Defaults
                            </button>
                        </div>

                        <!-- Warning Alert -->
                        <div class="alert alert-danger" style="margin-top: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This action cannot be undone! Files will be permanently deleted
                            when
                            not in dry run mode.
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="form-section">
                <div class="form-header" style="background: #6c757d;">
                    <h2><i class="fas fa-history"></i>Quick Actions</h2>
                </div>
                <div class="form-body">
                    <div class="quick-actions">
                        <form method="POST" action="/upload-cleanup/cleanup" style="display: inline;">
                            <input type="hidden" name="dry_run" value="1">
                            <input type="hidden" name="max_age_days" value="7">
                            <input type="hidden" name="clean_temp" value="1">
                            <input type="hidden" name="temp_max_age_hours" value="1">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search"></i> Check 7+ Day Orphans
                            </button>
                        </form>

                        <form method="POST" action="/upload-cleanup/cleanup" style="display: inline;">
                            <input type="hidden" name="dry_run" value="1">
                            <input type="hidden" name="max_age_days" value="30">
                            <input type="hidden" name="clean_temp" value="1">
                            <input type="hidden" name="temp_max_age_hours" value="1">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search"></i> Check 30+ Day Orphans
                            </button>
                        </form>

                        <form method="POST" action="/upload-cleanup/cleanup" style="display: inline;">
                            <input type="hidden" name="dry_run" value="1">
                            <input type="hidden" name="max_age_days" value="0">
                            <input type="hidden" name="clean_temp" value="1">
                            <input type="hidden" name="temp_max_age_hours" value="1">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-eye"></i> Check All Orphans
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger" id="quick-delete-temp">
                            <i class="fas fa-fire"></i> Delete All Temp Files
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------Custom JavaScript--------->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle temp hours input based on checkbox
    const cleanTempCheckbox = document.getElementById('clean_temp');
    const tempHoursInput = document.getElementById('temp_max_age_hours');

    if (cleanTempCheckbox && tempHoursInput) {
        cleanTempCheckbox.addEventListener('change', function() {
            tempHoursInput.disabled = !this.checked;
        });
    }

    // Reset form to defaults
    const resetBtn = document.getElementById('reset-form');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            document.getElementById('dry_run').checked = true;
            document.getElementById('max_age_days').value = 7;
            document.getElementById('clean_temp').checked = true;
            document.getElementById('temp_max_age_hours').value = 1;
            if (tempHoursInput) {
                tempHoursInput.disabled = false;
            }
        });
    }

    // Quick delete temp files
    const quickDeleteBtn = document.getElementById('quick-delete-temp');
    if (quickDeleteBtn) {
        quickDeleteBtn.addEventListener('click', function() {
            if (confirm(
                    'Are you sure you want to delete ALL temporary files (regardless of age)? This action cannot be undone!'
                )) {
                const form = document.getElementById('cleanup-form');
                form.querySelector('[name="dry_run"]').checked = false;
                form.querySelector('[name="max_age_days"]').value = 365; // Don't delete orphans
                form.querySelector('[name="clean_temp"]').checked = true;
                form.querySelector('[name="temp_max_age_hours"]').value = 0; // Delete all temp files
                form.submit();
            }
        });
    }

    // Confirm before actual deletion (not dry run)
    const cleanupForm = document.getElementById('cleanup-form');
    if (cleanupForm) {
        cleanupForm.addEventListener('submit', function(e) {
            const isDryRun = document.getElementById('dry_run').checked;
            const maxAgeDays = parseInt(document.getElementById('max_age_days').value);
            const cleanTemp = document.getElementById('clean_temp').checked;
            const tempMaxAge = parseInt(document.getElementById('temp_max_age_hours').value);

            if (!isDryRun) {
                let message = 'You are about to PERMANENTLY DELETE files:\n\n';

                if (maxAgeDays === 0) {
                    message += '• ALL orphan files (not in database)\n';
                } else if (maxAgeDays > 0) {
                    message += `• Orphan files older than ${maxAgeDays} days\n`;
                }

                if (cleanTemp) {
                    if (tempMaxAge === 0) {
                        message += '• ALL temporary files\n';
                    } else {
                        message += `• Temporary files older than ${tempMaxAge} hours\n`;
                    }
                }

                message += '\nThis action cannot be undone!\n\nAre you sure you want to continue?';

                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});
</script>
<?php $this->end(); ?>