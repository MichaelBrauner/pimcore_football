<?php

namespace App\Service\Document;

use App\Contract\FolderManagerInterface;
use Pimcore\Model\DataObject\Folder;

class FolderManager implements FolderManagerInterface
{
    public function getOrCreateFolder(string $path, string $key, ?int $parentId = 1): Folder
    {
        $folder = Folder::getByPath($path);
        if ($folder) {
            return $folder;
        }

        $folder = new Folder();
        $folder->setKey($key);
        $folder->setParentId($parentId);
        $folder->save();

        return $folder;
    }

    public function deleteFolderIfExists(string $path): void
    {
        $folder = Folder::getByPath($path);
        if (!$folder) {
            return;
        }

        foreach ($folder->getChildren() as $child) {
            if($child === false) {
                continue;
            }
            $child->delete();
        }

        $folder->delete();
    }
}
