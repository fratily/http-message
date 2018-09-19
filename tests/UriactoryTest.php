<?php
/**
 * FratilyPHP Http Message
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Tests\Http\Message;

use Fratily\Http\Message\UriFactory;

class UriFactoryTest extends \PHPUnit\Framework\TestCase{

    /**
     * @var UriFactory
     */
    private $factory    = null;

    public function setup(){
        $this->factory  = new UriFactory();
    }

    public function parseUriProvider(){
        return [
            [
                [
                    "scheme"    => "http",
                    "userinfo"  => "user:password",
                    "host"      => "example.com",
                    "port"      => 8080,
                    "path"      => "/segment/segment/",
                    "query"     => "query=value",
                    "fragment"  => "fragment",
                ],
                "http://user:password@example.com:8080/segment/segment/?query=value#fragment",
            ],
            [
                [
                    "scheme"    => "file",
                    "userinfo"  => "",
                    "host"      => "",
                    "port"      => null,
                    "path"      => "/var/www/html/index.html",
                    "query"     => "",
                    "fragment"  => "",
                ],
                "file:///var/www/html/index.html",
            ],
            [
                [
                    "scheme"    => "http",
                    "userinfo"  => "",
                    "host"      => "example.com",
                    "port"      => null,
                    "path"      => "/segment/segment/",
                    "query"     => "",
                    "fragment"  => "",
                ],
                "http://example.com/segment/segment/",
            ],
            [
                false,
                "This is not uri string.",
            ],
/*
 * template
            [
                [
                    "scheme"    => "",
                    "userinfo"  => "",
                    "host"      => "",
                    "port"      => "",
                    "path"      => "",
                    "query"     => "",
                    "fragment"  => "",
                ],
                "",
            ],
 */
        ];
    }

    /**
     * @dataProvider    parseUriProvider
     */
    public function testParseUri($expected, $uri){
        $this->assertSame($expected, UriFactory::parseUri($uri));
    }
}