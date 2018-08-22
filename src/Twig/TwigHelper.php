<?php

namespace Flying\Wordpress\Util;

use Timber\ImageHelper;

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
    public static function register(\Twig_Environment $twig)
    {
        // Get page url by its slug
        $twig->addFunction(new \Twig_SimpleFunction('page_url', [PostUtils::class, 'getPageUrlBySlug']));

        // Construct CSS classes list from given list of arguments
        $twig->addFunction(new \Twig_SimpleFunction('cl', [CssUtils::class, 'classList']));

        // Retrieve domain from given url
        $twig->addFilter(new \Twig_SimpleFilter('domain', function ($url) {
            $p = parse_url($url);
            return array_key_exists('host', $p) ? $p['host'] : '';
        }));

        // Convert given string to slug-alike string
        $twig->addFilter(new \Twig_SimpleFilter('to_slug', [StringUtils::class, 'toSlug']));

        // Resize image without allowing to scale it up
        // Workaround against Timber issue as of 1.6.0
        // @see https://github.com/timber/timber/issues/1435
        $twig->addFilter(new \Twig_SimpleFilter('downscale', function ($src, $w, $h = 0, $crop = 'default', $force = false) {
            $editor = \wp_get_image_editor($src);
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
