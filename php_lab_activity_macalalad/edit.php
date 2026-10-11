<?php
include("db_con.php");

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit("A valid user ID is required.");
}

$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    die("Error loading user: " . htmlspecialchars($stmt->error, ENT_QUOTES, "UTF-8"));
}
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    exit("User not found.");
}

$message = "";
$name = $user["name"];
$email = $user["email"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_user"])) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name === "" || $email === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $id);
        if (!$stmt->execute()) {
            die("Error updating user: " . htmlspecialchars($stmt->error, ENT_QUOTES, "UTF-8"));
        }
        $stmt->close();

        header("Location: index.php?updated=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit User</title>
</head>
<body>
<main class="container py-4">
    <h1 class="h2 mb-3">Edit User</h1>

    <?php if ($message !== ""): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?= (int) $id ?>" class="mb-3" style="max-width: 32rem;">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input id="name" class="form-control" type="text" name="name" required
                   value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" class="form-control" type="email" name="email" required
                   value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>">
        </div>
        <button class="btn btn-primary" type="submit" name="update_user" value="1">Update User</button>
        <a class="btn btn-secondary" href="index.php">Cancel</a>
    </form>
</main>
</body>
</html>
