<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - JoMonitor | Monitoring Infus RS Manguharjo Madiun</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #2a9d8f;
            --primary-dark: #21867a;
            --primary-light: #e9f5f3;
            --secondary: #264653;
            --accent: #e9c46a;
            --danger: #e76f51;
            --danger-dark: #d65d3a;
            --success: #2a9d8f;
            --warning: #f4a261;
            --info: #5dade2;
            --text-dark: #2d3748;
            --text-light: #718096;
            --white: #ffffff;
            --bg-light: #f8fafc;
            --sidebar-bg: #1a3c34;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #0f2c25 100%);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            left: -280px;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .logo-text h3 {
            font-size: 1.2rem;
            font-weight: 700;
        }

        .logo-text p {
            font-size: 0.7rem;
            opacity: 0.7;
        }

        .user-info {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
        }

        .user-details h4 {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-details p {
            font-size: 0.7rem;
            opacity: 0.7;
        }

        .nav-menu {
            padding: 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 8px;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(42, 157, 143, 0.3);
            color: white;
        }

        .nav-item i {
            width: 22px;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
            display: none;
        }

        .page-title h2 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .page-title p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .datetime {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        /* Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.blue { background: #e0f2fe; color: #0284c7; }
        .stat-icon.green { background: #dcfce7; color: #16a34a; }
        .stat-icon.yellow { background: #fef9c3; color: #ca8a04; }
        .stat-icon.red { background: #fee2e2; color: #dc2626; }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--card-shadow);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 10px;
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        td {
            padding: 15px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.85rem;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-normal { background: #dcfce7; color: #166534; }
        .status-fast { background: #fee2e2; color: #991b1b; }
        .status-slow { background: #fef9c3; color: #854d0e; }
        .status-stuck { background: #f1f5f9; color: #475569; }

        .progress-bar {
            width: 100px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1100;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            width: 90%;
            max-width: 550px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                left: -280px;
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
            .chart-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .table-container {
                overflow-x: auto;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="fas fa-syringe"></i>
                </div>
                <div class="logo-text">
                    <h3>JoMonitor</h3>
                    <p>RS Manguharjo Madiun</p>
                </div>
            </div>
        </div>

        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-nurse"></i>
            </div>
            <div class="user-details">
                <h4>{{ Auth::user()->name }}</h4>
                <p>{{ Auth::user()->email }}</p>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="#" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-procedures"></i>
                <span>Pasien Aktif</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-history"></i>
                <span>Riwayat Monitoring</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span>Laporan</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-title">
                <h2>Dashboard Monitoring Infus</h2>
                <p>Pantau kondisi infus pasien secara real-time</p>
            </div>
            <div class="top-bar-right">
                <div class="notification-icon" onclick="showDemoNotification()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="datetime" id="datetime"></div>
            </div>
        </div>

        <div style="padding: 30px;">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <div class="stat-value" id="totalPatients">12</div>
                            <div class="stat-label">Total Pasien</div>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-procedures"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <div class="stat-value" id="activeInfusions">8</div>
                            <div class="stat-label">Infus Aktif</div>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-syringe"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <div class="stat-value" id="lowVolume">3</div>
                            <div class="stat-label">Volume Rendah</div>
                        </div>
                        <div class="stat-icon yellow">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <div class="stat-value" id="anomalyCount">2</div>
                            <div class="stat-label">Deteksi Anomali</div>
                        </div>
                        <div class="stat-icon red">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-line" style="margin-right: 8px; color: var(--primary);"></i> Monitoring Laju Tetes (TPM)</h3>
                        <span style="font-size: 0.7rem; color: var(--text-light);">Real-time / 5 menit terakhir</span>
                    </div>
                    <canvas id="tpmChart" height="200"></canvas>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie" style="margin-right: 8px; color: var(--primary);"></i> Distribusi Status Infus</h3>
                    </div>
                    <canvas id="statusChart" height="200"></canvas>
                    <div class="status-legend" style="display: flex; justify-content: center; gap: 20px; margin-top: 15px; flex-wrap: wrap;">
                        <span><span style="background: #16a34a; display: inline-block; width: 10px; height: 10px; border-radius: 50%;"></span> Normal</span>
                        <span><span style="background: #dc2626; display: inline-block; width: 10px; height: 10px; border-radius: 50%;"></span> Terlalu Cepat</span>
                        <span><span style="background: #ca8a04; display: inline-block; width: 10px; height: 10px; border-radius: 50%;"></span> Terlalu Lambat</span>
                        <span><span style="background: #64748b; display: inline-block; width: 10px; height: 10px; border-radius: 50%;"></span> Macet/Habis</span>
                    </div>
                </div>
            </div> -->

            <!-- Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-list" style="margin-right: 8px; color: var(--primary);"></i> Daftar Monitoring Infus Pasien</h3>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-outline" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button class="btn-primary" onclick="openAddModal()">
                            <i class="fas fa-plus"></i> Tambah Pasien
                        </button>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table id="patientsTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pasien / Ruang</th>
                                <th>Volume Tersisa</th>
                                <th>Laju Tetes (TPM)</th>
                                <th>Status</th>
                                <th>Estimasi Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Data akan diisi JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Pasien -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus" style="color: var(--primary);"></i> Tambah Pasien Baru</h3>
                <button onclick="closeAddModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form id="patientForm">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Pasien *</label>
                            <input type="text" id="name" required>
                        </div>
                        <div class="form-group">
                            <label>ID Pasien *</label>
                            <input type="text" id="patientId" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ruang</label>
                            <input type="text" id="room">
                        </div>
                        <div class="form-group">
                            <label>Nomor Bed</label>
                            <input type="text" id="bed">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Volume Awal (ml) *</label>
                            <input type="number" id="initialVolume" required>
                        </div>
                        <div class="form-group">
                            <label>Faktor Tetes</label>
                            <select id="dropFactor">
                                <option value="20">Makro (20 tetes/ml)</option>
                                <option value="60">Mikro (60 tetes/ml)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Durasi Pemberian (jam) *</label>
                            <input type="number" id="duration" required>
                        </div>
                        <div class="form-group">
                            <label>Target TPM (Otomatis)</label>
                            <input type="text" id="targetTpmDisplay" readonly style="background: #f1f5f9;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jenis Infus</label>
                        <select id="infusionType">
                            <option value="NaCl 0.9%">NaCl 0.9%</option>
                            <option value="Ringer Laktat">Ringer Laktat</option>
                            <option value="Dextrose 5%">Dextrose 5%</option>
                            <option value="Dextrose 10%">Dextrose 10%</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Pasien</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Data dummy
        let patients = [
            { id: 1, name: "Budi Santoso", room: "Melati", bed: "01", initialVolume: 500, remainingVolume: 320, currentTpm: 18, targetTpm: 21, status: "normal", infusionType: "NaCl 0.9%" },
            { id: 2, name: "Siti Aminah", room: "Mawar", bed: "03", initialVolume: 500, remainingVolume: 85, currentTpm: 14, targetTpm: 14, status: "normal", infusionType: "Ringer Laktat" },
            { id: 3, name: "Ahmad Wahyu", room: "Anggrek", bed: "02", initialVolume: 1000, remainingVolume: 620, currentTpm: 28, targetTpm: 21, status: "fast", infusionType: "Dextrose 5%" },
            { id: 4, name: "Dewi Lestari", room: "Flamboyan", bed: "05", initialVolume: 500, remainingVolume: 30, currentTpm: 6, targetTpm: 14, status: "slow", infusionType: "NaCl 0.9%" },
            { id: 5, name: "Rudi Hartono", room: "Cempaka", bed: "04", initialVolume: 500, remainingVolume: 0, currentTpm: 0, targetTpm: 21, status: "stuck", infusionType: "Dextrose 10%" },
            { id: 6, name: "Nurul Hidayah", room: "Dahlia", bed: "02", initialVolume: 1000, remainingVolume: 450, currentTpm: 19, targetTpm: 21, status: "normal", infusionType: "Ringer Laktat" },
            { id: 7, name: "Agus Prayogo", room: "Kamboja", bed: "01", initialVolume: 500, remainingVolume: 120, currentTpm: 23, targetTpm: 21, status: "fast", infusionType: "NaCl 0.9%" },
            { id: 8, name: "Linda Wijaya", room: "Mawar", bed: "06", initialVolume: 500, remainingVolume: 45, currentTpm: 10, targetTpm: 14, status: "slow", infusionType: "Dextrose 5%" }
        ];


        // Update datetime
        function updateDateTime() {
            const now = new Date();
            const formatted = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            document.getElementById('datetime').textContent = formatted;
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Render table
        function renderTable() {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = patients.map((p, idx) => {
                const percent = (p.remainingVolume / p.initialVolume) * 100;
                let statusClass = '';
                let statusText = '';
                switch(p.status) {
                    case 'normal': statusClass = 'status-normal'; statusText = 'Normal'; break;
                    case 'fast': statusClass = 'status-fast'; statusText = 'Terlalu Cepat'; break;
                    case 'slow': statusClass = 'status-slow'; statusText = 'Terlalu Lambat'; break;
                    case 'stuck': statusClass = 'status-stuck'; statusText = 'Macet/Habis'; break;
                }
                const estimatedTime = p.status === 'stuck' ? 'Selesai' : Math.floor(Math.random() * 60) + ' menit lagi';
                return `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><strong>${p.name}</strong><br><small style="color: #718096;">${p.room} / Bed ${p.bed}</small></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>${p.remainingVolume} ml</span>
                                <div class="progress-bar"><div class="progress-fill" style="width: ${percent}%"></div></div>
                            </div>
                        </td>
                        <td><strong>${p.currentTpm}</strong> <span style="color: #718096;">/ ${p.targetTpm} TPM</span></td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td>${estimatedTime}</td>
                        <td><button class="btn-outline" style="padding: 5px 12px;" onclick="viewDetail(${p.id})"><i class="fas fa-chart-line"></i></button></td>
                    </tr>
                `;
            }).join('');
            
            // Update stats
            document.getElementById('totalPatients').textContent = patients.length;
            document.getElementById('activeInfusions').textContent = patients.filter(p => p.status !== 'stuck').length;
            document.getElementById('lowVolume').textContent = patients.filter(p => p.remainingVolume < 50 && p.status !== 'stuck').length;
            document.getElementById('anomalyCount').textContent = patients.filter(p => p.status !== 'normal').length;
            
        }
  
        // Hitung target TPM
        function calculateTargetTpm() {
            const volume = document.getElementById('initialVolume').value;
            const factor = document.getElementById('dropFactor').value;
            const duration = document.getElementById('duration').value;
            if (volume && factor && duration) {
                const tpm = (volume * factor) / (duration * 60);
                document.getElementById('targetTpmDisplay').value = Math.round(tpm);
            }
        }

        // Event listeners untuk hitung otomatis
        document.getElementById('initialVolume')?.addEventListener('input', calculateTargetTpm);
        document.getElementById('dropFactor')?.addEventListener('change', calculateTargetTpm);
        document.getElementById('duration')?.addEventListener('input', calculateTargetTpm);

        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.add('show');
        }
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
            document.getElementById('patientForm').reset();
        }

        // Submit form
        document.getElementById('patientForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const newPatient = {
                id: patients.length + 1,
                name: document.getElementById('name').value,
                patientId: document.getElementById('patientId').value,
                room: document.getElementById('room').value || '-',
                bed: document.getElementById('bed').value || '-',
                initialVolume: parseInt(document.getElementById('initialVolume').value),
                remainingVolume: parseInt(document.getElementById('initialVolume').value),
                currentTpm: 0,
                targetTpm: parseInt(document.getElementById('targetTpmDisplay').value) || 21,
                status: 'normal',
                infusionType: document.getElementById('infusionType').value
            };
            patients.push(newPatient);
            renderTable();
            closeAddModal();
            Swal.fire({ title: 'Berhasil!', text: 'Pasien berhasil ditambahkan', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
        });

        function refreshData() {
            Swal.fire({ title: 'Memperbarui...', text: 'Mengambil data terbaru', icon: 'info', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
            renderTable();
        }

        function viewDetail(id) {
            Swal.fire({ title: 'Detail Pasien', text: 'Fitur detail akan segera tersedia', icon: 'info' });
        }

        function showDemoNotification() {
            Swal.fire({ title: 'Notifikasi', html: '3 pasien memerlukan perhatian:<br>- Ahmad Wahyu (TPM terlalu cepat)<br>- Dewi Lestari (Volume rendah)<br>- Rudi Hartono (Infus habis)', icon: 'warning', confirmButtonColor: '#2a9d8f' });
        }

        // Sidebar toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Initial render
        renderTable();
    </script>
</body>
</html>