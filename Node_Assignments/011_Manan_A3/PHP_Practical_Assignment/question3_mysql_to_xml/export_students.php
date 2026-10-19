<?php
/**
 * QUESTION 3 - Read the students table and save it as students_export.xml.
 *
 * Uses DOMDocument. Text is added with createTextNode(), which automatically
 * escapes special characters (& < > " ') so the XML is always valid.
 *
 * The output uses exactly the same element names as the Question 2 input
 * (<students><student><student_id>...), so it can be imported again.
 */
require_once __DIR__ . '/../common/db_connect.php';

function h($text): string { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }

$outputFile = __DIR__ . '/students_export.xml';
$ran        = ($_SERVER['REQUEST_METHOD'] === 'POST');
$message    = '';
$isError    = false;
$rows       = [];

if ($ran) {
    try {
        // 1. connect and read all students
        $pdo  = get_db_connection();
        $rows = $pdo->query('SELECT student_id, name, email, course, marks FROM students ORDER BY student_id')->fetchAll();

        if (!$rows) {
            throw new RuntimeException('The students table is empty. Run Question 2 first (or import database/students.sql).');
        }

        // 2. build the XML document
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;                       // pretty printing (indentation)

        $root = $dom->createElement('students');         // root element
        $dom->appendChild($root);

        foreach ($rows as $row) {
            $student = $dom->createElement('student');
            foreach (['student_id', 'name', 'email', 'course', 'marks'] as $field) {
                $element = $dom->createElement($field);
                $element->appendChild($dom->createTextNode((string)$row[$field]));   // escapes & < > automatically
                $student->appendChild($element);
            }
            $root->appendChild($student);
        }

        // 3. save to disk
        $bytes = @$dom->save($outputFile);
        if ($bytes === false) {
            throw new RuntimeException('Could not write students_export.xml. Check that the folder is writable.');
        }

        $message = count($rows) . ' student record(s) were exported to students_export.xml (' . $bytes . ' bytes).';
    } catch (PDOException $e) {
        $isError = true;
        $message = 'Database error. Is MySQL running in XAMPP? (Details: ' . $e->getMessage() . ')';
    } catch (RuntimeException $e) {
        $isError = true;
        $message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Question 3 - Export students to XML</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 820px; margin: 30px auto; padding: 0 15px; background: #f5f7fa; }
        .box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
        .ok { background: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 10px 14px; border-radius: 5px; margin: 10px 0; }
        .err { background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 10px 14px; border-radius: 5px; margin: 10px 0; }
        button { background: #0d6efd; color: #fff; border: 0; padding: 9px 18px; border-radius: 5px; cursor: pointer; font-size: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
        th { background: #eee; }
        code { background: #eee; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Question 3: Export MySQL <code>students</code> table to XML</h2>
    <form method="post" action="export_students.php">
        <button type="submit">Export now</button>
    </form>

    <?php if ($ran): ?>
        <hr>
        <div class="<?= $isError ? 'err' : 'ok' ?>"><?= h($message) ?></div>

        <?php if (!$isError): ?>
            <p><a href="students_export.xml" target="_blank">Open students_export.xml</a></p>
            <table>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Marks</th></tr>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= h($row['student_id']) ?></td><td><?= h($row['name']) ?></td>
                        <td><?= h($row['email']) ?></td><td><?= h($row['course']) ?></td><td><?= h($row['marks']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
