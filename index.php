<?php

$configFile = '/etc/webapp/db.json';

if (!file_exists($configFile)) {
    die("Database configuration file not found.");
}

$config = json_decode(file_get_contents($configFile), true);

if (
    !isset(
        $config['host'],
        $config['database'],
        $config['username'],
        $config['password']
    )
) {
    die("Invalid database configuration.");
}

$host     = $config['host'];
$dbname   = $config['database'];
$username = $config['username'];
$password = $config['password'];

try {

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $stmt = $pdo->query(
        "SELECT id, name, course, created_at
         FROM students
         ORDER BY id"
    );

    $students = $stmt->fetchAll();

} catch (PDOException $e) {

    die("Database connection failed.");

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Management System</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 40px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
        }

        h1 {
            text-align: center;
        }

        .info {
            text-align: center;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #eee;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Student Management System</h1>

    <div class="info">

        <p>
            AWS EC2 + Nginx + PHP + RDS MySQL
        </p>

    </div>

    <table>

        <thead>

        <tr>

            <th>ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Created At</th>

        </tr>

        </thead>

        <tbody>

        <?php foreach ($students as $student): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($student['id']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($student['name']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($student['course']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($student['created_at']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

</body>

</html>
