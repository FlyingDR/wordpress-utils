<?php

namespace Flying\Wordpress;

/**
 * WordPress have no convenient way to remove previously registered filter
 * because remove_filter() requires knowledge about not just function name,
 * but also filter priority that may not be known
 */
class Filter
{
    /**
     * Remove Wordpress filter handler registered by plugins based on given test
     *
     * @param string $filterName
     * @param callable $testFunc
     */
    public static function removeFilter($filterName, $testFunc)
    {
        /** @var \WP_Hook[] $wp_filter */
        $wp_filter = $GLOBALS['wp_filter'];
        if (!array_key_exists($filterName, $wp_filter)) {
            return;
        }

        foreach ($wp_filter[$filterName] as $priority => $callbacks) {
            /** @var callable[] $callbacks */
            foreach ($callbacks as $callback) {
                if (!is_array($callback) || !array_key_exists('function', $callback)) {
                    continue;
                }
                if ($testFunc($callback['function'])) {
                    remove_filter($filterName, $callback['function'], $priority);
                }
            }
        }
    }

    /**
     * Test if given WordPress filter callable is actually an object method reference
     * Useful inside removeFilter() $testFunc in a case if WordPress filter is defined
     * not as a function but as class method
     *
     * @param callable $filter
     * @param string $class
     * @return boolean
     */
    public static function isFilterAsMethodReference($filter, $class = null)
    {
        if ($filter instanceof \Closure) {
            return false;
        }
        $result = is_array($filter) &&
            count($filter) === 2 &&
            array_key_exists(0, $filter) &&
            is_object($filter[0]);
        if ($class) {
            $result &= $filter[0] instanceof $class;
        }
        return (boolean)$result;
    }
}
