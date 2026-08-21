console.log('File name:', __filename);
console.log('Directory name:', __dirname);

console.log('Node version:', process.version);
console.log('Platform:', process.platform);

console.log('Current working directory:', process.cwd());

const args = process.argv.slice(2);
console.log('Command line arguments:', args);

setTimeout(() => {
    console.log('This message is printed using setTimeout global function.');
}, 1000);
