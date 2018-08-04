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

    const REGEX = "(?<scheme>https?)://(?:(?<userinfo>(?:%[0-9a-f][0-9a-f]|[0-9"
        . "a-z-._~!$&'()*+,;=:])*)@)?(?<host>\[(?:::(?:(?:[0-9a-f]|[1-9a-f][0-9"
        . "a-f]{1,3})(?::(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,5})?|(?:[0-9a-f]"
        . "|[1-9a-f][0-9a-f]{1,3})(?:::(?:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?:"
        . ":(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,4})?|:(?:[0-9a-f]|[1-9a-f][0-"
        . "9a-f]{1,3})(?:::(?:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?::(?:[0-9a-f]"
        . "|[1-9a-f][0-9a-f]{1,3})){0,3})?|:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})("
        . "?:::(?:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?::(?:[0-9a-f]|[1-9a-f][0-"
        . "9a-f]{1,3})){0,2})?|:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?:::(?:(?:[0"
        . "-9a-f]|[1-9a-f][0-9a-f]{1,3})(?::(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3}))"
        . "?)?|:(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})(?:::(?:[0-9a-f]|[1-9a-f][0-9"
        . "a-f]{1,3})?|(?::(?:[0-9a-f]|[1-9a-f][0-9a-f]{1,3})){3})))))|v[0-9a-f"
        . "]\.(?:[0-9a-z-._~!$&'()*+,;=:])+)\]|(?:%[0-9a-f][0-9a-f]|[0-9a-z-._~"
        . "!$&'()*+,;=])+)(?::(?<port>[1-9][0-9]*))?(?<path>(?:/(?:%[0-9a-f][0-"
        . "9a-f]|[0-9a-z-._~!$&'()*+,;=:@])*)*)(?:\?(?<query>(?:%[0-9a-f][0-9a-"
        . "f]|[0-9a-z-._~!$&'()*+,;=:@/?[\]])*))?(?:#(?<fragment>(?:%[0-9a-f][0"
        . "-9a-f]|[0-9a-z-._~!$&'()*+,;=:@/?])*))?"
    ;

    /**
     * URL文字列を構造ごとに分割する
     *
     * @param   string  $uri
     *
     * @return  string[]|bool
     *      URLとして正しければ構造ごとに格納した配列を返す。空文字列や
     *      存在しない部分はインデックスされない。URLとして正しくなければ
     *      <b>FALSE</b>を返す。
     */
    public static function parseUri(string $uri){
        if(!(bool)preg_match("`\A" . self::REGEX . "\z`i", $uri, $m)){
            return false;
        }

        return array_filter($m, function($v, $k){
            return is_string($k) && $v !== "";
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ""): UriInterface{
        if($uri === ""){
            return new Uri();
        }

        if(($parts = self::parseUri($uri)) === false){
            throw new \InvalidArgumentException("Failed to parse URI.");
        }

        return new Uri(
            $parts["scheme"],
            $parts["userinfo"] ?? null,
            $parts["host"],
            $parts["port"] ?? null,
            $parts["path"] ?? null,
            $parts["query"] ?? null,
            $parts["fragment"] ?? null
        );
    }
}