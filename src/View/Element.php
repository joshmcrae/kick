<?php

namespace Kick\View;

/**
 * Class Element.
 *
 * @method static Element a(...$args)
 * @method static Element abbr(...$args)
 * @method static Element address(...$args)
 * @method static Element area(...$args)
 * @method static Element article(...$args)
 * @method static Element aside(...$args)
 * @method static Element audio(...$args)
 * @method static Element b(...$args)
 * @method static Element base(...$args)
 * @method static Element bdi(...$args)
 * @method static Element bdo(...$args)
 * @method static Element blockquote(...$args)
 * @method static Element body(...$args)
 * @method static Element br(...$args)
 * @method static Element button(...$args)
 * @method static Element canvas(...$args)
 * @method static Element caption(...$args)
 * @method static Element cite(...$args)
 * @method static Element code(...$args)
 * @method static Element col(...$args)
 * @method static Element colgroup(...$args)
 * @method static Element data(...$args)
 * @method static Element datalist(...$args)
 * @method static Element dd(...$args)
 * @method static Element del(...$args)
 * @method static Element details(...$args)
 * @method static Element dfn(...$args)
 * @method static Element dialog(...$args)
 * @method static Element div(...$args)
 * @method static Element dl(...$args)
 * @method static Element dt(...$args)
 * @method static Element em(...$args)
 * @method static Element embed(...$args)
 * @method static Element fieldset(...$args)
 * @method static Element figcaption(...$args)
 * @method static Element figure(...$args)
 * @method static Element footer(...$args)
 * @method static Element form(...$args)
 * @method static Element h1(...$args)
 * @method static Element h2(...$args)
 * @method static Element h3(...$args)
 * @method static Element h4(...$args)
 * @method static Element h5(...$args)
 * @method static Element h6(...$args)
 * @method static Element head(...$args)
 * @method static Element header(...$args)
 * @method static Element hgroup(...$args)
 * @method static Element hr(...$args)
 * @method static Element html(...$args)
 * @method static Element i(...$args)
 * @method static Element iframe(...$args)
 * @method static Element img(...$args)
 * @method static Element input(...$args)
 * @method static Element ins(...$args)
 * @method static Element kbd(...$args)
 * @method static Element keygen(...$args)
 * @method static Element label(...$args)
 * @method static Element legend(...$args)
 * @method static Element li(...$args)
 * @method static Element link(...$args)
 * @method static Element main(...$args)
 * @method static Element map(...$args)
 * @method static Element mark(...$args)
 * @method static Element menu(...$args)
 * @method static Element meta(...$args)
 * @method static Element meter(...$args)
 * @method static Element nav(...$args)
 * @method static Element noscript(...$args)
 * @method static Element object(...$args)
 * @method static Element ol(...$args)
 * @method static Element optgroup(...$args)
 * @method static Element option(...$args)
 * @method static Element output(...$args)
 * @method static Element p(...$args)
 * @method static Element param(...$args)
 * @method static Element picture(...$args)
 * @method static Element pre(...$args)
 * @method static Element progress(...$args)
 * @method static Element q(...$args)
 * @method static Element rp(...$args)
 * @method static Element rt(...$args)
 * @method static Element ruby(...$args)
 * @method static Element s(...$args)
 * @method static Element samp(...$args)
 * @method static Element script(...$args)
 * @method static Element section(...$args)
 * @method static Element select(...$args)
 * @method static Element small(...$args)
 * @method static Element source(...$args)
 * @method static Element span(...$args)
 * @method static Element strong(...$args)
 * @method static Element style(...$args)
 * @method static Element sub(...$args)
 * @method static Element summary(...$args)
 * @method static Element sup(...$args)
 * @method static Element table(...$args)
 * @method static Element tbody(...$args)
 * @method static Element td(...$args)
 * @method static Element textarea(...$args)
 * @method static Element tfoot(...$args)
 * @method static Element th(...$args)
 * @method static Element thead(...$args)
 * @method static Element time(...$args)
 * @method static Element title(...$args)
 * @method static Element tr(...$args)
 * @method static Element track(...$args)
 * @method static Element u(...$args)
 * @method static Element ul(...$args)
 * @method static Element var(...$args)
 * @method static Element video(...$args)
 * @method static Element wbr(...$args)
 */
class Element
{
    private const VOID_TAG_NAMES = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr'
    ];

    /**
     * Element attributes.
     *
     * @var array<string,string>
     */
    private array $attributes = [];

    /**
     * Element children.
     *
     * @var array<Element|string>
     */
    private array $children = [];

    public static function attr(string $name, string $value): Attribute
    {
        return new Attribute($name, $value);
    }

    /**
     * Dynamically creates an element by name.
     *
     * @param string $name
     * @param array<string,mixed> $args
     * @return self
     */
    public static function __callStatic(string $name, array $args): self
    {
        return new self($name, in_array($name, self::VOID_TAG_NAMES), ...$args);
    }

    /**
     * Element constructor.
     *
     * @param string $tagName 
     * @param bool $isVoid 
     * @param mixed ...$args 
     * @return void 
     */
    public function __construct(
        public string $tagName,
        public bool   $isVoid,
                   ...$args  
    ) {
        foreach ($args as $name => $value) {
            if ($value instanceof Attribute) {
                $this->attributes[$value->name] = $value->value;
            } elseif (is_string($name)) {
                $this->attributes[$name] = $value;
            } else {
                if (!is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $child) {
                    $this->children[] = $child;
                }
            }
        }
    }

    /**
     * Renders the element for output.
     *
     * @return string
     */
    public function __toString(): string
    {
        $str = '';

        if ($this->tagName === 'html') {
            $str .= '<!DOCTYPE html>';
        }

        $str .= '<' . $this->tagName;

        // Render attributes
        foreach ($this->attributes as $name => $value) {
            $name = strtolower(str_replace('_', '-', $name));

            if (is_array($value)) {
                $values = [];

                foreach ($value as $k => $v) {
                    if (is_string($k)) {
                        if (boolval($v)) {
                            $values[] = $k;
                        }
                    } else {
                        $values[] = $v;
                    }
                }

                $value = implode(' ', $values);
            }

            if (is_bool($value)) {
                $str .= ' ' . $name;
            } else {
                $str .= sprintf(' %s="%s"', $name, htmlspecialchars($value));
            }
        }

        if ($this->isVoid) {
            $str .= '/>';

            // Void elements cannot have children
            return $str;
        }

        $str .= '>';

        // Render inner text and HTML
        foreach ($this->children as $child) {
            if (is_string($child)) {
                $str .= htmlspecialchars($child);
            } else {
                $str .= (string) $child;
            }
        }

        $str .= '</' . $this->tagName . '>';

        return $str;
    }
}
