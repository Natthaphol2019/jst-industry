<?php
include "connection.php";
include "nav.php";

// INSERT INVENTORY
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $sql = "INSERT INTO inventory 
        (code_item, name_equipment, type, unit, current_stock, min_stock, location, status, id_item_category) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
        $_POST['id_item_category']
    ]);

    echo "<p style='color:green;'>เพิ่มข้อมูล Inventory แล้ว</p>";
}

// ดึง category มาแสดงใน dropdown
$catStmt = $pdo->query("SELECT * FROM item_category ORDER BY name_category");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// SELECT JOIN inventory + item_category
$sql = "SELECT i.*, c.name_category 
        FROM inventory i 
        LEFT JOIN item_category c 
        ON i.id_item_category = c.id_item_category";

$stmt = $pdo->query($sql);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Inventory</h2>

<!-- Form Insert -->
<form method="POST">
    <label>Code Item:</label><br>
    <input type="text" name="code_item" required><br><br>

    <label>Name Equipment:</label><br>
    <input type="text" name="name_equipment" required><br><br>

    <label>Type:</label><br>
    <input type="text" name="type"><br><br>

    <label>Unit:</label><br>
    <input type="text" name="unit"><br><br>

    <label>Current Stock:</label><br>
    <input type="number" name="current_stock"><br><br>

    <label>Min Stock:</label><br>
    <input type="number" name="min_stock"><br><br>

    <label>Location:</label><br>
    <input type="text" name="location"><br><br>

    <label>Status:</label><br>
    <input type="text" name="status"><br><br>

    <label>Category:</label><br>
    <select name="id_item_category" required>
        <option value="">-- เลือกหมวดหมู่ --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id_item_category'] ?>">
                <?= $cat['name_category'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">เพิ่มข้อมูล</button>
</form>

<hr>

<h3>ตาราง Inventory</h3>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Code</th>
        <th>Name</th>
        <th>Type</th>
        <th>Unit</th>
        <th>Stock</th>
        <th>Min</th>
        <th>Location</th>
        <th>Status</th>
        <th>Category</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>

    <?php foreach ($inventory as $i): ?>
        <tr>
            <td><?= $i['id_inventory'] ?></td>
            <td><?= $i['code_item'] ?></td>
            <td><?= $i['name_equipment'] ?></td>
            <td><?= $i['type'] ?></td>
            <td><?= $i['unit'] ?></td>
            <td><?= $i['current_stock'] ?></td>
            <td><?= $i['min_stock'] ?></td>
            <td><?= $i['location'] ?></td>
            <td><?= $i['status'] ?></td>
            <td><?= $i['name_category'] ?></td>
            <td><a href="inventory_edit.php?id=<?= $i['id_inventory'] ?>">Edit</a></td>
            <td><a href="inventory_delete.php?id=<?= $i['id_inventory'] ?>" onclick="return confirm('ต้องการลบข้อมูลนี้หรือไม่?')">Delete</a></td>
        </tr>
    <?php endforeach; ?>
</table>
