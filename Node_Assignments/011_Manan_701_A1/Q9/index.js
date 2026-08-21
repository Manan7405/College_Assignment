const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, 'demo.txt');
const copyPath = path.join(__dirname, 'demo-copy.txt');

fs.writeFileSync(filePath, 'Hello Node.js fs module\n');
console.log('File created.');

fs.appendFileSync(filePath, 'This line is appended.\n');
console.log('Data appended.');

const data = fs.readFileSync(filePath, 'utf8');
console.log('File content:\n' + data);

fs.copyFileSync(filePath, copyPath);
console.log('File copied.');

const files = fs.readdirSync(__dirname);
console.log('Files in current folder:', files);

const info = fs.statSync(filePath);
console.log('File size:', info.size, 'bytes');

fs.renameSync(copyPath, path.join(__dirname, 'renamed-demo.txt'));
console.log('Copied file renamed.');
