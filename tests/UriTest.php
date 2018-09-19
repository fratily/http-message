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

use Fratily\Http\Message\Uri;

class UriTest extends \PHPUnit\Framework\TestCase{

    /**
     * @var Uri
     */
    private $stdUri = null;

    public function setup(){
        $this->stdUri   = Uri::newInstance(
            "https",
            "user:pass",
            "example.com",
            8080,
            "path",
            "query=value",
            "fragment"
        );
    }

    public function testSetsAllProperties(){
        $uri    = $this->stdUri;

        $this->assertSame("https", $uri->getScheme());
        $this->assertSame("user:pass", $uri->getUserInfo());
        $this->assertSame("example.com", $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame("user:pass@example.com:8080", $uri->getAuthority());
        $this->assertSame("/path", $uri->getPath());
        $this->assertSame("query=value", $uri->getQuery());
        $this->assertSame("fragment", $uri->getFragment());
    }

    public function testCastToString(){
        $uri    = $this->stdUri;

        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=value#fragment",
            (string)$uri
        );
    }

    public function testWithSchemeReturnsNewInstanceWithNewScheme(){
        $uri    = $this->stdUri;
        $new    = $uri->withScheme("http");

        $this->assertNotSame($uri, $new);
        $this->assertSame("http", $new->getScheme());
        $this->assertSame(
            "http://user:pass@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithUserInfoReturnsNewInstanceWithProvidedUser(){
        $uri    = $this->stdUri;
        $new    = $uri->withUserInfo("kento-oka");

        $this->assertNotSame($uri, $new);
        $this->assertSame("kento-oka", $new->getUserInfo());
        $this->assertSame(
            "https://kento-oka@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithUserInfoReturnsNewInstanceWithProvidedUserAndPassword(){
        $uri    = $this->stdUri;
        $new    = $uri->withUserInfo("kento-oka", "qwerty");

        $this->assertNotSame($uri, $new);
        $this->assertSame("kento-oka:qwerty", $new->getUserInfo());
        $this->assertSame(
            "https://kento-oka:qwerty@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithUserInfoReturnsSameInstanceIfUserAndPasswordAreSameAsBefore(){
        $uri    = $this->stdUri;
        $new    = $uri->withUserInfo("user", "pass");

        $this->assertSame($uri, $new);
        $this->assertSame("user:pass", $new->getUserInfo());
        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithHostReturnsNewInstanceWithProvidedHost(){
        $uri    = $this->stdUri;
        $new    = $uri->withHost("kentoka.com");

        $this->assertNotSame($uri, $new);
        $this->assertSame("kentoka.com", $new->getHost());
        $this->assertSame(
            "https://user:pass@kentoka.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithHostReturnsSameInstanceWithProvidedHostIsSameAsBefore(){
        $uri    = $this->stdUri;
        $new    = $uri->withHost("example.com");

        $this->assertSame($uri, $new);
        $this->assertSame("example.com", $new->getHost());
        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithPortReturnsNewInstanceWithProvidedPort(){
        $uri    = $this->stdUri;
        $new1   = $uri->withPort(8888);
        $new2   = $uri->withPort(null);

        $this->assertNotSame($uri, $new1);
        $this->assertEquals(8888, $new1->getPort());
        $this->assertSame(
            "https://user:pass@example.com:8888/path?query=value#fragment",
            (string)$new1
        );

        $this->assertNotSame($uri, $new2);
        $this->assertEquals(null, $new2->getPort());
        $this->assertSame(
            "https://user:pass@example.com/path?query=value#fragment",
            (string)$new2
        );
    }

    public function testWithPathReturnsNewInstanceWithProvidedPath(){
        $uri    = $this->stdUri;
        $new    = $uri->withPath("/foo/bar");

        $this->assertNotSame($uri, $new);
        $this->assertSame("/foo/bar", $new->getPath());
        $this->assertSame(
            "https://user:pass@example.com:8080/foo/bar?query=value#fragment",
            (string)$new
        );
    }

    public function testWithPathReturnsSameInstanceWithProvidedPathSameAsBefore(){
        $uri    = $this->stdUri;
        $new    = $uri->withPath("path");

        $this->assertSame($uri, $new);
        $this->assertSame("/path", $new->getPath()); // pathは必ず先頭にスラッシュが付くようになっている
        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithQueryReturnsNewInstanceWithProvidedQuery(){
        $uri    = $this->stdUri;
        $new    = $uri->withQuery("query=new_value");

        $this->assertNotSame($uri, $new);
        $this->assertSame("query=new_value", $new->getQuery());
        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=new_value#fragment",
            (string)$new
        );
    }

    public function testWithQueryReturnsSameInstanceWithProvidedQuerySameAsBefore(){
        $uri    = $this->stdUri;
        $new    = $uri->withQuery("query=value");

        $this->assertSame($uri, $new);
        $this->assertSame("query=value", $new->getQuery());
        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function testWithFragmentReturnsNewInstanceWithProvidedFragment(){
        $uri    = $this->stdUri;
        $new    = $uri->withFragment("new_fragment");

        $this->assertNotSame($uri, $new);
        $this->assertSame("new_fragment", $new->getFragment());
        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=value#new_fragment",
            (string)$new
        );
    }

    public function testWithFragmentReturnsSameInstanceWithProvidedFragmentSameAsBefore(){
        $uri    = $this->stdUri;
        $new    = $uri->withFragment("fragment");

        $this->assertSame($uri, $new);
        $this->assertSame("fragment", $new->getFragment());
        $this->assertSame(
            "https://user:pass@example.com:8080/path?query=value#fragment",
            (string)$new
        );
    }

    public function authorityInfoProvider()
    {
        return [
            "host-only"      => ["example.com"          , ""    , "example.com", null],
            "host-port"      => ["example.com:8080"     , ""    , "example.com", 8080],
            "user-host"      => ["user@example.com"     , "user", "example.com", null],
            "user-host-port" => ["user@example.com:8080", "user", "example.com", 8080],
        ];
    }

    /**
     * @dataProvider    authorityInfoProvider
     */
    public function testRetrievingAuthorityReturnsExpectedValues($expected, $user, $host, $port){
        $uri    = Uri::newInstance("http", $user, $host, $port);

        $this->assertSame($expected, $uri->getAuthority());
    }
}