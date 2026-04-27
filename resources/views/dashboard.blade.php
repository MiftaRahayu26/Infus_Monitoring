<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - JoMonitor | Monitoring Infus RS Manguharjo Madiun</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .pagination button {
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .pagination button:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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

        /* Detail Modal */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: #f8fafc;
            padding: 12px;
            border-radius: 12px;
        }

        .detail-label {
            font-size: 0.7rem;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .progress-large {
            height: 20px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 15px 0;
        }

        .progress-fill-large {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: white;
            font-weight: bold;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.show {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            background: white;
            padding: 10px;
            border-radius: 50%;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Alert Container */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9998;
            max-width: 350px;
        }

        .alert-toast {
            background: white;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
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
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container"></div>

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
            <a href="#" class="nav-item" id="deviceMenuBtn">
                <i class="fas fa-microchip"></i>
                <span>Device Management</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; background: none; border: none; cursor: pointer;">
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
                <div class="notification-icon" onclick="showNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationBadge">0</span>
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
                            <div class="stat-value" id="totalPatients">0</div>
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
                            <div class="stat-value" id="activeInfusions">0</div>
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
                            <div class="stat-value" id="lowVolume">0</div>
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
                            <div class="stat-value" id="anomalyCount">0</div>
                            <div class="stat-label">Deteksi Anomali</div>
                        </div>
                        <div class="stat-icon red">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

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
                                <th>Device</th>
                                <th>Status</th>
                                <th>Estimasi Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="pagination" id="pagination">
                    <!-- Pagination buttons will be inserted here -->
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
                            <label>ID Pasien / No RM *</label>
                            <input type="text" id="patientId" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ruang</label>
                            <input type="text" id="room" placeholder="Contoh: Melati, Mawar">
                        </div>
                        <div class="form-group">
                            <label>Nomor Bed</label>
                            <input type="text" id="bed" placeholder="Contoh: 01, 02">
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
                    <div class="form-group">
                        <label>Device Key (Opsional)</label>
                        <select id="deviceKeySelect">
                            <option value="">-- Pilih Device --</option>
                        </select>
                        <small class="text-muted">Pilih device ESP32 yang terpasang pada infus pasien</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Pasien</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Pasien -->
    <div class="modal" id="detailModal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle" style="color: var(--primary);"></i> Detail Infus Pasien</h3>
                <button onclick="closeDetailModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin"></i> Memuat detail...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeDetailModal()">Tutup</button>
                <button type="button" class="btn-primary" id="updateVolumeBtn" onclick="updateVolume()">Update Volume</button>
            </div>
        </div>
    </div>

    <!-- Modal Device Management -->
    <div class="modal" id="deviceModal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3><i class="fas fa-microchip" style="color: var(--primary);"></i> Device Management</h3>
                <button onclick="closeDeviceModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <button class="btn-primary" onclick="showAddDeviceForm()" style="width: 100%;">
                        <i class="fas fa-plus"></i> Tambah Device Baru
                    </button>
                </div>
                <div id="deviceFormContainer" style="display: none; margin-bottom: 20px; padding: 15px; background: #f8fafc; border-radius: 12px;">
                    <h4 id="deviceFormTitle">Tambah Device</h4>
                    <div class="form-group">
                        <label>Device Key *</label>
                        <input type="text" id="deviceKeyInput" placeholder="Contoh: ESP32_A001">
                    </div>
                    <div class="form-group">
                        <label>Tipe Device</label>
                        <select id="deviceTypeInput">
                            <option value="infus">Infus</option>
                            <option value="suhu">Suhu</option>
                        </select>
                    </div>
                    <input type="hidden" id="deviceEditId">
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button class="btn-primary" onclick="saveDevice()">Simpan</button>
                        <button class="btn-outline" onclick="hideDeviceForm()">Batal</button>
                    </div>
                </div>
                <div id="deviceListContainer">
                    <table style="width: 100%;">
                        <thead>
                            <tr><th>Device Key</th><th>Tipe</th><th>Status</th><th>Last Seen</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="deviceTableBody">
                            <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeDeviceModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // GLOBAL VARIABLES
        // ============================================
        let currentPage = 1;
        let totalPages = 1;
        let autoRefreshInterval = null;
        let currentDetailPatientId = null;

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        function showAlert(message, type = 'success') {
            const container = document.getElementById('alertContainer');
            const alertId = 'alert_' + Date.now();
            const bgColor = type === 'success' ? '#dcfce7' : type === 'error' ? '#fee2e2' : '#fef9c3';
            const textColor = type === 'success' ? '#166534' : type === 'error' ? '#991b1b' : '#854d0e';
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            const alertHtml = `
                <div id="${alertId}" class="alert-toast" style="background: ${bgColor}; border-left: 4px solid ${textColor};">
                    <i class="fas ${icon}" style="color: ${textColor};"></i>
                    <span style="color: ${textColor}; flex: 1;">${message}</span>
                    <button onclick="document.getElementById('${alertId}').remove()" style="background: none; border: none; cursor: pointer;">&times;</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', alertHtml);
            
            setTimeout(() => {
                const el = document.getElementById(alertId);
                if (el) el.remove();
            }, 4000);
        }

        function updateDateTime() {
            const now = new Date();
            const formatted = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            document.getElementById('datetime').textContent = formatted;
        }

        // ============================================
        // API CALLS - LOAD DATA
        // ============================================
        async function loadStats() {
            try {
                const response = await fetch('/api/monitoring/stats');
                const result = await response.json();
                if (result.success) {
                    document.getElementById('totalPatients').textContent = result.total_patients;
                    document.getElementById('activeInfusions').textContent = result.active_infusions;
                    document.getElementById('lowVolume').textContent = result.low_volume;
                    document.getElementById('anomalyCount').textContent = result.anomaly_count;
                    document.getElementById('notificationBadge').textContent = result.anomaly_count;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadMonitoringData(page = 1) {
            showLoading();
            try {
                const response = await fetch(`/api/monitoring/data?page=${page}&per_page=10`);
                const result = await response.json();
                
                if (result.success) {
                    currentPage = result.current_page;
                    totalPages = result.last_page;
                    renderTable(result.data);
                    renderPagination();
                } else {
                    showAlert('Gagal memuat data', 'error');
                }
            } catch (error) {
                console.error('Error loading monitoring data:', error);
                showAlert('Gagal memuat data monitoring', 'error');
            } finally {
                hideLoading();
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('tableBody');
            
            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <p class="text-muted">Belum ada data pasien</p>
                            <button class="btn-primary" onclick="openAddModal()">Tambah Pasien</button>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = data.map((p, idx) => {
                const percent = p.remaining_percent || 0;
                let statusClass = '';
                let statusText = '';
                
                switch(p.status) {
                    case 'normal': statusClass = 'status-normal'; statusText = 'Normal'; break;
                    case 'too_fast': statusClass = 'status-fast'; statusText = 'Terlalu Cepat'; break;
                    case 'too_slow': statusClass = 'status-slow'; statusText = 'Terlalu Lambat'; break;
                    case 'stuck': statusClass = 'status-stuck'; statusText = 'Macet'; break;
                    case 'empty': statusClass = 'status-stuck'; statusText = 'Habis'; break;
                    default: statusClass = 'status-normal'; statusText = 'Normal';
                }
                
                const deviceDisplay = p.device_key ? 
                    `<span class="status-badge status-normal" style="background:#e0f2fe; color:#0284c7;">${p.device_key}</span>` : 
                    '<span class="status-badge status-stuck">Belum</span>';
                
                return `
                    <tr>
                        <td>${((currentPage - 1) * 10) + idx + 1}</td>
                        <td>
                            <strong>${escapeHtml(p.name)}</strong><br>
                            <small style="color: var(--text-light);">${p.room || '-'} / Bed ${p.bed_number || '-'}</small>
                         </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>${Math.round(p.remaining_ml)} ml</span>
                                <div class="progress-bar"><div class="progress-fill" style="width: ${percent}%"></div></div>
                            </div>
                         </td>
                        <td>
                            <strong>${p.current_tpm || 0}</strong> 
                            <span style="color: var(--text-light);">/ ${p.target_tpm} TPM</span>
                         </td>
                        <td>${deviceDisplay}</td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td>${p.estimated_time || '-'}</td>
                        <td>
                            <button class="btn-outline" style="padding: 5px 10px;" onclick="showDetail(${p.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-outline" style="padding: 5px 10px; margin-left: 5px; border-color: #e74c3c; color: #e74c3c;" onclick="deletePatient(${p.id}, '${escapeHtml(p.name)}')">
                                <i class="fas fa-trash"></i>
                            </button>
                         </td>
                    </tr>
                `;
            }).join('');
        }

        function renderPagination() {
            const container = document.getElementById('pagination');
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            html += `<button onclick="goToPage(1)" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i><i class="fas fa-chevron-left"></i></button>`;
            html += `<button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
            
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);
            
            for (let i = startPage; i <= endPage; i++) {
                html += `<button onclick="goToPage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
            }
            
            html += `<button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
            html += `<button onclick="goToPage(${totalPages})" ${currentPage === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i><i class="fas fa-chevron-right"></i></button>`;
            
            container.innerHTML = html;
        }

        function goToPage(page) {
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            loadMonitoringData(currentPage);
        }

        // ============================================
        // CRUD PATIENTS
        // ============================================
        async function savePatient(event) {
            event.preventDefault();
            
            const formData = {
                nama: document.getElementById('name').value,
                patient_id: document.getElementById('patientId').value,
                room: document.getElementById('room').value,
                bed_number: document.getElementById('bed').value,
                infusion_type: document.getElementById('infusionType').value,
                initial_volume: parseInt(document.getElementById('initialVolume').value),
                drop_factor: parseInt(document.getElementById('dropFactor').value),
                duration_hours: parseInt(document.getElementById('duration').value),
                target_tpm: parseInt(document.getElementById('targetTpmDisplay').value) || 0,
                device_key: document.getElementById('deviceKeySelect').value || null
            };
            
            // Validasi
            if (!formData.nama || !formData.patient_id || !formData.initial_volume || !formData.duration_hours) {
                showAlert('Harap lengkapi data yang wajib diisi', 'error');
                return;
            }
            
            showLoading();
            
            try {
                const response = await fetch('/api/patients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Pasien berhasil ditambahkan!', 'success');
                    closeAddModal();
                    loadMonitoringData(currentPage);
                    loadStats();
                } else {
                    showAlert(result.message || 'Gagal menambahkan pasien', 'error');
                }
            } catch (error) {
                console.error('Error saving patient:', error);
                showAlert('Terjadi kesalahan saat menyimpan', 'error');
            } finally {
                hideLoading();
            }
        }

        async function deletePatient(id, name) {
            Swal.fire({
                title: 'Hapus Pasien?',
                html: `Apakah Anda yakin ingin menghapus pasien <strong>${name}</strong>?<br>Semua data monitoring akan ikut terhapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    showLoading();
                    try {
                        const response = await fetch(`/api/patients/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            showAlert('Pasien berhasil dihapus', 'success');
                            loadMonitoringData(currentPage);
                            loadStats();
                        } else {
                            showAlert('Gagal menghapus pasien', 'error');
                        }
                    } catch (error) {
                        showAlert('Terjadi kesalahan', 'error');
                    } finally {
                        hideLoading();
                    }
                }
            });
        }

        // ============================================
        // DETAIL MODAL
        // ============================================
        async function showDetail(id) {
            currentDetailPatientId = id;
            const modal = document.getElementById('detailModal');
            const body = document.getElementById('detailModalBody');
            
            body.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat detail...</div>';
            modal.classList.add('show');
            
            try {
                const response = await fetch(`/api/patients/${id}`);
                const result = await response.json();
                
                if (result.success) {
                    renderDetailModal(result.data);
                } else {
                    body.innerHTML = '<div class="text-center py-4 text-danger">Gagal memuat detail</div>';
                }
            } catch (error) {
                body.innerHTML = '<div class="text-center py-4 text-danger">Terjadi kesalahan</div>';
            }
        }

        function renderDetailModal(data) {
            const percent = data.remaining_percent || 0;
            let fillColor = '#2a9d8f';
            if (percent <= 20) fillColor = '#e74c3c';
            else if (percent <= 50) fillColor = '#f39c12';
            
            let statusClass = '';
            let statusText = '';
            switch(data.status) {
                case 'normal': statusClass = 'status-normal'; statusText = 'Normal'; break;
                case 'too_fast': statusClass = 'status-fast'; statusText = 'Terlalu Cepat'; break;
                case 'too_slow': statusClass = 'status-slow'; statusText = 'Terlalu Lambat'; break;
                case 'stuck': statusClass = 'status-stuck'; statusText = 'Macet'; break;
                case 'empty': statusClass = 'status-stuck'; statusText = 'Habis'; break;
            }
            
            const html = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Nama Pasien</div>
                        <div class="detail-value">${escapeHtml(data.name)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">No. RM</div>
                        <div class="detail-value">${escapeHtml(data.patient_id)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Ruang / Bed</div>
                        <div class="detail-value">${escapeHtml(data.room || '-')} / ${escapeHtml(data.bed_number || '-')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Jenis Infus</div>
                        <div class="detail-value">${escapeHtml(data.infusion_type)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Device</div>
                        <div class="detail-value">${data.device_key ? escapeHtml(data.device_key) : '<span class="status-badge status-stuck">Belum assign</span>'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><span class="status-badge ${statusClass}">${statusText}</span></div>
                    </div>
                </div>
                
                <div class="detail-item" style="margin-bottom: 15px;">
                    <div class="detail-label">Sisa Volume</div>
                    <div class="progress-large">
                        <div class="progress-fill-large" style="width: ${percent}%; background: ${fillColor};">
                            ${percent}%
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>${Math.round(data.remaining_volume)} ml</span>
                        <span>dari ${data.initial_volume} ml</span>
                    </div>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Target TPM</div>
                        <div class="detail-value">${data.target_tpm} tetes/menit</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">TPM Saat Ini</div>
                        <div class="detail-value">${data.current_tpm || 0} tetes/menit</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Faktor Tetes</div>
                        <div class="detail-value">${data.drop_factor} tetes/ml</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Durasi</div>
                        <div class="detail-value">${data.duration_hours} jam</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Mulai Infus</div>
                        <div class="detail-value">${data.start_time || '-'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Estimasi Selesai</div>
                        <div class="detail-value">${data.end_time || '-'} <br><small>(${data.remaining_time || '-'})</small></div>
                    </div>
                </div>
            `;
            
            document.getElementById('detailModalBody').innerHTML = html;
        }

        async function updateVolume() {
            if (!currentDetailPatientId) return;
            
            const { value: percent } = await Swal.fire({
                title: 'Update Volume Infus',
                text: 'Masukkan persentase volume yang tersisa (0-100%)',
                input: 'number',
                inputLabel: 'Persentase Volume',
                inputValue: 0,
                inputAttributes: {
                    min: 0,
                    max: 100,
                    step: 1
                },
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Batal'
            });
            
            if (percent !== null && percent >= 0 && percent <= 100) {
                showLoading();
                try {
                    const response = await fetch(`/api/patients/${currentDetailPatientId}/update-volume`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ volume_percent: parseInt(percent) })
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        showAlert('Volume berhasil diupdate', 'success');
                        await showDetail(currentDetailPatientId);
                        loadMonitoringData(currentPage);
                        loadStats();
                    } else {
                        showAlert('Gagal update volume', 'error');
                    }
                } catch (error) {
                    showAlert('Terjadi kesalahan', 'error');
                } finally {
                    hideLoading();
                }
            }
        }

        // ============================================
        // DEVICE MANAGEMENT
        // ============================================
        async function loadDevices() {
            try {
                const response = await fetch('/api/devices?device_type=infus');
                const devices = await response.json();
                renderDeviceTable(devices);
                loadDeviceSelect();
            } catch (error) {
                console.error('Error loading devices:', error);
            }
        }

        function renderDeviceTable(devices) {
            const tbody = document.getElementById('deviceTableBody');
            if (!devices || devices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Belum ada device terdaftar</td></tr>';
                return;
            }
            
            tbody.innerHTML = devices.map(device => {
                let statusClass = device.status === 'online' ? 'status-normal' : device.status === 'error' ? 'status-fast' : 'status-stuck';
                let statusText = device.status === 'online' ? 'Online' : device.status === 'error' ? 'Error' : 'Offline';
                
                return `
                    <tr>
                        <td>${escapeHtml(device.device_key)}</td>
                        <td>${device.device_type}</td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td>${device.last_seen ? new Date(device.last_seen).toLocaleString() : '-'}</td>
                        <td>
                            <button class="btn-outline" style="padding: 4px 8px;" onclick="editDevice(${device.id}, '${escapeHtml(device.device_key)}', '${device.device_type}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-outline" style="padding: 4px 8px; border-color: #e74c3c; color: #e74c3c;" onclick="deleteDevice(${device.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function loadDeviceSelect() {
            try {
                const response = await fetch('/api/devices/available');
                const devices = await response.json();
                const select = document.getElementById('deviceKeySelect');
                select.innerHTML = '<option value="">-- Pilih Device --</option>';
                if (devices && devices.length > 0) {
                    devices.forEach(device => {
                        select.innerHTML += `<option value="${device.device_key}">${device.device_key}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error loading device select:', error);
            }
        }

        function showAddDeviceForm() {
            document.getElementById('deviceFormContainer').style.display = 'block';
            document.getElementById('deviceFormTitle').textContent = 'Tambah Device Baru';
            document.getElementById('deviceKeyInput').value = '';
            document.getElementById('deviceTypeInput').value = 'infus';
            document.getElementById('deviceEditId').value = '';
        }

        function hideDeviceForm() {
            document.getElementById('deviceFormContainer').style.display = 'none';
            document.getElementById('deviceKeyInput').value = '';
            document.getElementById('deviceEditId').value = '';
        }

        function editDevice(id, key, type) {
            document.getElementById('deviceFormContainer').style.display = 'block';
            document.getElementById('deviceFormTitle').textContent = 'Edit Device';
            document.getElementById('deviceKeyInput').value = key;
            document.getElementById('deviceTypeInput').value = type;
            document.getElementById('deviceEditId').value = id;
        }

        async function saveDevice() {
            const id = document.getElementById('deviceEditId').value;
            const deviceKey = document.getElementById('deviceKeyInput').value;
            const deviceType = document.getElementById('deviceTypeInput').value;
            
            if (!deviceKey) {
                showAlert('Device Key wajib diisi', 'error');
                return;
            }
            
            const url = id ? `/api/devices/${id}` : '/api/devices';
            const method = id ? 'PUT' : 'POST';
            
            showLoading();
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ device_key: deviceKey, device_type: deviceType })
                });
                
                const result = await response.json();
                if (result.success) {
                    showAlert(id ? 'Device berhasil diupdate' : 'Device berhasil ditambahkan', 'success');
                    hideDeviceForm();
                    loadDevices();
                } else {
                    showAlert(result.message || 'Gagal menyimpan device', 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan', 'error');
            } finally {
                hideLoading();
            }
        }

        async function deleteDevice(id) {
            Swal.fire({
                title: 'Hapus Device?',
                text: 'Device yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    showLoading();
                    try {
                        const response = await fetch(`/api/devices/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        });
                        const data = await response.json();
                        if (data.success) {
                            showAlert('Device berhasil dihapus', 'success');
                            loadDevices();
                        } else {
                            showAlert('Gagal menghapus device', 'error');
                        }
                    } catch (error) {
                        showAlert('Terjadi kesalahan', 'error');
                    } finally {
                        hideLoading();
                    }
                }
            });
        }

        // ============================================
        // MODAL FUNCTIONS
        // ============================================
        function openAddModal() {
            document.getElementById('addModal').classList.add('show');
            calculateTargetTpm();
            loadDeviceSelect();
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
            document.getElementById('patientForm').reset();
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('show');
            currentDetailPatientId = null;
        }

        function openDeviceModal() {
            document.getElementById('deviceModal').classList.add('show');
            loadDevices();
            hideDeviceForm();
        }

        function closeDeviceModal() {
            document.getElementById('deviceModal').classList.remove('show');
        }

        function showNotifications() {
            const anomalyCount = document.getElementById('anomalyCount').textContent;
            if (anomalyCount > 0) {
                Swal.fire({
                    title: 'Notifikasi',
                    html: `Terdapat ${anomalyCount} pasien dengan kondisi anomali.<br>Silakan cek tabel monitoring.`,
                    icon: 'warning',
                    confirmButtonColor: '#2a9d8f'
                });
            } else {
                Swal.fire({
                    title: 'Notifikasi',
                    text: 'Semua pasien dalam kondisi normal',
                    icon: 'success',
                    confirmButtonColor: '#2a9d8f'
                });
            }
        }

        // ============================================
        // CALCULATIONS
        // ============================================
        function calculateTargetTpm() {
            const volume = document.getElementById('initialVolume').value;
            const factor = document.getElementById('dropFactor').value;
            const duration = document.getElementById('duration').value;
            if (volume && factor && duration) {
                const tpm = (volume * factor) / (duration * 60);
                document.getElementById('targetTpmDisplay').value = Math.round(tpm);
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ============================================
        // REFRESH & AUTO REFRESH
        // ============================================
        async function refreshData() {
            showAlert('Memperbarui data...', 'info');
            await loadMonitoringData(currentPage);
            await loadStats();
            showAlert('Data berhasil diperbarui', 'success');
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(() => {
                loadMonitoringData(currentPage);
                loadStats();
            }, 30000);
        }

        // ============================================
        // EVENT LISTENERS
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            setInterval(updateDateTime, 1000);
            
            loadMonitoringData(1);
            loadStats();
            startAutoRefresh();
            
            document.getElementById('patientForm').addEventListener('submit', savePatient);
            document.getElementById('initialVolume').addEventListener('input', calculateTargetTpm);
            document.getElementById('dropFactor').addEventListener('change', calculateTargetTpm);
            document.getElementById('duration').addEventListener('input', calculateTargetTpm);
            
            document.getElementById('deviceMenuBtn').addEventListener('click', function(e) {
                e.preventDefault();
                openDeviceModal();
            });
        });

        // Sidebar toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
</body>
</html>