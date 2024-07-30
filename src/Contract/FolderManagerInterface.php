<?php

namespace App\Contract;

use Pimcore\Model\Element\AbstractElement;

interface FolderManagerInterface
{
    public function getOrCreateFolder(string $path, string $key, int $parentId = 1): AbstractElement;

    public function deleteFolderIfExists(string $path): void;
}
