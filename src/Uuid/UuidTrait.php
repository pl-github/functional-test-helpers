<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Uuid;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\NilUuid;
use Symfony\Component\Uid\Uuid;

use function dechex;
use function Safe\json_decode;
use function Safe\json_encode;
use function str_pad;
use function substr_replace;

use const STR_PAD_LEFT;

/** @mixin TestCase */
trait UuidTrait
{
    private int $lastUuidValue;

    #[Before]
    final protected function setUpUuidTrait(): void
    {
        $this->lastUuidValue = 0;
    }

    final protected static function uuidFromInteger(int $number): string
    {
        $uuid = str_pad(dechex($number), 32, '0', STR_PAD_LEFT);
        $uuid = substr_replace($uuid, '-', 8, 0);
        $uuid = substr_replace($uuid, '-', 13, 0);
        $uuid = substr_replace($uuid, '-', 18, 0);
        $uuid = substr_replace($uuid, '-', 23, 0);

        return (string) Uuid::fromString($uuid);
    }

    final protected function nextUuid(): string
    {
        $this->lastUuidValue ??= 0;

        return self::uuidFromInteger(++$this->lastUuidValue);
    }

    final protected static function assertIsUuid(mixed $actual, string $message = ''): void
    {
        self::assertIsString($actual, $message);
        self::assertTrue(Uuid::isValid($actual), $message);
    }

    final protected static function assertAndReplaceUuidInJson(mixed $jsonData, string $key): string
    {
        self::assertJson($jsonData);

        $jsonData = self::assertAndReplaceUuidInArray(json_decode($jsonData, true), $key);

        return json_encode($jsonData);
    }

    /** @return mixed[] */
    final protected static function assertAndReplaceUuidInArray(mixed $arrayData, string $key): array
    {
        self::assertIsArray($arrayData);

        if (($arrayData[$key] ?? null) !== null) {
            self::assertIsUuid($arrayData[$key] ?? null);

            $arrayData[$key] = (string) (new NilUuid());
        }

        return $arrayData;
    }
}
