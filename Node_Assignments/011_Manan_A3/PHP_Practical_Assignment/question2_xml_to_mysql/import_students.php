<?php
/**
 * QUESTION 2 - Read students.xml and store the data in MySQL.
 *
 * Steps:
 *   1. Choose an XML file (default: students.xml in this folder).
 *   2. Parse it with SimpleXML (and report XML errors nicely).
 *   3. Connect to MySQL with PDO (common/db_connect.php).
 *   4. Insert every <student> using ONE prepared statement.
 *   5. Show how many records were imported / skipped / invalid.
 *
 * DUPLICATE IDs: student_id is the PRIMARY KEY. If a student with the same
 * id already exists, the record is SKIPPED (the old row is kept unchanged)
 * and counted as "duplicate". This makes it safe to run the import twice.
 */
require_once __DIR__ . '/../common/db_connect.php';

// Only these files may be chosen (a whitelist, so nobody can read arbitrary files)
$allowedFiles = [
    'students' => ['label' => 'students.xml (this folder)',
                   'path'  => __DIR__ . '/students.xml'],
    'export'   => ['label' => 'students_export.xml (created by Question 3)',
                   'path'  => __DIR__ . '/../question3_mysql_to_xml/students_export.xml'],
];

function h($text): string { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }

$selected = $_POST['source'] ?? 'students';
if (!isset($allowedFiles[$selected])) {
    $selected = 'students';
}

$ran      = ($_SERVER['REQUEST_METHOD'] === 'POST');
$fatal    = '';          // a problem that stops the whole import
$imported = 0;
$duplicates = [];
$invalid  = [];
$total    = 0;

if ($ran) {
    $file = $allowedFiles[$selected]['path'];

    try {
        // ---- 1. check the file ----
        if (!is_file($file) || !is_readable($file)) {
            throw new RuntimeException('The XML file was not found: ' . basename($file)
                . ($selected === 'export' ? ' (run Question 3 first)' : ''));
        }

        // ---- 2. parse the XML ----
        libxml_use_internal_errors(true);                       // collect errors instead of printing warnings
        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NONET);
        if ($xml === false) {
            $messages = [];
            foreach (libxml_get_errors() as $err) {
                $messages[] = 'Line ' . $err->line . ': ' . trim($err->message);
            }
            libxml_clear_errors();
            throw new RuntimeException('The XML file is not valid. ' . implode(' | ', $messages));
        }
        if ($xml->getName() !== 'students') {
            throw new RuntimeException('The root element must be <students>, found <' . $xml->getName() . '>.');
        }

        // ---- 3. connect to MySQL ----
        $pdo = get_db_connection();

        // ---- 4. one prepared statement, executed once per student ----
        $insert = $pdo->prepare(
            'INSERT INTO students (student_id, name, email, course, marks)
             VALUES (:student_id, :name, :email, :course, :marks)'
        );

        $position = 0;
        foreach ($xml->student as $student) {
            $position++;
            $total++;

            // read each child element as trimmed text
            $id     = trim((string)$student->student_id);
            $name   = trim((string)$student->name);
            $email  = trim((string)$student->email);
            $course = trim((string)$student->course);
            $marks  = trim((string)$student->marks);

            // validate the record before touching the database
            if (!ctype_digit($id) || (int)$id < 1) {
                $invalid[] = "Record #$position: invalid student_id '" . $id . "'"; continue;
            }
            if ($name === '' || mb_strlen($name) > 100) {
                $invalid[] = "Record #$position (ID $id): name is missing or too long"; continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
                $invalid[] = "Record #$position (ID $id): invalid email '" . $email . "'"; continue;
            }
            if ($course === '' || mb_strlen($course) > 100) {
                $invalid[] = "Record #$position (ID $id): course is missing or too long"; continue;
            }
            if (!is_numeric($marks) || (float)$marks < 0 || (float)$marks > 100) {
                $invalid[] = "Record #$position (ID $id): marks must be a number from 0 to 100"; continue;
            }

            try {
                $insert->execute([
                    ':student_id' => (int)$id,
                    ':name'       => $name,
                    ':email'      => $email,
                    ':course'     => $course,
                    ':marks'      => round((float)$marks, 2),
                ]);
                $imported++;
            } catch (PDOException $e) {
                // MySQL error 1062 = duplicate entry for the PRIMARY KEY
                if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                    $duplicates[] = $id;
                } else {
                    $invalid[] = "Record #$position (ID $id): database error";
                }
            }
        }

        if ($total === 0) {
            throw new RuntimeException('The XML file contains no <student> elements.');
        }
    } catch (PDOException $e) {
        $fatal = 'Database error: could not connect or run the query. Is MySQL running in XAMPP? '
               . '(Details: ' . $e->getMessage() . ')';
    } catch (RuntimeException $e) {
        $fatal = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Question 2 - Import students.xml into MySQL</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 760px; margin: 30px auto; padding: 0 15px; background: #f5f7fa; }
        .box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
        .ok { background: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 10px 14px; border-radius: 5px; margin: 10px 0; }
        .err { background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 10px 14px; border-radius: 5px; margin: 10px 0; }
        .warn { background: #fff3cd; border: 1px solid #ffecb5; color: #664d03; padding: 10px 14px; border-radius: 5px; margin: 10px 0; }
        button { background: #0d6efd; color: #fff; border: 0; padding: 9px 18px; border-radius: 5px; cursor: pointer; font-size: 15px; }
        select { padding: 8px; font-size: 15px; }
        code { background: #eee; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Question 2: Import <code>students.xml</code> into MySQL</h2>
    <p>Database: <code><?= h(DB_NAME) ?></code>, table: <code>students</code>. Existing student IDs are skipped (not overwritten).</p>

    <form method="post" action="import_students.php">
        <label for="source">XML file to import:</label>
        <select name="source" id="source">
            <?php foreach ($allowedFiles as $key => $info): ?>
                <option value="<?= h($key) ?>" <?= $key === $selected ? 'selected' : '' ?>><?= h($info['label']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Import now</button>
    </form>

    <?php if ($ran): ?>
        <hr>
        <h3>Result</h3>
        <?php if ($fatal !== ''): ?>
            <div class="err"><strong>Import failed.</strong> <?= h($fatal) ?></div>
        <?php else: ?>
            <div class="ok"><strong><?= $imported ?></strong> record(s) imported successfully out of <?= $total ?> found in the XML file.</div>
            <?php if ($duplicates): ?>
                <div class="warn"><strong><?= count($duplicates) ?></strong> duplicate record(s) skipped because the student ID already exists:
                    <?= h(implode(', ', $duplicates)) ?></div>
            <?php endif; ?>
            <?php if ($invalid): ?>
                <div class="err"><strong><?= count($invalid) ?></strong> invalid record(s) skipped:
                    <ul><?php foreach ($invalid as $msg): ?><li><?= h($msg) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
