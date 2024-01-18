<?php

namespace Flying\Wordpress\Util;

class CssUtils
{
    /**
     * Build CSS classes list string from given arguments
     *
     * @param mixed ...$args
     * @return string
     */
    public static function classList(...$args)
    {
        $classes = [];
        array_walk($args, function ($arg) use (&$classes) {
            $parts = [];
            if (is_array($arg)) {
                $parts = $arg;
            } elseif (is_string($arg)) {
                $parts = explode(' ', $arg);
            }
            array_walk_recursive($parts, function ($cl) use (&$classes) {
                $cl = trim($cl);
                if ($cl !== '' && !in_array($cl, $classes, true)) {
                    $classes[] = $cl;
                }
            });
        });
        return implode(' ', $classes);
    }

    /**
     * @param string|array $classList
     * @param string $class
     * @param callable $modificator
     * @return string
     */
    private static function updateClassList($classList, $class, $modificator) {
        if (is_string($classList)) {
            $classList = explode(' ', $classList);
        }
        $classList = array_map('trim', $classList);
        $class = trim($class);
        $classList = $modificator($classList, $class);
        return implode(' ', $classList);
    }

    /**
     * Add given CSS class to the given list of CSS classes
     *
     * @param string|array $classList
     * @param string $class
     * @return string
     */
    public static function addClass($classList, $class)
    {
        return self::updateClassList($classList, $class, function (array $classList, $class) {
            if (!in_array($class, $classList, true)) {
                $classList[] = $class;
            }
            return $classList;
        });
    }

    /**
     * Remove given CSS class from the given list of CSS classes
     *
     * @param string|array $classList
     * @param string $class
     * @return string
     */
    public static function removeClass($classList, $class)
    {
        return self::updateClassList($classList, $class, function (array $classList, $class) {
            return array_filter($classList, function ($v) use ($class) {
                return $v !== $class;
            });
        });
    }
}
