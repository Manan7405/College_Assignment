<?php
/**
 * QUESTION 5 (part B) - PHP client that calls the Express API with cURL.
 *
 * IMPORTANT: the Express server (express_api/server.js) must be RUNNING
 * (npm start) before you open this page, otherwise you will see a
 * "cannot connect" message.
 *
 * If your Express server uses another port, change API_BASE_URL below.
 */
const API_BASE_URL = 'http://localhost:3000/api/students';

function h($text): string { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }

/**
 * Sends a GET request and returns the decoded JSON.
 * Throws a RuntimeException with a friendly message when anything goes wrong.
 */
function call_api(string $url): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is not enabled. Enable "extension=curl" in php.ini and restart Apache.');
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,                       // return the response as a string
        CURLOPT_CONNECTTIMEOUT => 3,                          // give up connecting after 3 s
        CURLOPT_TIMEOUT        => 8,                          // give up completely after 8 s
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);

    $body = curl_exec($ch);

    // 1. connection errors (server not running, wrong port, timeout ...)
    if ($body === false) {
        $curlError = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Cannot reach the Express API at ' . $url
            . '. Is the Express server running (npm start)? Details: ' . $curlError);
    }

    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 2. decode JSON
    try {
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new RuntimeException('The API returned invalid JSON (HTTP ' . $status . ').');
    }
    if (!is_array($data)) {
        throw new RuntimeException('The API returned an unexpected JSON structure.');
    }

    // 3. HTTP errors such as 404 / 400 / 500
    if ($status < 200 || $status >= 300) {
        $apiMessage = $data['error'] ?? 'Unknown error';
        throw new RuntimeException('The API answered with HTTP ' . $status . ': ' . $apiMessage);
    }

    return $data;
}

$students = [];
$error    = '';
$title    = 'All students';
$url      = API_BASE_URL;

// ?id=102 shows a single student
if (isset($_GET['id']) && $_GET['id'] !== '') {
    if (!ctype_digit((string)$_GET['id'])) {
        $error = 'The student id must be a whole number.';
    } else {
        $url   = API_BASE_URL . '/' . (int)$_GET['id'];
        $title = 'Student ' . (int)$_GET['id'];
    }
}

if ($error === '') {
    try {
        $result = call_api($url);
        if (!isset($result['data']) || !is_array($result['data'])) {
            throw new RuntimeException('The API response has no "data" field.');
        }
        // one student (an object with student_id) -> wrap into an array;  list -> use as it is
        $students = array_key_exists('student_id', $result['data']) ? [$result['data']] : $result['data'];
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Question 5 - PHP client for the Express API</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 860px; margin: 30px auto; padding: 0 15px; background: #f5f7fa; }
        .box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #0d6efd; color: #fff; }
        tr:nth-child(even) { background: #f2f6ff; }
        .err { background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 10px 14px; border-radius: 5px; margin: 10px 0; }
        code { background: #eee; padding: 1px 5px; border-radius: 3px; }
        input[type=text] { padding: 6px; width: 90px; }
        button { background: #0d6efd; color: #fff; border: 0; padding: 7px 14px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h2>Question 5: PHP client &rarr; Express API</h2>
    <p>API URL used: <code><?= h($url) ?></code></p>

    <form method="get" action="index.php">
        Find one student by ID: <input type="text" name="id" value="<?= h($_GET['id'] ?? '') ?>" placeholder="e.g. 102">
        <button type="submit">Search</button>
        <a href="index.php">Show all</a>
    </form>

    <h3><?= h($title) ?></h3>

    <?php if ($error !== ''): ?>
        <div class="err"><?= h($error) ?></div>
    <?php else: ?>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Marks</th></tr>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= h($s['student_id'] ?? '') ?></td>
                    <td><?= h($s['name'] ?? '') ?></td>
                    <td><?= h($s['email'] ?? '') ?></td>
                    <td><?= h($s['course'] ?? '') ?></td>
                    <td><?= h($s['marks'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
