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
namespace Fratily\Http\Message;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 *
 */
class UriFactory implements UriFactoryInterface{

    /**
     * このURIファクトリで受け付けるURI文字列の正規表現
     *
     * RFCで定義されているURIに必ずしも一致するわけではないことに注意
     *
     * 以下のような文字列に一致する
     *
     * - http://userinfo@example.com:8080/path?query#fragment
     * - file:///var/www/html/index.html
     * - //userinfo@example.com:8080/path?query#fragment
     * - /path?query#fragment
     *
     * @var string
     */
    const REGEX_URI = "`\A(?:(?<scheme>[a-z][0-9a-z-+.]*):)?(?://(?:(?:(?<useri"
        . "nfo>(?:%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:])*)@)?(?<host>\[(?:"
        . "::(?:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?::(?:[0-9a-f]|[1-9a-f][0-9a"
        . "-f]{1,3})){0,5})?|(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?:::(?:(?:[0-9a"
        . "-f]|[1-9a-f][0-9a-f]{1,3})(?::(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,"
        . "4})?|:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?:::(?:(?:[0-9a-f]|[1-9a-f]"
        . "[0-9a-f]{1,3})(?::(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,3})?|:(?:[0-"
        . "9a-f]|[1-9a-f][0-9a-f]{1,3})(?:::(?:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3"
        . "})(?::(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,2})?|:(?:[0-9a-f]|[1-9a-"
        . "f][0-9a-f]{1,3})(?:::(?:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?::(?:[0-"
        . "9a-f]|[1-9a-f][0-9a-f]{1,3}))?)?|:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})"
        . "(?:::(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})?|(?::(?:[0-9a-f]|[1-9a-f][0-"
        . "9a-f]{1,3})){3})))))|v[0-9a-f]\.(?:[0-9a-z-._~!$&'()*+,;=:])+)\]|(?:"
        . "%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=])+)(?::(?<port>[1-9][0-9]*)"
        . ")?)?)?(?<path>(?:/(?:%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@])*)*"
        . ")(?:\?(?<query>(?:%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@/?[\]])*"
        . "))?(?:#(?<fragment>(?:%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@/?])"
        . "*))?\z`i"
    ;

    /**
     * URL文字列を構造ごとに分割する
     *
     * @param   string  $uri
     *
     * @return  string[]|bool
     */
    public static function parseUri(string $uri){
        if(1 !== preg_match(static::REGEX_URI, $uri, $m)){
            return false;
        }

        $result = [
            "scheme"    => $m["scheme"] ?? "",
            "userinfo"  => $m["userinfo"] ?? "",
            "host"      => $m["host"] ?? "",
            "port"      => "" === $m["port"] ? null : (int)$m["port"],
            "path"      => $m["path"] ?? "",
            "query"     => $m["query"] ?? "",
            "fragment"  => $m["fragment"] ?? "",
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ""): UriInterface{
        if("" === $uri){
            return Uri::newInstance();
        }

        if(false === ($parts = self::parseUri($uri))){
            throw new \InvalidArgumentException();
        }

        return Uri::newInstance(
            $parts["scheme"],
            $parts["userinfo"],
            $parts["host"],
            $parts["port"],
            $parts["path"],
            $parts["query"],
            $parts["fragment"]
        );
    }

    /**
     * グローバルな値からリクエストURIを作成する
     *
     * @return  UriInterface
     */
    public function createUriFromGlobal(): UriInterface{
        return $this->createUri(
            $this->resolveScheme()
            . "://"
            . $this->resolveAuthority()
            . $this->resolvePathAndQuery()
        );
    }


    /**
     * サーバー変数からスキームを解決する
     *
     * @return  string
     */
    private static function resolveScheme(){
        $scheme = filter_input(INPUT_SERVER, "REQUEST_SCHEME");
        $https  = filter_input(INPUT_SERVER, "HTTPS");

        if(is_string($scheme)){
            return strtolower($scheme);
        }

        if(null !== $https){
            if(is_bool($https)){
                return $https ? "https" : "http";
            }

            return "off" === $https ? "http" : "https";
        }

        return "http";
    }

    /**
     * サーバー変数からホストとポートを解決する
     *
     * @return  string
     */
    private static function resolveAuthority(){
        $host   = filter_input(INPUT_SERVER, "HTTP_HOST");

        if(is_string($host)){
            return $host;
        }

        $port   = filter_input(INPUT_SERVER, "REQUEST_PORT", FILTER_VALIDATE_INT);
        $name   = filter_input(INPUT_SERVER, "SERVER_NAME");

        if(false === $port){
            throw new \RuntimeException();
        }

        if(is_string($name)){
            return $name . (null === $port ? "" : (":" . $port));
        }

        $addrv4 = filter_input(INPUT_SERVER, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        $addrv6 = filter_input(INPUT_SERVER, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

        if(is_string($addrv4)){
            return $addrv4 . (null === $port ? "" : (":" . $port));
        }

        if(is_string($addrv6)){
            return "[" . $addrv6. "]" . (null === $port ? "" : (":" . $port));
        }

        throw new \RuntimeException;
    }

    /**
     * サーバー変数からパスを解決する
     *
     * @return  string
     */
    private static function resolvePathAndQuery(){
        $path   = filter_input(INPUT_SERVER, "REQUEST_URI");

        if(is_string($path)){
            return $path;
        }

        return "/";
    }
}