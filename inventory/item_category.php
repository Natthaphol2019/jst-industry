<?php
include "connection.php";
include "nav.php";

// INSERT
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $sql = "INSERT INTO item_category (name_category, description_category) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['name_category'], $_POST['description_category']]);
    echo "<p style='color:green;'>เพิ่มข้อมูลเรียบร้อย</p>";
}

// SELECT
$stmt = $pdo->query("SELECT * FROM item_category");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Item Category</h2>

<!-- Form Insert -->
<form method="POST">
    <label>Name Category:</label><br>
    <input type="text" name="name_category" required><br><br>

    <label>Description:</label><br>
    <textarea name="description_category"></textarea><br><br>

    <button type="submit">เพิ่มข้อมูล</button>
</form>

<hr>

<h3>ตารางข้อมูล Item Category</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Name Category</th>
        <th>Description</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>

    <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= $cat['id_item_category'] ?></td>
            <td><?= $cat['name_category'] ?></td>
            <td><?= $cat['description_category'] ?></td>
            <td><a href="item_category_edit.php?id=<?= $cat['id_item_category'] ?>">Edit</a></td>
            <td><a href="item_category_delete.php?id=<?= $cat['id_item_category'] ?>" onclick="return confirm('ต้องการลบข้อมูลนี้หรือไม่?');">Delete</a></td>
        </tr>
    <?php endforeach; ?>
</table>
