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
        "DELETE"    => true,
        "OPTIONS"   => true,
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
     * Constructor.
     *
     * @param string       $method
     * @param UriInterface $uri
     */
    public function __construct(string $method, UriInterface $uri){
        if(!array_key_exists($method, static::ALLOW_HTTP_METHODS)){
            throw new \InvalidArgumentException(
                "The HTTP request method {$method}"
                ." is not allowed. allow methods is ["
                . implode(", ", static::ALLOW_HTTP_METHODS)
                . "]."
            );
        }

        $this->method   = $method;
        $this->uri      = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(){
        return
            $this->uri->getPath()
            . ("" === $this->uri->getQuery() ? "" : "?" . $this->uri->getQuery())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget){
        if(!is_string($requestTarget)){
            throw new \InvalidArgumentException(
                "Argument must be of the type string, " . gettype($requestTarget) . " given."
            );
        }

        $parts      = explode("?", $requestTarget, 2);
        $parts[1]   = $parts[1] ?? "";

        if(
            $this->uri->getPath() === $parts[0]
            && $this->uri->getQuery() === $parts[1]
        ){
            return $this;
        }

        $clone      = clone $this;
        $clone->uri = $clone->uri
            ->withPath($parts[0])
            ->withQuery($parts[1])
        ;

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
            throw new \InvalidArgumentException(
                "Argument must be of the type string, " . gettype($method) . " given."
            );
        }

        if(!array_key_exists($method, static::ALLOW_HTTP_METHODS)){
            throw new \InvalidArgumentException(
                "The HTTP request method {$method}"
                ." is not allowed. allow methods is ["
                . implode(", ", static::ALLOW_HTTP_METHODS)
                . "]."
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
                "Second argument must be of the type bool, " . gettype($preserveHost) . " given."
            );
        }

        $resultInstance = $this;

        if($this->uri !== $uri){
            $resultInstance         = clone $this;
            $resultInstance->uri    = $uri;
        }

        if(!$preserveHost){
            if("" === $uri->getHost()){
                throw new \InvalidArgumentException(
                    "Host is not defined in URI instance,  host header can not be changed."
                );
            }

            $resultInstance = $resultInstance->withHeader(
                "Host",
                null === $uri->getPort()
                    ? $uri->getHost()
                    : ($uri->getHost() . ":" . $uri->getPort())
            );
        }

        return $resultInstance;
    }
}