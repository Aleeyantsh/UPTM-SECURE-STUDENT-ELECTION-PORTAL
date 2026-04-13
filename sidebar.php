<?php
// Get current page for active state highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <!-- Logo & Brand -->
    <div class="sidebar-brand">
        <a href="dashboard.php" class="brand-link">
            <div class="brand-logo">
                <img src="/img/logo uptm.png" alt="UPTM Logo">
            </div>
            <div class="brand-text">
                <span class="brand-name">UPTM</span>
                <span class="brand-sub">Voting System</span>
            </div>
        </a>
    </div>

    <!-- Admin Badge -->
    <div class="admin-badge">
        <div class="admin-avatar">A</div>
        <div class="admin-info">
            <span class="admin-label">Administrator</span>
            <span class="admin-status">● Online</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <p class="nav-section-label">MAIN MENU</p>

        <a href="dashboard.php" class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </span>
            <span class="nav-label">Dashboard</span>
            <?php if($current_page == 'dashboard.php'): ?>
                <span class="nav-active-dot"></span>
            <?php endif; ?>
        </a>

        <a href="manage_user.php" class="nav-item <?php echo ($current_page == 'manage_user.php') ? 'active' : ''; ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </span>
            <span class="nav-label">Manage Users</span>
        </a>

        <a href="manage_candidates.php" class="nav-item <?php echo ($current_page == 'manage_candidates.php') ? 'active' : ''; ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </span>
            <span class="nav-label">Candidates</span>
        </a>

        <p class="nav-section-label" style="margin-top: 10px;">ELECTION</p>

        <a href="schedule.php" class="nav-item <?php echo ($current_page == 'schedule.php') ? 'active' : ''; ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </span>
            <span class="nav-label">Schedule</span>
        </a>

        <a href="audit_logs.php" class="nav-item <?php echo ($current_page == 'audit_logs.php') ? 'active' : ''; ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </span>
            <span class="nav-label">Audit Logs</span>
        </a>

        <a href="election_report.php" class="nav-item <?php echo ($current_page == 'election_report.php') ? 'active' : ''; ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
                </svg>
            </span>
            <span class="nav-label">Election Report</span>
        </a>
    </nav>

    <!-- Logout Button -->
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Logout
        </a>
    </div>
</div>

<style>
/* =============================================
   UPTM ADMIN SIDEBAR - FIXED & REDESIGNED
   ============================================= */

.sidebar {
    width: 260px;
    min-width: 260px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    display: flex;
    flex-direction: column;
    z-index: 1000;
    border-right: 1px solid rgba(255,255,255,0.06);
    overflow-y: auto;
    overflow-x: hidden;
}

/* Scrollbar styling */
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

/* ---- BRAND ---- */
.sidebar-brand {
    padding: 24px 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}

.brand-link {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.brand-logo img {
    width: 100px;
    height: 40px;
    object-fit: contain;
    border-radius: 8px;
    background: rgba(255,255,255,0.1);
    padding: 4px;
}

.brand-text {
    display: flex;
    flex-direction: column;
}

.brand-name {
    color: #f8fafc;
    font-weight: 800;
    font-size: 16px;
    letter-spacing: 1px;
}

.brand-sub {
    color: #64748b;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* ---- ADMIN BADGE ---- */
.admin-badge {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    margin: 12px 16px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 10px;
}

.admin-avatar {
    width: 34px;
    height: 34px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 14px;
    flex-shrink: 0;
}

.admin-info {
    display: flex;
    flex-direction: column;
}

.admin-label {
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 600;
}

.admin-status {
    color: #22c55e;
    font-size: 11px;
    font-weight: 500;
}

/* ---- NAVIGATION ---- */
.sidebar-nav {
    flex: 1;
    padding: 8px 12px;
}

.nav-section-label {
    color: #475569;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 12px 8px 6px;
    margin: 0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    text-decoration: none;
    color: #94a3b8;
    border-radius: 9px;
    margin-bottom: 2px;
    transition: all 0.2s ease;
    position: relative;
    font-size: 14px;
    font-weight: 500;
}

.nav-item:hover {
    background: rgba(255,255,255,0.07);
    color: #e2e8f0;
    transform: translateX(2px);
}

.nav-item.active {
    background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(29,78,216,0.15));
    color: #93c5fd;
    border: 1px solid rgba(59,130,246,0.25);
    font-weight: 600;
}

.nav-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 7px;
    background: rgba(255,255,255,0.05);
    flex-shrink: 0;
    transition: background 0.2s;
}

.nav-item:hover .nav-icon,
.nav-item.active .nav-icon {
    background: rgba(59,130,246,0.2);
    color: #60a5fa;
}

.nav-label {
    flex: 1;
}

.nav-active-dot {
    width: 6px;
    height: 6px;
    background: #3b82f6;
    border-radius: 50%;
    box-shadow: 0 0 6px #3b82f6;
}

/* ---- FOOTER / LOGOUT ---- */
.sidebar-footer {
    padding: 16px;
    border-top: 1px solid rgba(255,255,255,0.07);
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 11px;
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 9px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
}

.logout-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border-color: rgba(239, 68, 68, 0.4);
}

/* ---- MAIN CONTENT OFFSET ---- */
.main-content {
    margin-left: 260px;
}
</style>