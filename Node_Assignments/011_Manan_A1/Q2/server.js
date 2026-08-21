const express = require('express');
const path = require('path');

const app = express();
const PORT = 3000;

// Serve all static resources inside the 'public' directory
// Express automatically resolves paths and sets appropriate MIME types
// (e.g., text/html, text/css, application/javascript, image/png)
app.use(express.static(path.join(__dirname, 'public')));

// Fallback route: serve index.html when hitting root '/'
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Start the server
app.listen(PORT, () => {
    console.log(`Static file server running at http://localhost:${PORT}`);
});