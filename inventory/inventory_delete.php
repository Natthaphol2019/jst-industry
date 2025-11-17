<?php
include "connection.php";

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM inventory WHERE id_inventory = ?");
$stmt->execute([$id]);

header("Location: inventory.php");
exit;
?>
