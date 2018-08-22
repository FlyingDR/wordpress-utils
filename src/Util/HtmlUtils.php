<?php

namespace Flying\Wordpress\Util;

class HtmlUtils
{
    /**
     * List of HTML tags that have no closing tag
     *
     * @var array
     */
    protected static $emptyHtmlTags = [
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
     *
     * @var array
     */
    protected static $nonEmptyHtmlAttrs = [
        'id',
        'class',
        'style',
    ];

    /**
     * Generate HTML tag with given attributes and content
     *
     * @param string $tag      HTML tag name (also tag#id, tag.class, tag.classA.classB, tag.class#id)
     * @param array $attrs     OPTIONAL List of attributes for HTML tag (can be skipped)
     * @param string $content  OPTIONAL Tag contents
     * @param boolean $newline OPTIONAL TRUE to add newline character at the end of tag (or "-" or "=" at last char of tag name)
     * @return string
     */
    public static function tag($tag, $attrs = null, $content = null, $newline = false)
    {
        if (!is_array($attrs)) {
            $newline = (boolean)$content;
            $content = (string)$attrs;
            $attrs = [];
        }
        $tag = trim($tag);
        if (in_array(substr($tag, -1), ['-', '='], true)) {
            $newline = true;
            $tag = (string)substr($tag, 0, -1);
        }
        $t = explode('#', $tag, 2);
        $tag = array_shift($t);
        $t = array_shift($t);
        if ((string)$t !== '') {
            $attrs['id'] = $t;
        }
        $t = explode('.', $tag);
        $tag = array_shift($t);
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
     */
    public static function attrs(array $attrs)
    {
        $html = '';
        foreach ($attrs as $name => $value) {
            $name = htmlspecialchars($name, ENT_COMPAT, 'utf-8');
            if (('constraints' === $name) || (strpos($name, 'on') === 0)) {
                if (!is_scalar($value)) {
                    $value = json_encode($value);
                }
                $value = preg_replace('/"([^"]*)":/', '$1:', $value);
            } elseif (strpos($name, 'data-') === 0) {
                if (!is_scalar($value)) {
                    $value = json_encode($value);
                }
                $value = htmlspecialchars($value, ENT_COMPAT, 'utf-8');
            } else {
                if (is_array($value)) {
                    $value = trim(implode(' ', $value));
                }
                $value = htmlspecialchars($value, ENT_COMPAT, 'utf-8');
            }
            if ('id' === $name && strpos($value, '[') !== false) {
                if ('[]' === substr($value, -2)) {
                    $value = substr($value, 0, -2);
                }
                $value = trim($value, ']');
                $value = str_replace(['][', '['], '-', $value);
            }
            if (($value === '') && in_array($name, self::$nonEmptyHtmlAttrs, true)) {
                continue;
            }
            if (strpos($value, '"') !== false) {
                $html .= ' ' . $name . "='" . $value . "'";
            } else {
                $html .= ' ' . $name . '="' . $value . '"';
            }
        }
        return $html;
    }

    /**
     * Trim tag that surrounds given content and return result
     *
     * @param string $content
     * @param string $tag
     * @param boolean $endTag
     * @return string
     */
    public static function trimTag($content, $tag = null, $endTag = true)
    {
        $regexp = '/^(\s*)\<' . ($tag ?: '[a-z\-]+') . '\s*[^\>]*\>(.*?)' . ($endTag ? '\<\/' . ($tag ?: '[a-z\-]+') . '>' : '') . '(\s*)$/usi';
        return preg_replace($regexp, '\1\2\3', $content);
    }
}
