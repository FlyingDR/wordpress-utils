<?php

namespace Flying\Wordpress\Twig;

use Flying\Wordpress\Util\CssUtils;
use Flying\Wordpress\Util\ImageUtils;
use Flying\Wordpress\Util\PostUtils;
use Flying\Wordpress\Util\StringUtils;
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
     * @param Environment $twig
     * @return Environment
     */
    public static function register(Environment $twig): Environment
    {
        // Get page url by its slug
        $twig->addFunction(new TwigFunction('page_url', [PostUtils::class, 'getPageUrlBySlug']));

        // Construct CSS classes list from the given list of arguments
        $twig->addFunction(new TwigFunction('cl', [CssUtils::class, 'classList']));

        // Retrieve domain from given url
        $twig->addFilter(new TwigFilter('domain', fn(string $url): ?string => parse_url($url, PHP_URL_HOST)));

        // Convert given string to slug-alike string
        $twig->addFilter(new TwigFilter('to_slug', [StringUtils::class, 'toSlug']));

        // Resize image without allowing to scale it up
        $twig->addFilter(new TwigFilter('downscale', [ImageUtils::class, 'downscale']));

        return $twig;
    }
}
