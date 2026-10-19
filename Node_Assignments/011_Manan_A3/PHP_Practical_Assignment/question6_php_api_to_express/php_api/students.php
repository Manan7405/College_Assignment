<?php
/**
 * QUESTION 6 (part A) - PHP REST API for student records.
 *
 *   GET students.php          -> all students
 *   GET students.php?id=102   -> one student (404 if not found)
 *
 * Data comes from the MySQL "students" table (the same table used in
 * Questions 2 and 3). Run Question 2 first to fill the table.
 *
 * Status codes used:
 *   200 OK, 400 Bad Request (bad id), 404 Not Found,
 *   405 Method Not Allowed (anything except GET), 500 Server Error (database problem)
 */
require_once __DIR__ . '/../../common/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

/** Sends a JSON answer with the given HTTP status code and stops the script. */
function send_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---- only GET is supported ----
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    send_json(405, ['success' => false, 'error' => 'Method ' . $_SERVER['REQUEST_METHOD'] . ' is not allowed. Use GET.']);
}

/** Makes sure numbers are real numbers in the JSON (not strings). */
function format_student(array $row): array
{
    return [
        'student_id' => (int)$row['student_id'],
        'name'       => $row['name'],
        'email'      => $row['email'],
        'course'     => $row['course'],
        'marks'      => (float)$row['marks'],
    ];
}

try {
    $pdo = get_db_connection();

    // ---- one student: ?id=102 ----
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            send_json(400, ['success' => false, 'error' => 'The id must be a positive whole number.']);
        }

        $stmt = $pdo->prepare('SELECT student_id, name, email, course, marks FROM students WHERE student_id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            send_json(404, ['success' => false, 'error' => 'Student with id ' . $id . ' was not found.']);
        }
        send_json(200, ['success' => true, 'data' => format_student($row)]);
    }

    // ---- all students ----
    $rows = $pdo->query('SELECT student_id, name, email, course, marks FROM students ORDER BY student_id')->fetchAll();
    $students = array_map('format_student', $rows);

    send_json(200, ['success' => true, 'count' => count($students), 'data' => $students]);

} catch (PDOException $e) {
    // do not reveal database details to the client
    send_json(500, ['success' => false, 'error' => 'Database error. Make sure MySQL is running in XAMPP.']);
}
