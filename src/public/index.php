<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

require __DIR__ . '/../vendor/autoload.php';

// Create a root path for Flysystem test folders
$rootPath = __DIR__ . '/../../data';
if (!mkdir($rootPath, 0755) && !is_dir($rootPath)) {
    throw new RuntimeException(sprintf('Directory "%s" was not created', $rootPath));
}

$adapter = new LocalFilesystemAdapter(
    location: $rootPath,
    visibility: PortableVisibilityConverter::fromArray(permissionMap: [
        'file' => [
            'public' => 0666,   // All 'rw'
            'private' => 0600,  // Owner only 'rw', Group '-', World '-'
        ],
        'dir' => [
            'public' => 0777,   // All 'rw'
            'private' => 0705,  // Owner 'rw', Group '-', World 'x'
        ],
    ]),
    writeFlags: LOCK_EX,
    linkHandling: LocalFilesystemAdapter::DISALLOW_LINKS,
);

$filesystem = new Filesystem(adapter: $adapter);

$publicPath = 'pub/bar.txt';
$privatePath = 'priv/foo.txt';
$publicPath2 = 'pub2/foo.txt';
$contents = 'The file contents';

// Create folders with visibility
$filesystem->createDirectory(
    location:'pub',
    config: [
        'visibility' => 'public',
    ]
);
$filesystem->createDirectory(
    location:'priv',
    config: [
        'visibility' => 'private',
    ]
);

$filesystem->write(
    location: $publicPath,
    contents: $contents
);
$filesystem->setVisibility(
    path: $publicPath,
    visibility: 'public'
);

$filesystem->write(
    location: $privatePath,
    contents: $contents,
);
$filesystem->setVisibility(
    path: $privatePath,
    visibility: 'private',
);

$filesystem->write(
    location: $publicPath2,
    contents: $contents,
);
// Set folder to public
$filesystem->setVisibility(
    path: 'pub2',
    visibility: 'public',
);
// Set file to private
$filesystem->setVisibility(
    path: $publicPath2,
    visibility: 'private',
);

echo 'Done.';
