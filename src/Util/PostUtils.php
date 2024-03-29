<?php

namespace Flying\Wordpress\Util;

use Flying\Wordpress\Query\QueryBuilder;

/**
 * Various utility functions that are related to WordPress posts
 */
class PostUtils
{
    /** @type array<string, ?int> */
    private static array $cache = [];

    /**
     * Get Id of WordPress page by given slug
     */
    public static function getPageIdBySlug(string $slug): ?int
    {
        return self::$cache[$slug] ??= (static function (string $slug): ?int {
            $wpdb = $GLOBALS['wpdb'] ?? null;
            if (!$wpdb instanceof \wpdb) {
                return null;
            }
            if (is_numeric($slug)) {
                $id = $slug;
            } else {
                $id = $wpdb->get_var(QueryBuilder::buildQuery(/** @lang text */ 'select id from ?? where post_name=? and post_type in (?) limit 1', [
                    $wpdb->posts,
                    $slug,
                    array_keys(array_filter(get_post_types(['public' => true]), static fn(string $v): bool => $v !== 'attachment')),
                ]));
            }
            return $id !== null && (int)$id !== 0 ? (int)$id : null;
        })($slug);
    }

    /**
     * Get WordPress page by given slug
     */
    public static function getPageUrlBySlug(string $slug): ?string
    {
        $id = self::getPageIdBySlug($slug);
        if ($id === null) {
            return null;
        }
        $slug = get_permalink($id);
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        return is_string($slug) ? $slug : null;
    }
}
