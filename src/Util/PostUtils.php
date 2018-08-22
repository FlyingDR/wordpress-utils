<?php

namespace Flying\Wordpress\Util;

use Flying\Wordpress\Query\QueryBuilder;

/**
 * Various utility functions that are related to Wordpress posts
 */
class PostUtils
{
    /**
     * Get Wordpress page by given slug
     *
     * @param string $slug
     * @return string|false
     */
    public static function getPageUrlBySlug($slug)
    {
        /** @var $wpdb \wpdb */
        global $wpdb;
        static $cache = [];

        if (!array_key_exists($slug, $cache)) {
            if (is_string($slug) && (!is_numeric($slug))) {
                /** @noinspection SqlResolve */
                /** @noinspection SqlNoDataSourceInspection */
                $id = (int)$wpdb->get_var(QueryBuilder::buildQuery('select id from ?? where post_name=? and post_type in (?) limit 1', [
                    $wpdb->posts,
                    $slug,
                    array_keys(get_post_types(['public' => true])),
                ]));
            } else {
                $id = (int)$slug;
            }
            $cache[$slug] = get_permalink($id);
        }
        return $cache[$slug];
    }
}
