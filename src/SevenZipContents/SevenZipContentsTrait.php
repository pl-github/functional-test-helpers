<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\SevenZipContents;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @mixin TestCase */
trait SevenZipContentsTrait
{
    final protected static function read7zFile(string $path): SevenZipInfo
    {
        $zipContents = new SevenZipContents();

        return $zipContents->readFile($path);
    }

    final protected static function assert7zHasSize(int $expectedSize, SevenZipInfo $zip, string $message = ''): void
    {
        Assert::assertSame($expectedSize, $zip->getSize(), $message);
    }

    final protected static function assert7zHasNumberOfFiles(
        int $expectedNumberOfFiles,
        SevenZipInfo $zip,
        string $message = '',
    ): void {
        Assert::assertCount($expectedNumberOfFiles, $zip, $message);
    }

    final protected static function assert7zHasFile(string $expectedPath, SevenZipInfo $zip, string $message = ''): void
    {
        Assert::assertTrue($zip->hasFile($expectedPath), $message);
    }

    final protected static function assert7zHasFileWithSize(
        string $expectedPath,
        int $expectedSize,
        SevenZipInfo $zip,
        string $message = '',
    ): void {
        self::assert7zHasFile($expectedPath, $zip, $message);

        $file = $zip->getFile($expectedPath);

        Assert::assertSame($expectedSize, $file?->getSize(), $message);
    }
}
