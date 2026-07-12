<?php

namespace App\Modules\Core\Application\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SafeUploadService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];

    private const ALLOWED_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'pdf',
    ];

    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $extension = Str::lower($file->getClientOriginalExtension());
        $mimeType = (string) $file->getMimeType();

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true) || ! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('Unsupported upload type.');
        }

        $safeDirectory = trim(Str::of($directory)->replace('\\', '/')->replace('..', '')->value(), '/');
        $filename = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs($safeDirectory, $filename, $disk);
    }

    public function allowedMimeTypes(): array
    {
        return self::ALLOWED_MIME_TYPES;
    }
}
