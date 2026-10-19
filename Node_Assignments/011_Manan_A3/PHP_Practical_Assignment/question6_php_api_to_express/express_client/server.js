/**
 * QUESTION 6 (part B) - Express.js client that calls the PHP REST API.
 *
 *   http://localhost:3001/       -> students shown in an HTML table
 *   http://localhost:3001/json   -> the raw JSON received from the PHP API
 *
 * It uses the fetch() function that is built into Node.js 18 or newer,
 * together with async/await.
 *
 * ---- WHERE IS THE PHP API? ----
 * PHP_API_URL below must be the URL of php_api/students.php in XAMPP.
 *   * Apache on the default port 80 :  http://localhost/PHP_Practical_Assignment/...
 *   * Apache on port 8080           :  http://localhost:8080/PHP_Practical_Assignment/...
 * Change the default below OR set an environment variable, for example (Windows):
 *     set PHP_API_URL=http://localhost:8080/PHP_Practical_Assignment/question6_php_api_to_express/php_api/students.php
 *     npm start
 */
const express = require('express');

const app  = express();
const PORT = process.env.PORT || 3001;

const PHP_API_URL = process.env.PHP_API_URL ||
  'http://localhost/PHP_Practical_Assignment/question6_php_api_to_express/php_api/students.php';

/** Prevents HTML injection when we print API data into a web page. */
function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/**
 * Calls the PHP API. Returns the parsed JSON.
 * Throws an Error (with a "status" number) when something goes wrong.
 */
async function fetchStudents() {
  let response;
  try {
    // AbortSignal.timeout stops waiting after 5 seconds
    response = await fetch(PHP_API_URL, {
      headers: { Accept: 'application/json' },
      signal: AbortSignal.timeout(5000)
    });
  } catch (err) {
    // network problem: XAMPP/Apache not running, wrong URL or port, timeout
    const error = new Error('Cannot connect to the PHP API at ' + PHP_API_URL +
      '. Is Apache running in XAMPP and is the URL correct? (' + err.message + ')');
    error.status = 502;
    throw error;
  }

  let data;
  try {
    data = await response.json();
  } catch (err) {
    const error = new Error('The PHP API did not return valid JSON (HTTP ' + response.status + '). ' +
      'Check the URL - it may be showing a PHP error or a 404 page.');
    error.status = 502;
    throw error;
  }

  if (!response.ok) {
    const error = new Error('The PHP API replied with HTTP ' + response.status + ': ' + (data.error || 'unknown error'));
    error.status = 502;
    throw error;
  }
  return data;
}

// ---- raw JSON ----
app.get('/json', async (req, res) => {
  try {
    res.json(await fetchStudents());
  } catch (err) {
    res.status(err.status || 500).json({ success: false, error: err.message });
  }
});

// ---- HTML table ----
app.get('/', async (req, res) => {
  try {
    const result = await fetchStudents();
    const students = Array.isArray(result.data) ? result.data : [];

    const rows = students.map((s) =>
      '<tr><td>' + escapeHtml(s.student_id) + '</td><td>' + escapeHtml(s.name) + '</td><td>' +
      escapeHtml(s.email) + '</td><td>' + escapeHtml(s.course) + '</td><td>' + escapeHtml(s.marks) + '</td></tr>'
    ).join('');

    res.send(page('<p>Data received from: <code>' + escapeHtml(PHP_API_URL) + '</code></p>' +
      '<p><a href="/json">View raw JSON</a></p>' +
      (students.length === 0
        ? '<div class="err">The PHP API returned no students. Run Question 2 first.</div>'
        : '<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Marks</th></tr>' + rows + '</table>')));
  } catch (err) {
    res.status(err.status || 500).send(page('<div class="err">' + escapeHtml(err.message) + '</div>'));
  }
});

/** Wraps content in a simple HTML page. */
function page(content) {
  return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">' +
    '<meta name="viewport" content="width=device-width, initial-scale=1">' +
    '<title>Question 6 - Express client for the PHP API</title><style>' +
    'body{font-family:Arial,sans-serif;max-width:860px;margin:30px auto;padding:0 15px;background:#f5f7fa}' +
    '.box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.15)}' +
    'table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:8px 10px;text-align:left}' +
    'th{background:#0d6efd;color:#fff}tr:nth-child(even){background:#f2f6ff}' +
    '.err{background:#f8d7da;border:1px solid #f5c2c7;color:#842029;padding:10px 14px;border-radius:5px}' +
    'code{background:#eee;padding:1px 5px;border-radius:3px}</style></head><body><div class="box">' +
    '<h2>Question 6: Express client &rarr; PHP API</h2>' + content + '</div></body></html>';
}

app.listen(PORT, () => {
  console.log('Express client running on http://localhost:' + PORT);
  console.log('Using PHP API: ' + PHP_API_URL);
});
