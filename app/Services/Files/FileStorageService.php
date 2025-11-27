<?php

namespace App\Services\Files;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class FileStorageService
{

    /**
     * Store an uploaded file and return the relative path on the disk.
     *
     * @param UploadedFile $file
     * @param string $dir directory inside the disk (e.g. "proposals", "avatars")
     * @param string|null $disk (e.g. "public", "local", "s3")
     * @param string|null $name optional filename (without path). If null, Laravel generates it.
     * @return string
     */
    public function store(
        UploadedFile $file,
        string $dir,
        ?string $disk = null,
        ?string $name = null
    ): string {
        $path = $name
            ? $file->storeAs(!$disk || $disk === 'local' ? storage_path($dir) : $dir, $name, ['disk' => $disk])
            : $file->store(!$disk || $disk === 'local' ? storage_path($dir) : $dir, ['disk' => $disk]);

        if (!$path) {
            throw new RuntimeException('Failed to store file.');
        }

        return $path;
    }


}
