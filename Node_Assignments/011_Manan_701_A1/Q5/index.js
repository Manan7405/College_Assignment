const fs = require('fs');
const path = require('path');
const { execFile } = require('child_process');

const zipFile = path.join(__dirname, '..', 'Q4', 'sample_folder.zip');
const outputFolder = path.join(__dirname, 'extracted_files');

function extractZip(source, destination) {
    return new Promise((resolve, reject) => {
        execFile('powershell', [
            '-NoProfile',
            '-Command',
            `Expand-Archive -LiteralPath "${source}" -DestinationPath "${destination}" -Force`
        ], (error) => {
            if (error) {
                reject(error);
            } else {
                resolve();
            }
        });
    });
}

async function main() {
    if (!fs.existsSync(zipFile) || fs.statSync(zipFile).size === 0) {
        console.log('Zip file not found or empty. First run: node Q4/index.js');
        return;
    }

    await extractZip(zipFile, outputFolder);
    console.log('Zip file extracted successfully.');
    console.log('Output folder:', outputFolder);
}

main().catch((error) => {
    console.log('Error:', error.message);
});
