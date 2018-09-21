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

use Fratily\Http\Message\Request;
use Fratily\Http\Message\Uri;
use Psr\Http\Message\UriInterface;

class RequestTest extends \PHPUnit\Framework\TestCase{

    const URI_INTERFACE_METHODS = [
        "getScheme",
        "getAuthority",
        "getUserInfo",
        "getHost",
        "getPort",
        "getPath",
        "getQuery",
        "getFragment",
        "withScheme",
        "withUserInfo",
        "withHost",
        "withPort",
        "withPath",
        "withQuery",
        "withFragment",
        "__toString",
    ];

    /**
     * @var Request
     */
    private $stdRequest = null;

    public function setup(){
        $this->stdRequest   = new Request("GET", $this->createMock(UriInterface::class));
    }

    public function testWithRequestTargetReturnsNewInstanceWithNewProvidedRequestTarget(){
        $uri = $this->getMockBuilder(Uri::class)
            ->disableOriginalConstructor()
            ->setMethods(self::URI_INTERFACE_METHODS)
            ->getMock()
        ;

        $uri
            ->expects($this->once())
            ->method("withPath")
            ->with($this->equalTo("/foo/bar"))
            ->willReturn($uri)
        ;

        $uri
            ->expects($this->once())
            ->method("withQuery")
            ->with($this->equalTo("query=value"))
            ->willReturn($uri)
        ;

        // Request.php: 99
        $uri
            ->method("getPath")
            ->will($this->onConsecutiveCalls("", "/foo/bar"))
        ;

        $uri
            ->method("getQuery")
            ->willReturn("query=value")
        ;

        $request    = new Request("GET", $uri);
        $new        = $request->withRequestTarget("/foo/bar?query=value");

        $this->assertNotSame($request, $new);
        $this->assertSame("/foo/bar?query=value", $new->getRequestTarget());
    }

    public function testWithMethodReturnsNewInstanceWithNewProvidedMethod(){
        $req    = $this->stdRequest;
        $new    = $req->withMethod("POST");

        $this->assertNotSame($req, $new);
        $this->assertSame("POST", $new->getMethod());
    }

    public function testWithUriAndPreserveHostReturnsNewInstanceWithNewProvidedUri(){
        $uri    = $this->createMock(UriInterface::class);
        $req    = $this->stdRequest;
        $new    = $req->withUri($uri, true);

        $this->assertNotSame($req, $new);
        $this->assertSame($uri, $new->getUri());
    }

    public function testWithUriReturnsNewInstanceWithNewProvidedUri(){
        $uri    = $this->createMock(UriInterface::class);
        $uri->method("getHost")->willReturn("example.com");
        $uri->method("getPort")->willReturn(8080);

        $req    = $this->stdRequest;
        $new    = $req->withUri($uri, false);

        $this->assertNotSame($req, $new);
        $this->assertSame($uri, $new->getUri());
        $this->assertSame("example.com:8080", $new->getHeaderLine("Host"));
    }
}