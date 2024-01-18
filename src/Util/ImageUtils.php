<?php

declare(strict_types=1);

namespace Flying\Wordpress\Util;

use Timber\ImageHelper;

class ImageUtils
{
    /**
     * Resize image without allowing to scale it up
     * Workaround against Timber issue as of 1.6.0
     *
     * @see https://github.com/timber/timber/issues/1435
     */
    public static function downscale($src, int $w, int $h = 0, string $crop = 'default', bool $force = false): string
    {
        $src = (string)$src;
        $isrc = $src;
        $uDir = wp_get_upload_dir();
        if (str_starts_with($src, $uDir['baseurl'])) {
            // This is url to image which resides
            $tsrc = str_replace($uDir['baseurl'], $uDir['basedir'], $src);
            if (is_file($tsrc)) {
                $isrc = $tsrc;
            }
        }

        $editor = \wp_get_image_editor($isrc);
        if (!$editor instanceof \WP_Image_Editor) {
            return $src;
        }
        $size = $editor->get_size();
        if ($w > $size['width']) {
            return $src;
        }
        if ($h !== 0 && $h > $size['height']) {
            return $src;
        }
        return ImageHelper::resize($src, $w, $h, $crop, $force);
    }
}
