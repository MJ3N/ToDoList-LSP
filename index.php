<?php
session_start();

// Inisialisasi array
if (!isset($_SESSION['tasks']) || !is_array($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Tambah tugas baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_task'])) {
    $taskText = trim($_POST['new_task']);
    if ($taskText !== '') {
        $_SESSION['tasks'][] = [
            'task' => htmlspecialchars($taskText),
            'created' => date("Y-m-d H:i:s"),
            'status' => 'Belum'
        ];
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Edit tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_index'], $_POST['edit_task'])) {
    $index = $_POST['edit_index'];
    $newText = trim($_POST['edit_task']);
    if ($newText !== '' && isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['task'] = htmlspecialchars($newText);
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Toggle status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $index = $_GET['toggle'];
    if (isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['status'] =
            $_SESSION['tasks'][$index]['status'] === 'Belum' ? 'Selesai' : 'Belum';
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Hapus tugas
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $index = $_GET['delete'];
    if (isset($_SESSION['tasks'][$index])) {
        unset($_SESSION['tasks'][$index]);
        $_SESSION['tasks'] = array_values($_SESSION['tasks']);
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Hapus semua
if (isset($_GET['clear'])) {
    $_SESSION['tasks'] = [];
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>To Do List Me</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f4;
            color: #222;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            margin-bottom: 20px;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
        }

        button {
            padding: 10px 16px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        button:hover {
            background: #555;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background: #fafafa;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .left {
            display: flex;
            align-items: center;
        }

        .left form {
            margin-right: 10px;
        }

        .task-done {
            text-decoration: line-through;
            color: green;
        }

        .info {
            font-size: 12px;
            color: #777;
        }

        .hapus {
            color: red;
            text-decoration: none;
        }

        .clear {
            margin-top: 15px;
            text-align: right;
        }

        .clear a {
            color: #888;
            text-decoration: none;
        }

        .clear a:hover {
            color: red;
        }

        @media (prefers-color-scheme: dark) {
            body { background: #1e1e1e; color: #eee; }
            .container { background: #2c2c2c; }
            input[type="text"] { background: #444; color: #fff; border: 1px solid #666; }
            li { background: #333; border-color: #444; }
            .info { color: #aaa; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 To Do List Me</h1>

        <form method="POST">
            <input type="text" name="new_task" placeholder="Tulis tugas baru..." required>
            <button type="submit">Tambah</button>
        </form>

        <ul>
            <?php if (!empty($_SESSION['tasks'])): ?>
                <?php foreach ($_SESSION['tasks'] as $index => $item): ?>
                    <li>
                        <div class="left">
                            <!-- Checkbox toggle -->
                            <form method="get" style="margin:0;">
                                <input type="hidden" name="toggle" value="<?= $index ?>">
                                <input type="checkbox" onchange="this.form.submit()" <?= $item['status'] === 'Selesai' ? 'checked' : '' ?>>
                            </form>

                            <div>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $index): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="edit_index" value="<?= $index ?>">
                                        <input type="text" name="edit_task" value="<?= htmlspecialchars($item['task']) ?>" required>
                                        <button type="submit">💾</button>
                                    </form>
                                <?php else: ?>
                                    <span class="<?= $item['status'] === 'Selesai' ? 'task-done' : '' ?>">
                                        <?= htmlspecialchars($item['task']) ?>
                                    </span><br>
                                    <span class="info">
                                        Status: <?= $item['status'] ?> |
                                        Dibuat: <?= $item['created'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <a class="hapus" href="?delete=<?= $index ?>" onclick="return confirm('Hapus tugas ini?')">❌</a>
                            <a href="?edit=<?= $index ?>" style="margin-left: 8px;" title="Edit">✏️</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><i>Belum ada tugas.</i></li>
            <?php endif; ?>
        </ul>

        <?php if (!empty($_SESSION['tasks'])): ?>
            <div class="clear">
                <a href="?clear" onclick="return confirm('Yakin hapus semua tugas?')">🗑 Hapus Semua</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
