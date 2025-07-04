<?php
require_once('../includes/db_connect.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: gestion_utilisateurs.php?deleted=success");
    } else {
        header("Location: gestion_utilisateurs.php?deleted=error");
    }
    $stmt->close();
} else {
    header("Location: gestion_utilisateurs.php");
}
?>
