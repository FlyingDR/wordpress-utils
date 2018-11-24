<?php

namespace Flying\Wordpress\Util;

use Cocur\Slugify\Slugify;

/**
 * Various utility functions that are related to strings processing
 */
class StringUtils
{
    /**
     * @var string
     */
    private static $slugMethod;
    /**
     * @var Slugify
     */
    private static $slugify;

    /**
     * Convert given string into slug-alike string
     *
     * @param string $string
     * @return string
     */
    public static function toSlug($string)
    {
        if (!self::$slugMethod) {
            if (iconv('utf-8', 'us-ascii//TRANSLIT', 'ā') === 'a') {
                self::$slugMethod = 'iconv';
            } elseif (class_exists(Slugify::class)) {
                self::$slugMethod = 'slugify';
                self::$slugify = new Slugify();
            } else {
                throw new \RuntimeException('There is no available method for generating slugs, consider installing cocur/slugify library');
            }
        }
        switch (self::$slugMethod) {
            case 'iconv':
                return strtolower(preg_replace('/\s+/', '-', trim(iconv('utf-8', 'us-ascii//TRANSLIT', $string))));
                break;
            case 'slugify':
                return self::$slugify->slugify($string);
                break;
        }
        return $string;
    }
}
