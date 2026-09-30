<?php

declare(strict_types=1);

// Run with a consumer's Composer autoloader supplying the Laminas dependencies.
$autoload = $argv[1] ?? '';
if (!is_file($autoload)) {
    throw new RuntimeException('Usage: php tests/image-upload.php <consumer-vendor-autoload.php>');
}
require $autoload;
require_once dirname(__DIR__) . '/src/Base/Image.php';

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

foreach (['webp' => 'imagewebp', 'png' => 'imagepng', 'jpeg' => 'imagejpeg'] as $format => $encode) {
    $image = imagecreatetruecolor(24, 24);
    ob_start();
    $encode($image);
    $body = ob_get_clean();
    foreach ([false, true] as $withExtension) {
        $temporary = tempnam(sys_get_temp_dir(), 'profile-image-');
        $path = $withExtension ? $temporary . '.' . $format : $temporary;
        if ($withExtension) rename($temporary, $path);
        try {
            file_put_contents($path, $body);
            $loaded = new Base\Image();
            $loaded->setLocation($path);
            if ($loaded->getMimeType() !== 'image/' . $format
                || $loaded->getWidth() !== 24 || $loaded->getHeight() !== 24
                || $loaded->getExtension() === '' || $loaded->getBody() !== $body) {
                throw new RuntimeException('Upload metadata/body mismatch: ' . $format);
            }
        } finally {
            unlink($path);
        }
    }
    echo $format . ": extensionless and named upload OK\n";
}
restore_error_handler();
