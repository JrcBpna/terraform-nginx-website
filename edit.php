<?php

require_once __DIR__ . '/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Invalid student ID.");
}

$stmt = $pdo->prepare(
    "SELECT id, name, course
     FROM students
     WHERE id = :id"
);

$stmt->execute([
    ':id' => $id
]);

$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if ($name === '' || $course === '') {

        $error = "Name and course are required.";

    } elseif (strlen($name) > 100 || strlen($course) > 100) {

        $error = "Name and course must be less than 100 characters.";

    } else {

        try {

            $stmt = $pdo->prepare(
                "UPDATE students
                 SET name = :name,
                     course = :course
                 WHERE id = :id"
            );

            $stmt->execute([
                ':name'   => $name,
                ':course' => $course,
                ':id'     => $id
            ]);

            header("Location: index.php?updated=1");
            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $error = "Unable to update student.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Student</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Edit Student</h1>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="name">
                    Student Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="100"
                    value="<?= htmlspecialchars($student['name']) ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="course">
                    Course
                </label>

                <input
                    type="text"
                    id="course"
                    name="course"
                    maxlength="100"
                    value="<?= htmlspecialchars($student['course']) ?>"
                    required
                >

            </div>

            <button type="submit">
                Update Student
            </button>

            <a class="button-link" href="index.php">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>

</html>