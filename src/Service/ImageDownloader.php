<?php

namespace App\Service;

use Pimcore\Model\Asset;
use Webmozart\Assert\Assert;

class ImageDownloader
{
    public function downloadAndSaveImageAsAsset(string $url, Asset\Folder $parent, string $filename): Asset\Image
    {
        $context = stream_context_create([
            'http' => [
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
            ]
        ]);

        $imageContents = file_get_contents($url, false, $context);
        if ($imageContents === false) {
            throw new \Exception("Failed to download image from $url");
        }

        $parsedUrl = parse_url($url, PHP_URL_PATH);
        Assert::string($parsedUrl, 'The URL path must be a string.');
        $extension = pathinfo($parsedUrl, PATHINFO_EXTENSION);
        $filePath = sys_get_temp_dir() . '/' . $filename . '.' . $extension;
        file_put_contents($filePath, $imageContents);

        $asset = new Asset\Image();
        $asset->setFilename($filename . '.' . $extension);
        $asset->setData(file_get_contents($filePath));
        $asset->setParent($parent);
        $asset->save();

        return $asset;
    }
}
