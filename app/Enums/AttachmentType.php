<?php
declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static self Cover()
 * @method static self Gallery()
 * @method static self Thumbnail()
 * @method static self Video()
 */
final class AttachmentType extends Enum
{
    public const Cover     = 'cover';
    public const Gallery   = 'gallery';
    public const Thumbnail = 'thumbnail';
    public const Video     = 'video';

    /**
     * @return array<string,string>
     */
    public static function getLabels(): array
    {
        return [
            self::Cover     => __('Cover Image'),
            self::Gallery   => __('Gallery Images'),
            self::Thumbnail => __('Thumbnail'),
            self::Video     => __('Video'),
        ];
    }

    public function label(): string
    {
        return static::getLabels()[$this->value];
    }
}