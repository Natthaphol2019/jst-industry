<?php
$currentFile = basename($_SERVER['PHP_SELF']);

// base path
$adminRoot = '/admin/';      // ไฟล์ที่อยู่ใน admin ตรง ๆ
$adminTime = '/admin/time/'; // ไฟล์ที่อยู่ใน admin/time
$employeeBase = '/employee/';
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar border-end">
    <div class="position-sticky">

        <div class="menu-title">เมนูผู้ดูแล</div>

        <ul class="nav flex-column mb-3">

            <li class="nav-item">
                <a class="nav-link <?= $currentFile === 'admin_dashboard.php' ? 'active' : ''; ?>"
                   href="<?= $adminRoot ?>admin_dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    แดชบอร์ด
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $currentFile === 'admin_time_batch_select.php' ? 'active' : ''; ?>"
                   href="<?= $adminTime ?>admin_time_batch_select.php">
                    <i class="bi bi-calendar-check"></i>
                    บันทึกเวลาจากบัตรตอก (15 วัน)
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $currentFile === 'admin_time_summary.php' ? 'active' : ''; ?>"
                   href="<?= $adminTime ?>admin_time_summary.php">
                    <i class="bi bi-list-check"></i>
                    สรุปเวลาทำงาน 15 วัน
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $currentFile === 'admin_time_edit.php' ? 'active' : ''; ?>"
                   href="<?= $adminTime ?>admin_time_edit.php">
                    <i class="bi bi-pencil-square"></i>
                    แก้ไขเวลาย้อนหลัง
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $currentFile === 'report_monthly.php' ? 'active' : ''; ?>"
                   href="<?= $adminTime ?>report_monthly.php">
                    <i class="bi bi-file-bar-graph"></i>
                    รายงานเวลาทำงานทั้งเดือน
                </a>
            </li>

            <li class="nav-item mt-2">
                <a class="nav-link <?= $currentFile === 'employee_list.php' ? 'active' : ''; ?>"
                   href="<?= $employeeBase ?>employee_list.php">
                    <i class="bi bi-people-fill"></i>
                    จัดการข้อมูลพนักงาน
                </a>
            </li>

        </ul>
    </div>
</nav>
