<?php /** @noinspection DevelopmentDependenciesUsageInspection */

namespace Flying\Wordpress\Util;

use Cocur\Slugify\Slugify;

/**
 * Various utility functions that are related to string processing
 */
class StringUtils
{
    /** @type callable(string): string */
    private static $slugMethod;
    private static Slugify $slugify;

    /**
     * Convert given string into slug-alike string
     */
    public static function toSlug(string $string): string
    {
        self::$slugMethod ??= (static function (): callable {
            if (class_exists(Slugify::class)) {
                return [self::class, 'toSlugUsingSlugify'];
            }
            if (function_exists('iconv') && iconv('utf-8', 'us-ascii//TRANSLIT', 'ā') === 'a') {
                return [self::class, 'toSlugUsingIconv'];
            }
            throw new \RuntimeException('There is no available method for generating slugs, consider installing iconv extension or cocur/slugify library');
        })();

        return (self::$slugMethod)($string);
    }

    private static function toSlugUsingIconv(string $string): string
    {
        return strtolower(preg_replace('/\s+/', '-', trim(iconv('utf-8', 'us-ascii//TRANSLIT', $string))));
    }

    private static function toSlugUsingSlugify(string $string): string
    {
        self::$slugify ??= new Slugify();
        return self::$slugify->slugify($string);
    }
}
