<?php
include("db_con.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit("Delete requests must use POST.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit("A valid user ID is required.");
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    die("Error deleting user: " . htmlspecialchars($stmt->error, ENT_QUOTES, "UTF-8"));
}
$deleted = $stmt->affected_rows;
$stmt->close();

if ($deleted !== 1) {
    http_response_code(404);
    exit("User not found.");
}

header("Location: index.php?deleted=1");
exit();
