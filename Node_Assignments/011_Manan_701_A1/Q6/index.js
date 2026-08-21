const fs = require('fs');
const path = require('path');
const util = require('util');

const unlinkPromise = util.promisify(fs.unlink);
const filePath = path.join(__dirname, 'delete-me.txt');

fs.writeFileSync(filePath, 'This file will be deleted.');

unlinkPromise(filePath)
    .then(() => {
        console.log('File deleted successfully using promisified fs.unlink.');
    })
    .catch((error) => {
        console.log('Error:', error.message);
    });
