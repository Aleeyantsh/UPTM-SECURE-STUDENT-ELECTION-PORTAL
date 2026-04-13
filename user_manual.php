<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manual | UPTM Voting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, #1a237e 0%, #283593 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 4px 0 15px rgba(0,0,0,0.15);
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header img {
            width: 80px;
            margin-bottom: 10px;
        }

        .sidebar-title {
            font-size: 12px;
            color: #c5cae9;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 700;
            line-height: 1.4;
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-link {
            color: #c5cae9;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 25px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }

        .sidebar-active {
            background: rgba(255,255,255,0.12);
            border-left: 4px solid #7986cb;
        }

        .sidebar-link.active {
            color: white;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logout {
            display: block;
            background: #c62828;
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .sidebar-logout:hover { background: #b71c1c; }

        /* ===== TOGGLE (mobile) ===== */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: #1a237e;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        /* ===== MAIN CONTENT ===== */
       .main-content {
    margin-left: 320px;
    padding: 40px 80px;
    flex: 1;
    min-height: 100vh;
    max-width: 1000px;
        }

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .page-header p {
            color: #64748b;
            font-size: 14px;
        }

        /* STEP CARDS */
        .steps {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 780px;
        }

        .step-card {
            background: white;
            border-radius: 16px;
            padding: 24px 28px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .step-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .step-number {
            min-width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1a56db, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(29,78,216,0.3);
        }

        .step-content h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .step-content p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .step-content .tip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        /* FOOTER NOTE */
        .footer-note {
            margin-top: 30px;
            max-width: 780px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px 28px;
            text-align: center;
        }

        .footer-note p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.8;
        }

        .footer-note strong { color: #1e293b; }

        /* PRINT BUTTON */
        .btn-print {
            margin-top: 20px;
            padding: 12px 28px;
            background: linear-gradient(135deg, #1a56db, #1d4ed8);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: opacity 0.3s ease;
        }

        .btn-print:hover { opacity: 0.9; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-toggle { display: block; }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding: 20px; padding-top: 60px; }
        }

        @media print {
            .sidebar, .sidebar-toggle, .btn-print { display: none; }
            .main-content { margin-left: 0; padding: 20px; }
            .step-card { box-shadow: none; border: 1px solid #ddd; break-inside: avoid; }
            .tip { background: #eff6ff !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

    <!-- TOGGLE (mobile) -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/img/logo uptm.png" alt="UPTM Logo">
            <h3 class="sidebar-title">SYSTEM VOTING UPTM</h3>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="sidebar-link">
                    <span>📊</span> Dashboard
                </a>
            </li>
            <li>
                <a href="portal.php" class="sidebar-link">
                    <span>🗳️</span> Portal
                </a>
            </li>
            <li class="sidebar-active">
                <a href="user_manual.php" class="sidebar-link active">
                    <span>📘</span> User Manual
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-logout">Logout</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="page-header">
            <h1>📘 User Manual</h1>
            <p>Follow these steps to cast your vote easily and securely.</p>
        </div>

        <div class="steps">

            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>🔐 Log In with Your Student Email</h3>
                    <p>Open the UPTM Voting System link. Click the <strong>"Login with Student Email"</strong> button and select your official student email account ending with <strong>@student.uptm.edu.my</strong>.</p>
                    <span class="tip">⚠️ Only your UPTM student email is accepted. Personal emails will be rejected.</span>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>📊 Check Your Dashboard</h3>
                    <p>After logging in, you will be directed to the <strong>Dashboard</strong>. Here you can view your <strong>Voting Quota Status</strong> — showing how many votes you have remaining (e.g. 0/5).</p>
                    <span class="tip">ℹ️ Each student is eligible to vote for up to 5 different candidates.</span>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>🗳️ Go to the Voting Portal</h3>
                    <p>Click <strong>"Vote Now"</strong> in the left sidebar menu to navigate to the voting portal page.</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>👤 View Candidate Profiles</h3>
                    <p>Browse the list of candidates running in the election. Click <strong>"View Candidate"</strong> to read the full profile of any candidate before making your decision.</p>
                    <span class="tip">💡 Read each candidate's profile carefully before voting.</span>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h3>✅ Cast Your Vote</h3>
                    <p>Once you are ready, click the <strong>"Vote"</strong> button on your preferred candidate. Confirm your selection when prompted. Your vote will be securely recorded in the system.</p>
                    <span class="tip">🔒 Your vote is confidential and secure.</span>
                </div>
            </div>

        </div>

        <div class="footer-note">
            <p>
                If you encounter any issues during the voting process, please contact the system administrator.<br>
                <strong>UPTM Voting System</strong> — Your vote shapes the future of the campus.
            </p>
            <button class="btn-print" onclick="window.print()">🖨️ Print This Guide</button>
        </div>

    </div>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    }
    </script>

</body>
</html>