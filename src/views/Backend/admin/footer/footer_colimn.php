<!-- admin/footer-menu/index.html -->
<div class="admin-section">
    <div class="admin-header">
        <h1>Footer Menu Management</h1>
        <a href="/admin/footer-menu/create-column" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Column
        </a>
    </div>

    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-value">2</div>
            <div class="stat-label">Active Columns</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">12</div>
            <div class="stat-label">Menu Items</div>
        </div>
    </div>

    <div class="footer-columns-grid">
        <!-- Service Column Card -->
        <div class="footer-column-card">
            <div class="card-header">
                <h3>Services</h3>
                <div class="actions">
                    <a href="/admin/footer-menu/items/1" class="btn-icon" title="Manage Items">
                        <i class="fas fa-list-ul"></i>
                    </a>
                    <a href="/admin/footer-menu/edit-column/1" class="btn-icon" title="Edit Column">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteColumn(1)" class="btn-icon danger" title="Delete Column">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Key:</span>
                    <span class="info-value">services</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="badge active">Active</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sort Order:</span>
                    <span class="info-value">1</span>
                </div>
            </div>
            <div class="card-footer">
                <a href="/admin/footer-menu/items/1" class="btn btn-sm btn-outline">
                    Manage Items (6)
                </a>
            </div>
        </div>

        <!-- Assistance Column Card -->
        <div class="footer-column-card">
            <div class="card-header">
                <h3>Assistance to the buyer</h3>
                <div class="actions">
                    <a href="/admin/footer-menu/items/2" class="btn-icon" title="Manage Items">
                        <i class="fas fa-list-ul"></i>
                    </a>
                    <a href="/admin/footer-menu/edit-column/2" class="btn-icon" title="Edit Column">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteColumn(2)" class="btn-icon danger" title="Delete Column">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Key:</span>
                    <span class="info-value">assistance</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="badge active">Active</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sort Order:</span>
                    <span class="info-value">2</span>
                </div>
            </div>
            <div class="card-footer">
                <a href="/admin/footer-menu/items/2" class="btn btn-sm btn-outline">
                    Manage Items (6)
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteColumn(id) {
    if (confirm('Delete this column? All menu items will also be deleted.')) {
        fetch(`/admin/footer-menu/delete-column/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            if (response.ok) window.location.reload();
        });
    }
}
</script>