<?php
include "connection.php";
include "nav.php";

$id = $_GET['id'];

// ดึงข้อมูลเดิม
$stmt = $pdo->prepare("SELECT * FROM inventory WHERE id_inventory = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่ทั้งหมด
$catStmt = $pdo->query("SELECT * FROM item_category");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// อัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $sql = "UPDATE inventory SET 
        code_item = ?, 
        name_equipment = ?, 
        type = ?, 
        unit = ?, 
        current_stock = ?, 
        min_stock = ?, 
        location = ?, 
        status = ?, 
        id_item_category = ?
        WHERE id_inventory = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['code_item'],
        $_POST['name_equipment'],
        $_POST['type'],
        $_POST['unit'],
        $_POST['current_stock'],
        $_POST['min_stock'],
        $_POST['location'],
        $_POST['status'],
        $_POST['id_item_category'],
        $id
    ]);

    header("Location: inventory.php");
    exit;
}
?>

<h2>Edit Inventory</h2>

<form method="POST">
    <label>Code Item:</label><br>
    <input type="text" name="code_item" value="<?= $data['code_item'] ?>" required><br><br>

    <label>Name Equipment:</label><br>
    <input type="text" name="name_equipment" value="<?= $data['name_equipment'] ?>" required><br><br>

    <label>Type:</label><br>
    <input type="text" name="type" value="<?= $data['type'] ?>"><br><br>

    <label>Unit:</label><br>
    <input type="text" name="unit" value="<?= $data['unit'] ?>"><br><br>

    <label>Current Stock:</label><br>
    <input type="number" name="current_stock" value="<?= $data['current_stock'] ?>"><br><br>

    <label>Min Stock:</label><br>
    <input type="number" name="min_stock" value="<?= $data['min_stock'] ?>"><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" value="<?= $data['location'] ?>"><br><br>

    <label>Status:</label><br>
    <input type="text" name="status" value="<?= $data['status'] ?>"><br><br>

    <label>Category:</label><br>
    <select name="id_item_category">
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id_item_category'] ?>" 
                <?= $c['id_item_category'] == $data['id_item_category'] ? 'selected' : '' ?>>
                <?= $c['name_category'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">บันทึก</button>
</form>
