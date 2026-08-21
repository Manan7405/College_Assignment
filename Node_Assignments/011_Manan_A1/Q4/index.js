const path = require('path');
const { zipDirectory } = require('./ziputil'); // Make sure this matches ziputil.js

const sourceDir = path.join(__dirname, 'sample_folder');
const destinationZip = path.join(__dirname, 'sample_folder.zip');

console.log("==================================================");
console.log("            FOLDER ZIP COMPRESSOR TOOL            ");
console.log("==================================================");
console.log(`Source Folder : ${sourceDir}`);
console.log(`Target Zip    : ${destinationZip}`);
console.log("Compressing... Please wait.\n");

zipDirectory(sourceDir, destinationZip)
    .then((message) => {
        console.log("--------------------------------------------------");
        console.log(`SUCCESS: ${message}`);
        console.log("--------------------------------------------------");
    })
    .catch((err) => {
        console.error("--------------------------------------------------");
        console.error(`FAILURE: ${err.message}`);
        console.error("--------------------------------------------------");
    });