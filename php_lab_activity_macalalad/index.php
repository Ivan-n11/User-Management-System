<?php
include("db_con.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_user"])) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name === "" || $email === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $email);

        if (!$stmt->execute()) {
            die("Error adding user: " . htmlspecialchars($stmt->error, ENT_QUOTES, "UTF-8"));
        }

        $stmt->close();
        header("Location: index.php?added=1");
        exit();
    }
}

if (isset($_GET["added"])) {
    $message = "User added successfully.";
} elseif (isset($_GET["updated"])) {
    $message = "User updated successfully.";
} elseif (isset($_GET["deleted"])) {
    $message = "User deleted successfully.";
}

$result = $conn->query("SELECT id, name, email FROM users ORDER BY id DESC");
if (!$result) {
    die("Error loading users: " . htmlspecialchars($conn->error, ENT_QUOTES, "UTF-8"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registered Users</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: black;
        }

        main {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin: 30px;
        }

        .panel {
            border: 1px solid gray;
            padding: 15px;
        }

        .field {
            margin-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 4px;
        }

        input {
            width: 100%;
            box-sizing: border-box;
        }

        .message {
            border: 1px solid gray;
            padding: 10px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid gray;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <main>
        <section class="panel left-panel">
            <?php if ($message !== ""): ?>
                <div class="message"><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></div>
            <?php endif; ?>

            <h1>Add New User</h1>
            <form method="POST" action="index.php">
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" type="text" name="name" required
                        value="<?= htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" required
                        value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                </div>
                <button type="submit" name="add_user" value="1">Submit</button>
            </form>
        </section>

        <section class="panel right-panel">
            <h2>Registered Users</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $row["id"] ?></td>
                            <td><?= htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") ?></td>
                            <td><?= htmlspecialchars($row["email"], ENT_QUOTES, "UTF-8") ?></td>
                            <td>
                                <a class="btn btn-primary" href="edit.php?id=<?= (int) $row["id"] ?>"> Edit</a>
                                <form method="POST" action="delete.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= (int) $row["id"] ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
