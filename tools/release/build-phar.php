#!/usr/bin/env php
<?php

declare(strict_types=1);

use ScipLaravel\Cli\ApplicationFactory;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);
$version = $argv[1] ?? ApplicationFactory::VERSION;
$distDirectory = $projectRoot . '/dist';
$pharPath = $distDirectory . '/scip-laravel-' . $version . '.phar';

if (ini_get('phar.readonly') !== '0') {
    throw new RuntimeException('Cannot build PHAR while phar.readonly is enabled.');
}

if (!is_dir($distDirectory) && !mkdir($distDirectory, 0777, true) && !is_dir($distDirectory)) {
    throw new RuntimeException("Cannot create dist directory: $distDirectory.");
}

if (is_file($pharPath) && !unlink($pharPath)) {
    throw new RuntimeException("Cannot replace existing PHAR: $pharPath.");
}

$phar = new Phar($pharPath, 0, 'scip-laravel.phar');
$phar->startBuffering();
$phar->setSignatureAlgorithm(Phar::SHA512);

foreach (packageFiles($projectRoot) as $localPath => $absolutePath) {
    $phar->addFile($absolutePath, $localPath);
}

$phar->setStub(<<<'PHP'
#!/usr/bin/env php
<?php

declare(strict_types=1);

Phar::mapPhar('scip-laravel.phar');
require 'phar://scip-laravel.phar/vendor/autoload.php';

exit(ScipLaravel\Cli\ApplicationFactory::create()->run());

__HALT_COMPILER();
PHP);
$phar->stopBuffering();

if (!chmod($pharPath, 0755)) {
    throw new RuntimeException("Cannot mark PHAR as executable: $pharPath.");
}

fwrite(STDOUT, $pharPath . PHP_EOL);

/**
 * @return array<string, string>
 */
function packageFiles(string $projectRoot): array
{
    $files = [];

    foreach (['bin', 'src', 'vendor'] as $directory) {
        $absoluteDirectory = $projectRoot . '/' . $directory;
        if (!is_dir($absoluteDirectory)) {
            throw new RuntimeException("Missing directory for PHAR packaging: $absoluteDirectory.");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absoluteDirectory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
                continue;
            }

            $absolutePath = $fileInfo->getPathname();
            $localPath = substr($absolutePath, strlen($projectRoot) + 1);
            if ($localPath === false) {
                throw new RuntimeException("Cannot normalize PHAR path for file: $absolutePath.");
            }

            $files[$localPath] = $absolutePath;
        }
    }

    foreach (['composer.json', 'composer.lock', 'README.md', 'CHANGELOG.md'] as $file) {
        $absolutePath = $projectRoot . '/' . $file;
        if (!is_file($absolutePath)) {
            throw new RuntimeException("Missing file for PHAR packaging: $absolutePath.");
        }

        $files[$file] = $absolutePath;
    }

    ksort($files);

    return $files;
}
