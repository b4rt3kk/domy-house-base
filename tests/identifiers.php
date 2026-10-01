<?php

declare(strict_types=1);

$autoload = $argv[1] ?? '';
if (!is_file($autoload)) throw new RuntimeException('Pass the consumer Composer autoloader.');
require $autoload;
require_once dirname(__DIR__) . '/src/Base/Validator/Nip.php';
require_once dirname(__DIR__) . '/src/Base/Validator/Pesel.php';
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
foreach ([new Base\Validator\Nip(), new Base\Validator\Pesel()] as $validator) {
    foreach ([null, [], new stdClass(), 123, '', '1', 'abc', '123456789012345'] as $value) {
        if ($validator->isValid($value) || !$validator->getMessages()) throw new RuntimeException('Malformed identifier was not rejected with a validation message.');
    }
}
if (!(new Base\Validator\Nip())->isValid('5260250274')) throw new RuntimeException('Valid NIP rejected.');
foreach (['44051401458', '00000000000'] as $pesel) {
    if (!(new Base\Validator\Pesel())->isValid($pesel)) throw new RuntimeException('Valid checksum rejected.');
}
echo "Identifier validation: OK\n";
