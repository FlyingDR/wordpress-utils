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
    public static function classList(...$args): string
    {
        $classes = [];
        array_walk($args, static function ($arg) use (&$classes) {
            $parts = [];
            if (is_array($arg)) {
                $parts = $arg;
            } elseif (is_string($arg)) {
                $parts = explode(' ', $arg);
            }
            array_walk_recursive($parts, static function ($cl) use (&$classes) {
                $cl = trim((string)$cl);
                if ($cl !== '' && !in_array($cl, $classes, true)) {
                    $classes[] = $cl;
                }
            });
        });
        return implode(' ', $classes);
    }

    /**
     * @param string|string[] $classList
     * @param string $class
     * @param callable(array, string): array $modificator
     * @return string
     */
    private static function updateClassList(array|string $classList, string $class, callable $modificator): string
    {
        if (is_string($classList)) {
            $classList = explode(' ', $classList);
        }
        $classList = array_map(trim(...), $classList);
        $class = trim($class);
        $classList = $modificator($classList, $class);
        return implode(' ', $classList);
    }

    /**
     * Add given CSS class to the given list of CSS classes
     *
     * @param string|string[] $classList
     * @param string $class
     * @return string
     */
    public static function addClass(array|string $classList, string $class): string
    {
        return self::updateClassList($classList, $class, static function (array $classList, string $class): array {
            if (!in_array($class, $classList, true)) {
                $classList[] = $class;
            }
            return $classList;
        });
    }

    /**
     * Remove given CSS class from the given list of CSS classes
     *
     * @param string|string[] $classList
     * @param string $class
     * @return string
     */
    public static function removeClass(array|string $classList, string $class): string
    {
        return self::updateClassList(
            $classList,
            $class,
            static fn(array $classList, $class) => array_filter($classList, static fn($v) => $v !== $class)
        );
    }
}
