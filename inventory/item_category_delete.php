<?php
require 'connection.php'; // ให้แน่ใจว่าไฟล์นี้กำหนด $pdo

$id = $_GET['id'] ?? 0;

try {
    // เตรียมคำสั่ง
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE id_item_category = ?");
    $stmt->execute([$id]);

    // วิธีที่ 1 (ตรงที่สุด) — fetchColumn()
    $count = (int) $stmt->fetchColumn();

    if ($count > 0) {
        echo "<script>alert('ไม่สามารถลบได้: ยังมีข้อมูลใน inventory ใช้หมวดหมู่นี้อยู่'); window.location='item_category.php';</script>";
        exit;
    }

    // ถ้าไม่มีรายการ จึงลบ
    $del = $pdo->prepare("DELETE FROM item_category WHERE id_item_category = ?");
    $del->execute([$id]);

    header("Location: item_category.php");
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
