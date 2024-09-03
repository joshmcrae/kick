<?php

namespace Kick\View;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Element::class)]
class ElementTest extends TestCase
{
    public function testBasic()
    {
        $el = new Element('div', false, class: 'container');

        $this->assertEquals('<div class="container"></div>', (string) $el);
    }

    public function testVoid()
    {
        $el = new Element('input', true, name: 'email');

        $this->assertEquals('<input name="email"/>', (string) $el);
    }

    public function testAttributeEncoding()
    {
        $el = new Element('input', true, placeholder: 'before > after');

        $this->assertEquals('<input placeholder="before &gt; after"/>', (string) $el);
    }

    public function testAttributeCase()
    {
        $el = new Element('div', false, x_show: 'open');

        $this->assertEquals('<div x-show="open"></div>', (string) $el);
    }

    public function testInnerText()
    {
        $el = new Element('p', false, 'Hello, world!<br/>');

        $this->assertEquals('<p>Hello, world!&lt;br/&gt;</p>', (string) $el);
    }

    public function testInnerHtml()
    {
        $el = new Element('div', false, new Element('p', false, 'Hello!', class: 'warning'));

        $this->assertEquals('<div><p class="warning">Hello!</p></div>', (string) $el);
    }

    public function testStatic()
    {
        $button = Element::button('Submit', type: 'submit');
        $br = Element::br();

        $this->assertEquals('<button type="submit">Submit</button>', (string) $button);
        $this->assertEquals('<br/>', (string) $br);
    }

    public function testHtml5()
    {
        $doc = Element::html();

        $this->assertEquals('<!DOCTYPE html><html></html>', (string) $doc);
    }

    public function testSpecialAttribute()
    {
        $button = Element::button(
            'Load more...',
            Element::attr('@click', 'show = true')
        );

        $this->assertEquals('<button @click="show = true">Load more...</button>', (string) $button);
    }
}
