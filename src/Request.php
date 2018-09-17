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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 *
 */
class Request extends Message implements RequestInterface{

    const ALLOW_HTTP_METHODS    = [
        "GET"       => true,
        "HEAD"      => true,
        "POST"      => true,
        "PUT"       => true,
        "PATCH"     => true,
        "DELETE"    => true
    ];

    /**
     * @var string
     */
    private $method;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * リクエストインスタンスを生成する
     *
     * @param   string  $method
     *  HTTPリクエストメソッド
     * @param   UriInterface    $uri
     *  リクエストURI
     * @param   StreamInterface $body
     *  メッセージボディ
     * @param   string[]    $headers
     *  メッセージヘッダー
     * @param   string  $version
     *  メッセージプロトコルバージョン
     *
     * @return  static
     */
    public static function newInstance(
        string $method,
        UriInterface $uri,
        StreamInterface $body = null,
        array $headers = [],
        string $version = static::DEFAULT_PROTOCOL_VERSION
    ){
        return parent::newInstance($body, $headers, $version)
            ->withMethod($method)
            ->withUri($uri, false)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(){
        return
            $this->uri->getPath()
            . (
                "" === $this->uri->getQuery() ? "" : "?{$this->uri->getQuery()}"
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget){
        if(!is_string($requestTarget)){
            throw new \InvalidArgumentException(
                "Request target path must be a string."
            );
        }

        $path   = $requestTarget;
        $query  = "";

        if(false !== ($pos = strpos($requestTarget, "?"))){
            $path   = substr($requestTarget, 0, $pos);
            $query  = substr($requestTarget, $pos + 1);
        }

        if($this->uri->getPath() === $path && $this->uri->getQuery() === $query){
            return $this;
        }

        $clone      = clone $this;
        $clone->uri = $clone->getUri()->withPath($query)->withQuery($query);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(){
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method){
        if(!is_string($method)){
            throw new \InvalidArgumentException("Method must be a string");
        }

        if(!array_key_exists($method, static::ALLOW_HTTP_METHODS)){
            $allow  = implode(", ", static::ALLOW_HTTP_METHODS);

            throw new \InvalidArgumentException(
                "Method 'A' can not be used,"
                . " because it is not included in the allowed method ({$allow})."
            );
        }

        if($this->method === $method){
            return $this;
        }

        $clone          = clone $this;
        $clone->method  = $method;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(){
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false){
        if(!is_bool($preserveHost)){
            throw new \InvalidArgumentException(
                "The host header preserve flag must be boolean."
            );
        }

        if($this->uri === $uri){
            return $this;
        }

        $clone      = clone $this;
        $clone->uri = $uri;

        if(!$preserveHost){
            if("" === $uri->getHost()){
                throw new \InvalidArgumentException(
                    "Host is not defined in URI instance,"
                    . " host header can not be changed."
                );
            }

            $clone  = $clone->withHeader(
                "Host",
                null === $uri->getPort()
                    ? $uri->getHost()
                    : ($uri->getHost() . ":" . $uri->getPort())
            );
        }

        return $clone;
    }
}