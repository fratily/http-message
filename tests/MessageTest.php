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

use Fratily\Http\Message\Message;
use Psr\Http\Message\StreamInterface;

class MessageTest extends \PHPUnit\Framework\TestCase{

    /**
     * @var Message
     */
    private $stdMessage = null;

    public function setup(){
        $this->stdMessage   = new Message();
    }

    public function testWithProtocolVersionReturnsNewInstanceWithNewProvidedProtocolVersion(){
        $msg    = $this->stdMessage;
        $new    = $msg->withProtocolVersion("2.0");

        $this->assertNotSame($msg, $new);
        $this->assertSame("2.0", $new->getProtocolVersion());
    }

    public function testWithHeaderReturnsNewInstanceWithNewProvidedHeader(){
        $msg    = $this->stdMessage;
        $new    = $msg->withHeader("X-Foo-Bar", ["foo", "bar", "baz"]);

        $this->assertNotSame($msg, $new);
        $this->assertSame(["foo", "bar", "baz"], $new->getHeader("X-Foo-Bar"));
        $this->assertSame("foo,bar,baz", $new->getHeaderLine("X-Foo-Bar"));
    }

    public function testWithAddedHeaderReturnsNewInstanceWithNewProvidedAddedHeader(){
        $msg    = $this->stdMessage;
        $new1   = $msg->withHeader("X-Foo-Bar", ["foo", "bar", "baz"]);
        $new2   = $new1->withAddedHeader("X-Foo-Bar", ["hoge", "fuga"]);

        $this->assertNotSame($msg, $new1);
        $this->assertNotSame($msg, $new2);
        $this->assertNotSame($new1, $new2);
        $this->assertSame(["foo", "bar", "baz", "hoge", "fuga"], $new2->getHeader("X-Foo-Bar"));
        $this->assertSame("foo,bar,baz,hoge,fuga", $new2->getHeaderLine("X-Foo-Bar"));
    }

    public function testWithBodyReturnsNewInstanceWithProvidedBody(){
        $body   = $this->createMock(StreamInterface::class);
        $msg    = $this->stdMessage;
        $new    = $msg->withBody($body);

        $this->assertNotSame($msg, $new);
        $this->assertSame($body, $new->getBody());
    }
}