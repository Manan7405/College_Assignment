/**
 * QUESTION 5 (part A) - Express.js REST API for student records.
 *
 *   GET /api/students       -> all students (JSON array wrapper)
 *   GET /api/students/:id   -> one student, or 404 if it does not exist
 *
 * Data is kept in memory (a JavaScript array) so no database is needed.
 * Start it with:  npm start     (runs on http://localhost:3000)
 */
const express = require('express');

const app  = express();
const PORT = process.env.PORT || 3000;

// ---- sample data (same students as the MySQL examples) ----
const students = [
  { student_id: 101, name: 'Asha Patel',    email: 'asha.patel@example.com',   course: 'BCA',                   marks: 85.5 },
  { student_id: 102, name: "Ravi D'Souza",  email: 'ravi.dsouza@example.com',  course: 'MCA',                   marks: 72 },
  { student_id: 103, name: 'Meera Desai',   email: 'meera.desai@example.com',  course: 'BCA',                   marks: 91.25 },
  { student_id: 104, name: 'Karan Mehta',   email: 'karan.mehta@example.com',  course: 'B.Sc. Physics & Maths', marks: 64.75 },
  { student_id: 105, name: 'Priya Joshi',   email: 'priya.joshi@example.com',  course: 'MCA',                   marks: 78 },
  { student_id: 106, name: 'Neha Shah',     email: 'neha.shah@example.com',    course: 'B.Com',                 marks: 88.4 }
];

// ---- GET /api/students : all students ----
app.get('/api/students', (req, res) => {
  res.status(200).json({ success: true, count: students.length, data: students });
});

// ---- GET /api/students/:id : one student ----
app.get('/api/students/:id', (req, res) => {
  // the id arrives as text, so make sure it is a whole number
  if (!/^\d+$/.test(req.params.id)) {
    return res.status(400).json({ success: false, error: 'Student id must be a positive whole number.' });
  }

  const id = Number(req.params.id);
  const student = students.find((s) => s.student_id === id);

  if (!student) {
    return res.status(404).json({ success: false, error: 'Student with id ' + id + ' was not found.' });
  }
  res.status(200).json({ success: true, data: student });
});

// ---- any other URL -> JSON 404 ----
app.use((req, res) => {
  res.status(404).json({ success: false, error: 'Route not found: ' + req.method + ' ' + req.originalUrl });
});

// ---- unexpected errors -> JSON 500 ----
app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).json({ success: false, error: 'Internal server error.' });
});

app.listen(PORT, () => {
  console.log('Students API is running on http://localhost:' + PORT);
  console.log('Try: http://localhost:' + PORT + '/api/students');
});
