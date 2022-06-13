<?php

namespace Flying\Wordpress\Twig;

use Flying\Wordpress\Util\CssUtils;
use Flying\Wordpress\Util\PostUtils;
use Flying\Wordpress\Util\StringUtils;
use Timber\ImageHelper;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Collection of Twig filters and functions
 */
class TwigHelper
{
    /**
     * Register Twig functions and filters
     * mean to be registered as "get_twig" filter
     *
     * @param \Twig_Environment $twig
     * @return \Twig_Environment
     */
    public static function register(Environment $twig)
    {
        // Get page url by its slug
        $twig->addFunction(new TwigFunction('page_url', [PostUtils::class, 'getPageUrlBySlug']));

        // Construct CSS classes list from given list of arguments
        $twig->addFunction(new TwigFunction('cl', [CssUtils::class, 'classList']));

        // Retrieve domain from given url
        $twig->addFilter(new TwigFilter('domain', function ($url) {
            $p = parse_url($url);
            return array_key_exists('host', $p) ? $p['host'] : '';
        }));

        // Convert given string to slug-alike string
        $twig->addFilter(new TwigFilter('to_slug', [StringUtils::class, 'toSlug']));

        // Resize image without allowing to scale it up
        // Workaround against Timber issue as of 1.6.0
        // @see https://github.com/timber/timber/issues/1435
        $twig->addFilter(new TwigFilter('downscale', function ($src, $w, $h = 0, $crop = 'default', $force = false) {
            $isrc = $src;
            $uDir = wp_get_upload_dir();
            if (strpos($src, $uDir['baseurl']) === 0) {
                // This is url to image which resides
                $tsrc = str_replace($uDir['baseurl'], $uDir['basedir'], $src);
                if (is_file($tsrc)) {
                    $isrc = $tsrc;
                }
            }

            $editor = \wp_get_image_editor($isrc);
            if (!($editor instanceof \WP_Image_Editor)) {
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
        }));

        return $twig;
    }
}
