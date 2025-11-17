<?php
// partials/header.php

// กำหนด title เริ่มต้น ถ้าไฟล์หลักไม่ได้ set $pageTitle มาก่อน
if (!isset($pageTitle)) {
    $pageTitle = 'Time Attendance System';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (เอาไว้ใช้ไอคอนใน sidebar) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
        }

        /* ===== Sidebar Style ===== */
        .sidebar {
            background: #ffffff;
            min-height: 100vh;
            padding-top: 10px;
        }

        .sidebar .menu-title {
            font-size: 13px;
            padding-left: 16px;
            margin-bottom: 6px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar .nav-link {
            color: #444;
            font-size: 15px;
            padding: 10px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            font-size: 1rem;
        }

        .sidebar .nav-link:hover {
            background: #f0f6ff;
            color: #0d6efd;
        }

        .sidebar .nav-link.active {
            background: #e7f1ff;
            color: #0b66d1 !important;
            font-weight: 600;
            border-left: 4px solid #0d6efd;
            padding-left: 12px;
        }

        .card-stat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
    </style>
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            ระบบบันทึกเวลาทำงาน
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <?php if (isset($currentUser)): ?>
                    <li class="nav-item me-3">
                        <span class="navbar-text text-white">
                            สวัสดี, <?php echo htmlspecialchars($currentUser['username']); ?>
                            <?php if (!empty($currentUser['role'])): ?>
                                <span class="badge bg-light text-primary ms-1">
                                    <?php echo strtoupper(htmlspecialchars($currentUser['role'])); ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
