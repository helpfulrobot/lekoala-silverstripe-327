<?php
// @link https://github.com/silverstripe/silverstripe-framework/pull/4551

$v = phpversion();
if (strpos($v, '7') !== 0) {
    throw new Exception("You need to run PHP7");
}

if (php_sapi_name() == 'cli') {

} else if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
    // Keep in mind that REMOTE_ADDR can be spoofed so it's just extra security
} else {
    throw new Exception("This script can only be run locally");
}

header("Content-Type: text/plain");
$base_dir = dirname(__DIR__);

$rename = [
    '/framework/model/fieldtypes/Int.php' => [
        "class Int extends DBField" => "class DBInt extends DBField"
    ],
    '/framework/model/fieldtypes/Float.php' => [
        "class Float extends DBField" => "class DBFloat extends DBField",
    ],
    '/framework/model/fieldtypes/Double.php' => [
        "class Double extends Float" => "class Double extends DBFloat",
    ],
    '/framework/model/fieldtypes/ForeignKey.php' => [
        'class ForeignKey extends Int' => "class ForeignKey extends DBInt",
    ],
    '/framework/model/fieldtypes/PrimaryKey.php' => [
        'class PrimaryKey extends Int' => "class PrimaryKey extends DBInt",
    ],
    '/framework/model/connect/DBSchemaManager.php' => [
        '$spec = $this->$spec[\'type\']($spec[\'parts\'], true);' => '$spec = $this->{$spec[\'type\']}($spec[\'parts\'], true);',
        '$spec_orig = $this->$spec_orig[\'type\']($spec_orig[\'parts\']);' => '$spec_orig = $this->{$spec_orig[\'type\']}($spec_orig[\'parts\']);',
    ]
];

echo "Updating your current setup to support PHP7\n";
echo str_repeat('*', 60)."\n";

foreach ($rename as $file => $opts) {
    $filename = $base_dir.$file;

    if (!is_file($filename)) {
        throw new Exception("$filename does not exists");
    }

    if(!is_writable($filename)) {
        throw new Exception("$filename is not writable");
    }

    $updatedContent = $content        = file_get_contents($filename);

    if (!$updatedContent) {
        throw new Exception("$filename is empty");
    }

    $count = null;
    foreach ($opts as $before => $after) {
        $updatedContent = str_replace($before, $after, $updatedContent, $count);
    }

    if ($count > 1) {
        throw new Exception("Something wrong happened, replacement should only happen once");
    }

    $r = file_put_contents($filename, $updatedContent);

    if ($r) {
        echo "Updated $file\n";
    } else {
        echo "Failed to update $file\n";
    }
}

echo str_repeat('*', 60)."\n";
echo "All done! Run a /dev/build to put everything in order!";
