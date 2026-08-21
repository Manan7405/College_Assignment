const express = require('express');

const app = express();
const PORT = 3007;

app.get('/', (req, res) => {
    res.send('Open /google route to fetch Google page.');
});

app.get('/google', async (req, res) => {
    try {
        const response = await fetch('https://www.google.com');
        const data = await response.text();
        res.send(data);
    } catch (error) {
        res.status(500).send('Error fetching Google page: ' + error.message);
    }
});

app.listen(PORT, () => {
    console.log(`Server running at http://localhost:${PORT}`);
});
