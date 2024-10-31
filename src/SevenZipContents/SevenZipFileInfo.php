<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\SevenZipContents;

use DateTimeImmutable;

use function array_pop;
use function array_push;
use function explode;
use function implode;
use function str_replace;
use function substr;
use function trim;

final readonly class SevenZipFileInfo
{
    private string $path;
    private DateTimeImmutable|null $lastModified;

    public function __construct(
        string $path,
        private int $size,
        private int $compressedSize,
        private int $compression,
        string|null $lastModified,
        private string|null $crc,
        private bool $isDir,
    ) {
        $this->path = $this->cleanPath($path);

        $this->lastModified = $lastModified
            ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', substr($lastModified, 0, 19))
            : null;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        if ($this->isDir) {
            return 0;
        }

        return $this->size;
    }

    public function getCompressedSize(): int
    {
        return $this->compressedSize;
    }

    public function getCompression(): int
    {
        return $this->compression;
    }

    public function getCrc(): string|null
    {
        return $this->crc;
    }

    public function isDir(): bool
    {
        return $this->isDir;
    }

    public function getLastModified(): DateTimeImmutable|null
    {
        return $this->lastModified;
    }

    /**
     * Cleans up a path and removes relative parts, also strips leading slashes
     */
    private function cleanPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = explode('/', $path);
        $newpath = [];
        foreach ($path as $p) {
            if ($p === '' || $p === '.') {
                continue;
            }

            if ($p === '..') {
                array_pop($newpath);
                continue;
            }

            array_push($newpath, $p);
        }

        return trim(implode('/', $newpath), '/');
    }
}
