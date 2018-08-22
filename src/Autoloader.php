<?php

namespace Flying\Wordpress;

/**
 * Class autoloader for Wordpress
 */
class Autoloader
{
    /**
     * @var string
     */
    private $root;
    /**
     * @var array
     */
    private $transformers;

    /**
     * @param string $root
     * @param array $transformers
     * @param bool $skipDefaultTransformers
     * @throws \Exception
     */
    public function __construct($root, array $transformers = [], $skipDefaultTransformers = false)
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        if (!is_dir($root)) {
            throw new \InvalidArgumentException('Given Wordpress root directory is not actually exists');
        }
        $this->root = $root;
        if (!empty(array_filter($transformers, function ($transformer) {
            return !is_callable($transformer);
        }))) {
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
     *
     * @throws \Exception
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    /**
     * Load given class
     *
     * @param string $class
     */
    public function loadClass($class)
    {
        $root = $this->getRoot();
        foreach ($this->transformers as $transformer) {
            $path = $transformer($class, $root);
            if (!is_string($path)) {
                continue;
            }
            if (file_exists($path)) {
                /** @noinspection PhpIncludeInspection */
                include_once $path;
                return;
            }
            $path = $root . '/' . $path;
            if (file_exists($path)) {
                /** @noinspection PhpIncludeInspection */
                include_once $path;
                return;
            }
        }
    }

    /**
     * @return string
     */
    protected function getRoot()
    {
        return $this->root;
    }

    /**
     * @return array
     */
    protected function getTransformers()
    {
        return $this->transformers;
    }

    /**
     * Get default class name transformation functions
     *
     * @return array
     */
    protected function getDefaultTransformers()
    {
        return [
            function ($class) {
                return str_replace('_', '/', $class) . '.php';
            },
            function ($class) {
                return strtolower(str_replace('_', '/', $class)) . '.php';
            },
            function ($class) {
                return 'class-' . strtolower(str_replace('_', '-', $class)) . '.php';
            },
        ];
    }
}
