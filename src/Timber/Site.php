<?php

namespace Flying\Wordpress\Timber;

use Flying\Wordpress\ClientAssetsManager;
use Timber\Menu;
use Timber\Post;
use Timber\Site as TimberSite;
use Timber\Timber;
use Timber\User;

abstract class Site extends TimberSite
{
    /**
     * @var Post
     */
    private static $page;
    /**
     * @var Menu
     */
    private static $menu;
    /**
     * @var User
     */
    private static $user;
    /**
     * @var array
     */
    private static $pageTemplates = [
        '{type}-{slug}.twig',
        'page-{slug}.twig',
        '{type}.twig',
        'page.twig',
    ];

    /**
     * Get current page as Timber post
     *
     * @return Post
     */
    public static function getPage()
    {
        if (!self::$page) {
            self::$page = new Post();
        }
        return self::$page;
    }

    /**
     * Get current menu as Timber menu
     *
     * @return Menu
     */
    public static function getMenu()
    {
        if (!self::$menu) {
            self::$menu = new Menu();
        }
        return self::$menu;
    }

    /**
     * Get current user as Timber user
     *
     * @return User
     */
    public static function getUser()
    {
        if (!self::$user) {
            self::$user = new User();
        }
        return self::$user;
    }

    /**
     * @return array
     */
    public static function getPageTemplates()
    {
        return self::$pageTemplates;
    }

    /**
     * @param array|string $templates
     */
    public static function setPageTemplates($templates)
    {
        self::$pageTemplates = (array)$templates;
    }

    /**
     * Wrapper for Timber::render() to render site pages
     * to allow proper apply of deferred client assets
     * in a case if client assets manager is used
     *
     * @param array $context
     * @param string|array $templates
     * @param boolean $mergeContext
     */
    public static function renderPage(array $context = null, $templates = null, $mergeContext = true)
    {
        if ($templates === null) {
            $templates = self::getPageTemplates();
        }
        $templates = (array)$templates;
        $vars = [
            '{type}' => self::getPage()->post_type,
            '{slug}' => self::getPage()->slug,
        ];
        $templates = array_map(function ($v) use ($vars) {
            return strtr($v, $vars);
        }, $templates);
        if ($context === null) {
            $context = Timber::get_context();
        } elseif ($mergeContext) {
            $context = array_merge(Timber::get_context(), $context);
        }
        Timber::render($templates, $context);
        if (class_exists(ClientAssetsManager::class)) {
            echo ClientAssetsManager::getInstance()->applyAssets();
        }
    }
}
