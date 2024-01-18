<?php

namespace Flying\Wordpress;

/**
 * Class autoloader for WordPress
 */
class Autoloader
{
    private string $root;
    private array $transformers;

    public function __construct(string $root, array $transformers = [], bool $skipDefaultTransformers = false)
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        if (!is_dir($root)) {
            throw new \InvalidArgumentException('Given WordPress root directory does not actually exists');
        }
        $this->root = $root;
        if (!empty(array_filter($transformers, static fn($transformer) => !is_callable($transformer)))) {
            throw new \InvalidArgumentException('Class name transformers should be defined as array of callables');
        }
        if (!$skipDefaultTransformers) {
            $transformers = array_merge($this->getDefaultTransformers(), $transformers);
        }
        $this->transformers = $transformers;
        $this->register();
    }

    /**
     * Installs this class loader on the SPL autoload stack.
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     */
    public function unregister(): void
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    /**
     * Load given class
     */
    public function loadClass(string $class): void
    {
        $root = $this->getRoot();
        foreach ($this->transformers as $transformer) {
            $path = $transformer($class, $root);
            if (!is_string($path)) {
                continue;
            }
            if (is_file($path)) {
                include_once $path;
                return;
            }
            $path = $root . '/' . $path;
            if (is_file($path)) {
                include_once $path;
                return;
            }
        }
    }

    protected function getRoot(): string
    {
        return $this->root;
    }

    protected function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * Get default class name transformation functions
     */
    protected function getDefaultTransformers(): array
    {
        return [
            fn(string $class): string => str_replace('_', '/', $class) . '.php',
            fn(string $class): string => strtolower(str_replace('_', '/', $class)) . '.php',
            fn(string $class): string => 'class-' . strtolower(str_replace('_', '-', $class)) . '.php',
        ];
    }
}
