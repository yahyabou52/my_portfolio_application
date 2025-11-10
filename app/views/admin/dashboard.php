<!-- Modern NextJS-Style Dashboard -->
<div class="dashboard-modern">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <div class="dashboard-title">
                        <h1 class="h2 mb-1">Dashboard</h1>
                        <p class="text-muted mb-0">Welcome back! Here's what's happening with your portfolio.</p>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="dashboard-actions">
                        <button class="btn btn-outline-primary me-2">
                            <i class="bi bi-download me-2"></i>Export
                        </button>
                        <button class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>New Project
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-section">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Total Messages Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-primary">
                        <div class="stats-card-body">
                            <div class="stats-card-content">
                                <div class="stats-card-info">
                                    <h3 class="stats-number"><?= $total_messages ?></h3>
                                    <p class="stats-label">Total Messages</p>
                                    <div class="stats-trend">
                                        <span class="trend-indicator positive">
                                            <i class="bi bi-arrow-up"></i> 12%
                                        </span>
                                        <span class="trend-text">vs last month</span>
                                    </div>
                                </div>
                                <div class="stats-card-icon">
                                    <div class="icon-wrapper icon-primary">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unread Messages Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-warning">
                        <div class="stats-card-body">
                            <div class="stats-card-content">
                                <div class="stats-card-info">
                                    <h3 class="stats-number"><?= $unread_messages ?></h3>
                                    <p class="stats-label">Unread Messages</p>
                                    <div class="stats-trend">
                                        <span class="trend-indicator negative">
                                            <i class="bi bi-arrow-down"></i> 3%
                                        </span>
                                        <span class="trend-text">vs last week</span>
                                    </div>
                                </div>
                                <div class="stats-card-icon">
                                    <div class="icon-wrapper icon-warning">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Views Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-success">
                        <div class="stats-card-body">
                            <div class="stats-card-content">
                                <div class="stats-card-info">
                                    <h3 class="stats-number">12.8K</h3>
                                    <p class="stats-label">Page Views</p>
                                    <div class="stats-trend">
                                        <span class="trend-indicator positive">
                                            <i class="bi bi-arrow-up"></i> 28%
                                        </span>
                                        <span class="trend-text">vs last month</span>
                                    </div>
                                </div>
                                <div class="stats-card-icon">
                                    <div class="icon-wrapper icon-success">
                                        <i class="bi bi-eye-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Projects Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-info">
                        <div class="stats-card-body">
                            <div class="stats-card-content">
                                <div class="stats-card-info">
                                    <h3 class="stats-number">24</h3>
                                    <p class="stats-label">Total Projects</p>
                                    <div class="stats-trend">
                                        <span class="trend-indicator positive">
                                            <i class="bi bi-arrow-up"></i> 5%
                                        </span>
                                        <span class="trend-text">vs last month</span>
                                    </div>
                                </div>
                                <div class="stats-card-icon">
                                    <div class="icon-wrapper icon-info">
                                        <i class="bi bi-briefcase-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-content">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Chart Section -->
                <div class="col-xl-8">
                    <div class="modern-card">
                        <div class="card-header-modern">
                            <div class="card-header-content">
                                <h5 class="card-title">Analytics Overview</h5>
                                <p class="card-subtitle">Visitor statistics and engagement metrics</p>
                            </div>
                            <div class="card-header-actions">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Last 30 days
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Last 7 days</a></li>
                                        <li><a class="dropdown-item" href="#">Last 30 days</a></li>
                                        <li><a class="dropdown-item" href="#">Last 90 days</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <div class="chart-container">
                                <canvas id="analyticsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-xl-4">
                    <div class="modern-card">
                        <div class="card-header-modern">
                            <div class="card-header-content">
                                <h5 class="card-title">Quick Actions</h5>
                                <p class="card-subtitle">Manage your portfolio efficiently</p>
                            </div>
                        </div>
                        <div class="card-body-modern">
                            <div class="quick-actions-grid">
                                <a href="<?= url('admin/projects') ?>" class="quick-action-item">
                                    <div class="quick-action-icon bg-primary">
                                        <i class="bi bi-briefcase"></i>
                                    </div>
                                    <div class="quick-action-content">
                                        <h6>Projects</h6>
                                        <small>Manage portfolio</small>
                                    </div>
                                </a>
                                
                                <a href="<?= url('admin/media') ?>" class="quick-action-item">
                                    <div class="quick-action-icon bg-success">
                                        <i class="bi bi-images"></i>
                                    </div>
                                    <div class="quick-action-content">
                                        <h6>Media</h6>
                                        <small>Upload files</small>
                                    </div>
                                </a>
                                
                                <a href="<?= url('admin/settings') ?>" class="quick-action-item">
                                    <div class="quick-action-icon bg-warning">
                                        <i class="bi bi-gear"></i>
                                    </div>
                                    <div class="quick-action-content">
                                        <h6>Settings</h6>
                                        <small>Site configuration</small>
                                    </div>
                                </a>
                                
                                <a href="<?= url('admin/theme') ?>" class="quick-action-item">
                                    <div class="quick-action-icon bg-info">
                                        <i class="bi bi-palette"></i>
                                    </div>
                                    <div class="quick-action-content">
                                        <h6>Theme</h6>
                                        <small>Customize design</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Messages -->
                <div class="col-12">
                    <div class="modern-card">
                        <div class="card-header-modern">
                            <div class="card-header-content">
                                <h5 class="card-title">Recent Messages</h5>
                                <p class="card-subtitle">Latest contact form submissions</p>
                            </div>
                            <div class="card-header-actions">
                                <a href="<?= url('admin/messages') ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-arrow-right me-1"></i>View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body-modern p-0">
                            <?php if (empty($recent_messages)): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <h6 class="empty-state-title">No messages yet</h6>
                                    <p class="empty-state-text">Messages from your contact form will appear here.</p>
                                </div>
                            <?php else: ?>
                                <div class="modern-table-container">
                                    <table class="modern-table">
                                        <thead>
                                            <tr>
                                                <th>Contact</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_messages as $message): ?>
                                                <tr class="<?= $message['is_read'] ? '' : 'unread-row' ?>">
                                                    <td>
                                                        <div class="contact-info">
                                                            <div class="contact-avatar">
                                                                <span><?= strtoupper(substr($message['name'], 0, 1)) ?></span>
                                                            </div>
                                                            <div class="contact-details">
                                                                <div class="contact-name"><?= htmlspecialchars($message['name']) ?></div>
                                                                <div class="contact-email"><?= htmlspecialchars($message['email']) ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="message-subject">
                                                            <?= htmlspecialchars(substr($message['subject'], 0, 50)) ?><?= strlen($message['subject']) > 50 ? '...' : '' ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="message-date">
                                                            <?= date('M j, g:i A', strtotime($message['created_at'])) ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($message['is_read']): ?>
                                                            <span class="status-badge status-read">Read</span>
                                                        <?php else: ?>
                                                            <span class="status-badge status-unread">New</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= url('admin/messages/' . $message['id']) ?>" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern NextJS-Style Dashboard CSS */
.dashboard-modern {
    background: #f8fafc;
    min-height: 100vh;
}

.dashboard-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.dashboard-title h1 {
    font-weight: 700;
    color: #1a202c;
    font-size: 2rem;
}

.dashboard-title p {
    color: #718096;
    font-size: 1rem;
}

.dashboard-actions .btn {
    border-radius: 0.5rem;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
}

.stats-section {
    margin-bottom: 2rem;
}

.stats-card {
    background: white;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    height: 100%;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.stats-card-body {
    padding: 1.5rem;
}

.stats-card-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stats-label {
    color: #718096;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
}

.stats-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.trend-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

.trend-indicator.positive {
    background: #dcfce7;
    color: #166534;
}

.trend-indicator.negative {
    background: #fef2f2;
    color: #dc2626;
}

.trend-text {
    font-size: 0.75rem;
    color: #9ca3af;
}

.icon-wrapper {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.icon-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.icon-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.icon-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
.icon-info { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #1a202c; }

.modern-card {
    background: white;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    height: fit-content;
}

.card-header-modern {
    padding: 1.5rem 1.5rem 0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.card-header-content h5 {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.25rem;
}

.card-header-content p {
    color: #718096;
    font-size: 0.875rem;
    margin: 0;
}

.card-body-modern {
    padding: 1.5rem;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all 0.2s ease;
    color: inherit;
}

.quick-action-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: inherit;
}

.quick-action-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
}

.quick-action-content h6 {
    margin: 0;
    font-weight: 600;
    color: #1a202c;
}

.quick-action-content small {
    color: #718096;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-state-icon {
    font-size: 4rem;
    color: #cbd5e0;
    margin-bottom: 1rem;
}

.empty-state-title {
    color: #4a5568;
    margin-bottom: 0.5rem;
}

.empty-state-text {
    color: #718096;
    margin: 0;
}

.modern-table-container {
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table th {
    text-align: left;
    padding: 1rem 1.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #4a5568;
    border-bottom: 1px solid #e2e8f0;
    background: #f7fafc;
}

.modern-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.modern-table tbody tr:hover {
    background: #f8fafc;
}

.unread-row {
    background: #fefce8;
}

.unread-row:hover {
    background: #fef3c7;
}

.contact-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.contact-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
}

.contact-name {
    font-weight: 500;
    color: #1a202c;
}

.contact-email {
    font-size: 0.875rem;
    color: #718096;
}

.message-subject {
    font-weight: 500;
    color: #374151;
}

.message-date {
    font-size: 0.875rem;
    color: #718096;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-read {
    background: #dcfce7;
    color: #166534;
}

.status-unread {
    background: #fef3c7;
    color: #92400e;
}

@media (max-width: 768px) {
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-header {
        padding: 1rem 0;
    }
    
    .dashboard-title h1 {
        font-size: 1.5rem;
    }
    
    .stats-number {
        font-size: 2rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Modern Analytics Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('analyticsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Page Views',
                    data: [1200, 1900, 3000, 5000, 4200, 3000, 4500, 6000, 7200, 8100, 9500, 12847],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }, {
                    label: 'Messages',
                    data: [65, 78, 90, 81, 95, 85, 92, 98, 87, 105, 115, <?= $total_messages ?>],
                    borderColor: '#f093fb',
                    backgroundColor: 'rgba(240, 147, 251, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#f093fb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#ffffff'
                    }
                }
            }
        });
    }
});
</script>