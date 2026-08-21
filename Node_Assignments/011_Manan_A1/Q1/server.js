const express = require('express');
const path = require('path');

// Initialize the Express application
const app = express();
const port = 3000;

app.use(express.json());
// Automatically serve files from the public folder
app.use(express.static(path.join(__dirname,'public')));

app.get('/gethello',(req,res) => {
    res.send("Hello MSD!")
});

app.get('/',(req,res)=> {
    res.sendFile(path.join(__dirname,'public','index.html'));
});

app.listen(port, () => {
    console.log(`Server is Running on Port : ${port}`)
});



