<?php
include "connection.php";
include "nav.php";

$id = $_GET['id'];

// ดึงข้อมูลเดิม
$stmt = $pdo->prepare("SELECT * FROM item_category WHERE id_item_category = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// ถ้ากดปุ่มบันทึก
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $sql = "UPDATE item_category 
            SET name_category = ?, description_category = ? 
            WHERE id_item_category = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['name_category'], $_POST['description_category'], $id]);

    header("Location: item_category.php");
    exit;
}
?>

<h2>Edit Item Category</h2>

<form method="POST">
    <label>Name Category:</label><br>
    <input type="text" name="name_category" value="<?= $data['name_category'] ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description_category"><?= $data['description_category'] ?></textarea><br><br>

    <button type="submit">บันทึก</button>
</form>
