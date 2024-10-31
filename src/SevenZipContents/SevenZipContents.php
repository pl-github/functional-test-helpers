<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\SevenZipContents;

use function is_file;

final class SevenZipContents
{
    public function readFile(string $file): SevenZipInfo
    {
        if (!is_file($file)) {
            throw InvalidArchive::notAFile($file);
        }

        $archive = new SevenZipArchive($file);
        $info = $archive->getInfo();

        $fileInfos = [];
        foreach ($archive->getEntries() as $entry) {
            $path = $entry->getPath();

            $size = (int) $entry->getSize();
            $packedSize = (int) $entry->getPackedSize();
            $compression = $size && $packedSize
                ? (int) ($packedSize * 100 / $size)
                : 0;

            $fileInfos[$path] = new SevenZipFileInfo(
                $path,
                $size,
                $packedSize,
                $compression,
                $entry->getModified(),
                $entry->getCrc(),
                $entry->isDirectory(),
            );
        }

        return new SevenZipInfo($info->getPhysicalSize(), $fileInfos);
    }
}
