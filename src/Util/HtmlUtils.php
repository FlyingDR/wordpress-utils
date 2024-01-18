<?php

namespace Flying\Wordpress\Util;

class HtmlUtils
{
    /**
     * List of HTML tags that have no closing tag
     */
    private static array $emptyHtmlTags = [
        'area',
        'base',
        'basefont',
        'br',
        'col',
        'frame',
        'hr',
        'img',
        'input',
        'isindex',
        'link',
        'meta',
        'param',
        // HTML5 tags
        'wbr',
        'embed',
        'source',
        'track',
        'keygen',
        'menuitem',
    ];
    /**
     * List of HTML attributes that only have meaning being non-empty
     */
    private static array $nonEmptyHtmlAttrs = [
        'id',
        'class',
        'style',
    ];

    /**
     * Generate HTML tag with given attributes and content
     *
     * @param string $tag          HTML tag name (also tag#id, tag.class, tag.classA.classB, tag.class#id)
     * @param array|null $attrs    OPTIONAL List of attributes for HTML tag
     * @param string|null $content OPTIONAL Tag contents
     * @param boolean $newline     OPTIONAL TRUE to add newline character at the end of tag (or "-" or "=" at last char of tag name)
     * @return string
     */
    public static function tag(string $tag, ?array $attrs = [], ?string $content = null, bool $newline = false): string
    {
        $tag = trim($tag);
        if (in_array(substr($tag, -1), ['-', '='], true)) {
            $newline = true;
            $tag = substr($tag, 0, -1);
        }
        $t = explode('#', $tag, 2);
        $tag = (string)array_shift($t);
        $t = array_shift($t);
        if ((string)$t !== '') {
            $attrs['id'] = $t;
        }
        $t = explode('.', $tag);
        $tag = (string)array_shift($t);
        if (count($t)) {
            if (!array_key_exists('class', $attrs)) {
                $attrs['class'] = [];
            } elseif (!is_array($attrs['class'])) {
                $attrs['class'] = [$attrs['class']];
            }
            $attrs['class'] = array_merge($attrs['class'], $t);
        }
        $tag = strtolower(trim($tag));
        $html = '<' . $tag . self::attrs($attrs);
        if (!in_array($tag, self::$emptyHtmlTags, true)) {
            $html .= '>' . $content . '</' . $tag . '>';
        } else {
            $html .= ' />';
        }
        if ($newline) {
            $html .= "\n";
        }
        return $html;
    }

    /**
     * Render given HTML attributes
     *
     * @param array $attrs List of attributes to render
     * @return string
     * @throws \JsonException
     */
    public static function attrs(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $name => $value) {
            $name = htmlspecialchars($name, ENT_COMPAT, 'utf-8');
            if ('constraints' === $name || str_starts_with($name, 'on')) {
                if (!is_scalar($value)) {
                    $value = json_encode($value, JSON_THROW_ON_ERROR);
                }
                $value = preg_replace('/"([^"]*)":/', '$1:', $value);
            } elseif (str_starts_with($name, 'data-')) {
                if (!is_scalar($value)) {
                    $value = json_encode($value, JSON_THROW_ON_ERROR);
                }
                $value = htmlspecialchars($value, ENT_COMPAT, 'utf-8');
            } else {
                if (is_array($value)) {
                    $value = trim(implode(' ', $value));
                }
                $value = htmlspecialchars($value, ENT_COMPAT, 'utf-8');
            }
            if ('id' === $name && str_contains($value, '[')) {
                if (str_ends_with($value, '[]')) {
                    $value = substr($value, 0, -2);
                }
                $value = trim($value, ']');
                $value = str_replace(['][', '['], '-', $value);
            }
            if ($value === '' && in_array($name, self::$nonEmptyHtmlAttrs, true)) {
                continue;
            }
            if (str_contains($value, '"')) {
                $html .= ' ' . $name . "='" . $value . "'";
            } else {
                $html .= ' ' . $name . '="' . $value . '"';
            }
        }
        return $html;
    }

    /**
     * Trim tag that surrounds given content and return result
     */
    public static function trimTag(string $content, ?string $tag = null, bool $endTag = true): string
    {
        /** @noinspection RegExpRedundantEscape */
        $regexp = '/^(\s*)\<' . ($tag ?: '[a-z\-]+') . '\s*[^\>]*\>(.*?)' . ($endTag ? '\<\/' . ($tag ?: '[a-z\-]+') . '>' : '') . '(\s*)$/usi';
        return preg_replace($regexp, '\1\2\3', $content);
    }
}
