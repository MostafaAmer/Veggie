<?php
declare(strict_types=1);

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageServiceInterface
{
    /**
     * @param  string       $folder  مسار المجلد (مثلاً 'products')
     * @param  UploadedFile $file
     * @return array{path:string, thumbnail:string}
     */
    public function store(string $folder, UploadedFile $file): array;
}
