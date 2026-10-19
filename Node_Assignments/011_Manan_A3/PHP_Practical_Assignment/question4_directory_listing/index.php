<?php
/**
 * QUESTION 4 - List the files of a directory in an HTML table.
 *
 * SECURITY: the directory is fixed in the code (the "files" folder next
 * to this script). The browser cannot choose a different path, so no
 * other server folder can be listed. Hidden files (".htaccess" ...) and
 * sub-folders are not shown.
 */
$directory = __DIR__ . '/files';          // <-- the ONLY directory that is listed

function h($text): string { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }

/** Converts bytes to B / KB / MB. */
function format_size(int $bytes): string
{
    if ($bytes < 1024)         { return $bytes . ' B'; }
    if ($bytes < 1048576)      { return number_format($bytes / 1024, 2) . ' KB'; }
    return number_format($bytes / 1048576, 2) . ' MB';
}

$files = [];
$problem = '';

if (!is_dir($directory)) {
    $problem = 'The directory "files" does not exist. Create it inside the question4_directory_listing folder.';
} elseif (!is_readable($directory)) {
    $problem = 'The directory "files" cannot be read (permission problem).';
} else {
    $names = scandir($directory);                 // returns every entry name, including . and ..
    foreach ($names as $name) {
        if ($name[0] === '.') {                   // skip ".", ".." and hidden files
            continue;
        }
        $path = $directory . '/' . $name;
        if (!is_file($path)) {                    // skip sub-folders
            continue;
        }
        $files[] = [
            'name'      => $name,
            'extension' => pathinfo($name, PATHINFO_EXTENSION),
            'size'      => (int)filesize($path),
            'modified'  => filemtime($path),
        ];
    }
    // sort by file name, ignoring upper/lower case
    usort($files, function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Question 4 - Directory Listing</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 820px; margin: 30px auto; padding: 0 15px; background: #f5f7fa; }
        .box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #0d6efd; color: #fff; }
        tr:nth-child(even) { background: #f2f6ff; }
        .err { background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 10px 14px; border-radius: 5px; }
        .info { background: #cff4fc; border: 1px solid #b6effb; color: #055160; padding: 10px 14px; border-radius: 5px; }
        code { background: #eee; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Question 4: Files in the <code>files/</code> directory</h2>

    <?php if ($problem !== ''): ?>
        <div class="err"><?= h($problem) ?></div>
    <?php elseif (!$files): ?>
        <div class="info">The directory is empty. Put some files into <code>question4_directory_listing/files/</code> and reload this page.</div>
    <?php else: ?>
        <p>Total files: <strong><?= count($files) ?></strong></p>
        <table>
            <tr><th>#</th><th>File name</th><th>Extension</th><th>Size</th><th>Last modified</th></tr>
            <?php foreach ($files as $i => $f): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= h($f['name']) ?></td>
                    <td><?= $f['extension'] !== '' ? h($f['extension']) : '-' ?></td>
                    <td><?= h(format_size($f['size'])) ?></td>
                    <td><?= h(date('d M Y, H:i:s', $f['modified'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
