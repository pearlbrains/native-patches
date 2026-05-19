<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;
use Native\Mobile\PendingMediaPicker;
use Native\Mobile\PendingPhotoCapture;
use Native\Mobile\PendingVideoRecorder;

/**
 * @method static PendingPhotoCapture getPhoto(array $options = [])
 * @method static PendingMediaPicker pickImages(string $media_type = 'all', bool $multiple = false, int $max_items = 10)
 * @method static PendingVideoRecorder recordVideo(array $options = [])
 *
 * @see \Native\Mobile\Camera
 */
class Camera extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Native\Mobile\Camera::class;
    }
}
