<?php /** @noinspection DevelopmentDependenciesUsageInspection */

namespace Flying\Wordpress\Timber;

use Flying\Wordpress\ClientAssetsManager;
use Timber\Menu;
use Timber\Post;
use Timber\Site as TimberSite;
use Timber\Timber;
use Timber\User;

abstract class Site extends TimberSite
{
    private static Post $page;
    private static Menu $menu;
    private static User $user;
    private static array $pageTemplates = [
        '{type}-{slug}.twig',
        'page-{slug}.twig',
        '{slug}.twig',
        '{type}.twig',
        'page.twig',
    ];

    /**
     * Get current page as Timber post
     */
    public static function getPage(): Post
    {
        return self::$page ??= new Post();
    }

    /**
     * Get current menu as Timber menu
     */
    public static function getMenu(): Menu
    {
        return self::$menu ??= new Menu();
    }

    /**
     * Get current user as Timber user
     */
    public static function getUser(): User
    {
        return self::$user ??= new User();
    }

    public static function getPageTemplates(): array
    {
        return self::$pageTemplates;
    }

    /**
     * @param array|string $templates
     */
    public static function setPageTemplates($templates): void
    {
        self::$pageTemplates = (array)$templates;
    }

    /**
     * Wrapper for Timber::render() to render site pages
     * to allow proper apply of deferred client assets
     * in a case if client assets manager is used
     *
     * @param array|null $context
     * @param string|array|null $templates
     * @param boolean $mergeContext
     */
    public static function renderPage(?array $context = null, $templates = null, bool $mergeContext = true): void
    {
        $templates ??= self::getPageTemplates();
        $templates = (array)$templates;
        $vars = [
            '{type}' => self::getPage()->post_type,
            '{slug}' => self::getPage()->slug,
        ];
        $templates = array_map(static fn(string $v): string => strtr($v, $vars), $templates);
        if ($context === null) {
            $context = Timber::get_context();
        } elseif ($mergeContext) {
            $context = array_merge(Timber::get_context(), $context);
        }
        /** @noinspection UnusedFunctionResultInspection */
        Timber::render($templates, $context);
        if (class_exists(ClientAssetsManager::class)) {
            echo ClientAssetsManager::getInstance()->applyAssets();
        }
    }
}
