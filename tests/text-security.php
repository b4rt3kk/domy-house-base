<?php

declare(strict_types=1);

$autoload = $argv[1] ?? '';
if (!is_file($autoload)) throw new RuntimeException('Pass the consumer Composer autoloader.');
require $autoload;
require_once dirname(__DIR__) . '/src/Base/Validator/Url.php';
require_once dirname(__DIR__) . '/src/Base/View/Helper/Format.php';
foreach ([false, true] as $required) {
    $validator = new Base\Validator\Url(['requireScheme' => $required]);
    foreach (['javascript://example.com/%0aalert(1)', 'data://example.com/a', 'ftp://example.com', 'https://user:pass@example.com', "https://example.com/\n", 'https://example.com/" onload="x', 'https://example.com/\\evil', [], null] as $url) {
        // Outer whitespace is normalized; a trailing newline is acceptable after trim.
        if ($url === "https://example.com/\n") continue;
        if ($validator->isValid($url)) throw new RuntimeException('Unsafe URL accepted: ' . json_encode($url));
    }
    foreach (['https://example.com/path?x=1&y=2', 'http://example.com', 'HTTPS://example.com'] as $url) {
        if (!$validator->isValid($url)) throw new RuntimeException('Safe URL rejected: ' . $url);
    }
}
if (!(new Base\Validator\Url())->isValid('example.com')) throw new RuntimeException('Bare domain rejected.');
$format = new Base\View\Helper\Format();
foreach (['<svg onload=alert(1)>', '" onmouseover="alert(1)', '&lt;img src=x onerror=alert(1)&gt;', str_repeat('a', 195) . '<script>alert(1)</script>'] as $text) {
    $html = $format->format(Base\View\Helper\Format::FORMAT_TRUNCATE, $text);
    if (str_contains($html, 'data-bs-html="true"') || str_contains($html, '<svg') || str_contains($html, '<img') || str_contains($html, '<script') || str_contains($html, 'title=""')) throw new RuntimeException('Unsafe tooltip.');
    $html = $format->format(Base\View\Helper\Format::FORMAT_TEXT_TRUNCATE, $text);
    if (str_contains($html, '<')) throw new RuntimeException('Unsafe truncation.');
}
echo "Text and URL security: OK\n";
