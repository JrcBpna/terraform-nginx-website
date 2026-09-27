<?php

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Method not allowed.");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Invalid student ID.");
}

$stmt = $pdo->prepare(
    "DELETE FROM students
     WHERE id = :id"
);

$stmt->execute([
    ':id' => $id
]);

header("Location: index.php?deleted=1");
exit;