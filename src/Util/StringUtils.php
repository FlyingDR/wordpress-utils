<?php

namespace Flying\Wordpress\Util;

/**
 * Various utility functions that are related to strings processing
 */
class StringUtils
{
    /**
     * Convert given string into slug-alike string
     *
     * @param string $string
     * @return string
     */
    public static function toSlug($string)
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        return strtolower(preg_replace('/\s+/', '-', trim(iconv('utf-8', 'us-ascii//TRANSLIT', $string))));
    }
}
