<?php

namespace Flying\Wordpress\Query;

class QueryBuilder
{
    /**
     * Create SQL query text from given query template and binding list
     *
     * NOTE: In addition to normal parameter bindings, you can also use "??" binding to avoid parameter quoting
     * (for example, if you want to pass table or column name as parameter). Please also note that NULL and
     * boolean values are not allowed to be query parameters.
     *
     * @param string $query         SQL query text
     * @param array|mixed $bindings OPTIONAL List of bindings for this query
     * @param boolean $isUpdate     TRUE/FALSE to force given query to be treated as update/non-update, NULL to autodetect it
     * @return string               Query text with applied bindings
     * @throws \RuntimeException
     */
    public static function buildQuery($query, $bindings = null, $isUpdate = null)
    {
        $escape = function ($v) {
            return strtr($v, [
                chr(34) => chr(92) . chr(34),   // Double quote
                chr(39) => chr(92) . chr(39),   // Single quote
                chr(92) => chr(92) . chr(92),   // Slash
            ]);
        };
        // Check, if it is UPDATE query. For this type of queries query patching for NULL value should be avoided
        if ($isUpdate === null) {
            $isUpdate = (strtolower(strtok($query, ' ')) === 'update');
        }
        if (!is_array($bindings)) {
            $bindings = ($bindings !== null) ? [$bindings] : [];
        }
        // Count the number of bindings available into given SQL query
        preg_match_all('/\?+/', $query, $count);
        $count = count($count[0]);
        if ($count === 0) {
            // There are no bindings in this query
            return $query;
        }

        if ((!is_array($bindings)) || (count($bindings) !== $count)) {
            // We have some placeholders in a query, but have no bindings for them
            // or bindings count is not the same as placeholders count
            throw new \RuntimeException('Given list of bindings doesn\'t match number of placeholders into SQL query');
        }
        // Quote '%' sign in query text, so it will not be recognized as sprintf's special char
        $query = str_replace('%', '%%', $query);
        // Split query into parts and precess each part separately
        // (because we need to patch query text if we get NULL values)
        $parts = explode('?', $query . ' '); // We need this space char to have last part non-empty even if there is '?' char in last position
        $result = [];
        // Prepare the list of bindings in a way, they can be inserted in query text
        foreach ($bindings as $key => $value) {
            if ($value === null) {
                // Check, if we have '=' or '<>' condition - they must be replaced
                // with 'IS NULL' and 'IS NOT NULL' respectively (but not for UPDATE queries)
                if ((!$isUpdate) && preg_match('/^(.*?)\s*(=|\<\>)\s*$/s', $parts[0], $data)) {
                    /** @noinspection NestedPositiveIfStatementsInspection */
                    if (array_key_exists(2, $data)) {
                        if ($data[2] === '=') {
                            array_shift($parts);
                            $parts[0] = $data[1] . ' is null' . $parts[0];
                            unset($bindings[$key]);
                            continue;
                        }
                        if ($data[2] === '<>') {
                            array_shift($parts);
                            $parts[0] = $data[1] . ' is not null' . $parts[0];
                            unset($bindings[$key]);
                            continue;
                        }
                    }
                }
                $bindings[$key] = 'null';
            } elseif ($value === true) {
                $bindings[$key] = 1;
            } elseif ($value === false) {
                $bindings[$key] = 0;
            } elseif (is_array($value)) {
                $tq = substr(str_repeat('?,', count($value)), 0, -1);
                $tv = self::buildQuery($tq, $value, $isUpdate);
                $bindings[$key] = $tv;
            } elseif ((is_string($value) || is_numeric($value)) && array_key_exists(1, $parts) && ($parts[1] === '')) {
                // We have several '?' chars for this binding. It mean that we must not quote it but place it "as is" instead
                // Skip all empty query parts
                while (array_key_exists(1, $parts) && ($parts[1] === '')) {
                    $result[] = array_shift($parts);
                }
            } elseif (preg_match('/^-?0+[1-9]+/', $value)) {
                // It is something like 000001 and more likely NOT a number, so leave it as is
                $bindings[$key] = "'" . $escape($value) . "'";
            } elseif (preg_match('/^-?\d+(\.\d+)?$/', $value)) {
                $bindings[$key] = $value;
            } elseif (is_string($value)) {
                $bindings[$key] = "'" . $escape($value) . "'";
            } else {
                // We have something strange, better throw a warning message
                throw new \RuntimeException(sprintf('Data passed as binding value for "%s" key for SQL query is invalid', $key));
            }
            // Move processed query part to result query
            $result[] = array_shift($parts);
        }
        // Prepare query text to apply bindings
        $query = (string)preg_replace('/\?+/', '%s', implode('?', array_merge($result, $parts)));
        // Apply bindings
        array_unshift($bindings, $query);
        $query = @sprintf(...$bindings);
        // If bindings applying failed for some reason - we need to throw an error, otherwise return resulted query
        if ($query === false) {
            throw new \RuntimeException('Failed to apply bindings list to a query');
        }
        return trim($query);
    }
}
