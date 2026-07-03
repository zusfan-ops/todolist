<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use RuntimeException;

class PhotoService
{
    public function store(Task $task, UploadedFile $file, array $attributes): TaskPhoto
    {
        $disk = config('filesystems.photos_disk', 'public');
        $hash = hash_file('sha256', $file->getRealPath());

        if (! empty($attributes['sha256']) && ! hash_equals($attributes['sha256'], $hash)) {
            throw new RuntimeException('Verifikasi integritas foto gagal');
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = "photos/{$filename}";
        $thumbFilename = Str::uuid().'-thumb.jpg';
        $thumbPath = "photos/{$thumbFilename}";

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $manager = new ImageManager(new Driver);
        $image = $manager->decodePath($file->getRealPath());
        $image->scaleDown(width: 320);
        Storage::disk($disk)->put($thumbPath, (string) $image->encode(new JpegEncoder(quality: 80)));

        return TaskPhoto::firstOrCreate(
            ['client_uuid' => $attributes['client_uuid']],
            [
                'task_id' => $task->id,
                'type' => $attributes['type'] ?? 'progress',
                'disk' => $disk,
                'path' => $path,
                'thumb_path' => $thumbPath,
                'caption' => $attributes['caption'] ?? null,
                'sha256' => $hash,
                'size_bytes' => $file->getSize(),
                'latitude' => $attributes['latitude'] ?? null,
                'longitude' => $attributes['longitude'] ?? null,
                'taken_at' => $attributes['taken_at'] ?? now(),
            ]
        );
    }
}
