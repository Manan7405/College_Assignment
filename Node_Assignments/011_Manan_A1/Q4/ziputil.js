const fs = require('fs');
const { ZipArchive } = require('archiver');

/**
 * Compresses an entire folder into a .zip archive.
 * @param {string} sourceDirPath - Path of folder to compress
 * @param {string} outZipPath - Target destination zip file path
 * @returns {Promise<string>}
 */
function zipDirectory(sourceDirPath, outZipPath) {
    return new Promise((resolve, reject) => {
        // 1. Create a writable stream for the destination zip
        const output = fs.createWriteStream(outZipPath);

        // 2. Initialize zip archiver
        const archive = new ZipArchive({
            zlib: { level: 9 }
        });

        // 3. Listen to close event
        output.on('close', () => {
            const totalBytes = archive.pointer();
            resolve(`Zip file created successfully! Total size: ${totalBytes} bytes.`);
        });

        // 4. Handle warnings
        archive.on('warning', (err) => {
            if (err.code === 'ENOENT') {
                console.warn('Archiver Warning:', err);
            } else {
                reject(err);
            }
        });

        // 5. Handle errors
        archive.on('error', (err) => {
            reject(err);
        });

        // 6. Pipe archive data stream to the file
        archive.pipe(output);

        // 7. Append folder contents
        archive.directory(sourceDirPath, false);

        // 8. Finalize the archive
        archive.finalize();
    });
}

module.exports = { zipDirectory };
