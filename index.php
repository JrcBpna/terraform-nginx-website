<?php

require_once __DIR__ . '/db.php';

$error = "";

/*
|--------------------------------------------------------------------------
| CREATE STUDENT
|--------------------------------------------------------------------------
*/

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
                "INSERT INTO students (name, course)
                 VALUES (:name, :course)"
            );

            $stmt->execute([
                ':name'   => $name,
                ':course' => $course
            ]);

            /*
             * Redirect after successful POST.
             * Prevents duplicate insert when refreshing browser.
             */
            header("Location: index.php?created=1");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $error = "This student and course combination already exists.";
            } else {

                error_log($e->getMessage());

                $error = "Unable to add student.";
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| READ STUDENTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT id, name, course, created_at
     FROM students
     ORDER BY id DESC"
);

$students = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Management System</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<div class="container">

    <h1>Student Management System</h1>

    <p class="subtitle">
        AWS EC2 + Nginx + PHP + RDS MySQL
    </p>


    <!-- ADD STUDENT -->

    <div class="card">

        <h2>Add Student</h2>

        <?php if (isset($_GET['created'])): ?>

            <div class="success">
                Student added successfully.
            </div>

        <?php endif; ?>


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
                    required
                >

            </div>


            <button type="submit">
                Add Student
            </button>

        </form>

    </div>


    <!-- STUDENT LIST -->

    <div class="card">

        <h2>Students</h2>

        <?php if (count($students) === 0): ?>

            <p>No students found.</p>

        <?php else: ?>

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

        <?php endif; ?>

    </div>

</div>

</body>

</html>