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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 *
 */
class Message implements MessageInterface{

    const DEFAULT_PROTOCOL_VERSION = "1.1";

    const PROTOCOL_VERSION  = [
        "1.0"   => true,
        "1.1"   => true,
        "2.0"   => true,
    ];

    const REGEX_HEADER_NAME     = "/\A[0-9A-Z-!#$%&'*+.^_`|~]+\z/i";

    // Value of containing line breaks are deprecated in RFC 7230.
    const REGEX_HEADER_VALUE    = "/\A([\x21-\x7e]([\x20\x09]+[\x21-\x7e])?)*\z/";

    /**
     * @var string
     */
    private $version    = self::DEFAULT_PROTOCOL_VERSION;

    /**
     * @var string[][]
     */
    private $headers    = [];

    /**
     * @var StreamInterface
     */
    private $body       = null;

    /**
     * Get normalized header key.
     *
     * `content-type` to `Content-Type`.
     *
     * @param string $name
     *
     * @return string
     */
    protected static function getNormalizedHeaderKey(string $name){
        static $convertCache = [];

        if(!isset($convertCache[$name])){
            $convertCache[$name]    = ucfirst(ucwords($name, "-"));
        }

        return $convertCache[$name];
    }

    /**
     * Validate header name.
     *
     * @param string $name
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected static function validateHeaderName(string $name){
        static $validCache = [];

        if(!isset($validCache[$name])){
            if(1 !== preg_match(static::REGEX_HEADER_NAME, $name)){
                throw new \InvalidArgumentException(
                    "Invalid Header name given."
                );
            }
        }

        $validCache[$name]  = true;
    }

    /**
     * Validate header value.
     *
     * @param array $values
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected static function validateHeaderValue(array $values){
        static $validCache = [];

        foreach($values as $index => $value){
            if(!is_string($value)){
                throw new \InvalidArgumentException(
                    "Header value must be of the type string array, "
                    . gettype($value)
                    . " given at index of {$index}."
                );
            }

            if(!isset($validCache[$value])){
                if(1 !== preg_match(static::REGEX_HEADER_VALUE, $value)){
                    throw new \InvalidArgumentException(
                        "Invalid Header value given at index of {$index}."
                    );
                }
            }

            $validCache[$value]  = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(){
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version){
        if(!is_string($version)){
            throw new \InvalidArgumentException(
                "Argument must be of the type string, " . gettype($version) . " given."
            );
        }

        if(!array_key_exists($version, self::PROTOCOL_VERSION)){
            throw new \InvalidArgumentException(
                "The protocol version "
                . $version
                ." is not allowed. allow version is ["
                . implode(", ", static::PROTOCOL_VERSION)
                . "]."
            );
        }

        if($this->version === $version){
            return $this;
        }

        $clone          = clone $this;
        $clone->version = $version;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(){
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "Argument must be of the type string, " . gettype($name) . " given."
            );
        }

        if(!$this->hasHeader($name)){
            return [];
        }

        return $this->headers[static::getNormalizedHeaderKey($name)];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "Argument must be of the type string, " . gettype($name) . " given."
            );
        }

        return implode(",", $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "Argument must be of the type string, " . gettype($name) . " given."
            );
        }

        return array_key_exists(static::getNormalizedHeaderKey($name), $this->headers);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "First argument must be of the type string, " . gettype($name) . " given."
            );
        }

        if(!is_string($value) && !is_array($value)){
            throw new \InvalidArgumentException(
                "Second argument must be of the type string or array, " . gettype($value) . " given."
            );
        }

        $value  = (array)$value;

        if($this->hasHeader($name) && $this->getHeader($name) === $value){
            return $this;
        }

        static::validateHeaderName($name);
        static::validateHeaderValue($value);

        $clone  = clone $this;

        $clone->headers[static::getNormalizedHeaderKey($name)]  = array_values($value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "First argument must be of the type string, " . gettype($name) . " given."
            );
        }

        if(!is_string($value) && !is_array($value)){
            throw new \InvalidArgumentException(
                "Second argument must be of the type string or array, " . gettype($value) . " given."
            );
        }

        $value  = (array)$value;

        if($this->hasHeader($name) && $this->getHeader($name) === $value){
            return $this;
        }

        static::validateHeaderName($name);
        static::validateHeaderValue($value);

        $clone  = clone $this;

        $clone->headers[static::getNormalizedHeaderKey($name)]  = array_merge(
            $this->getHeader($name),
            array_values($value)
        );

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "Argument must be of the type string, " . gettype($name) . " given."
            );
        }

        if(!$this->hasHeader($name)){
            return $this;
        }

        $clone  = clone $this;

        unset($clone->headers[static::getNormalizedHeaderKey($name)]);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(){
        if(null === $this->body){
            $this->body = new Stream\MemoryStream();
        }

        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body){
        if($this->body === $body){
            return $this;
        }

        $clone  = clone $this;

        $clone->body    = $body;

        return $clone;
    }
}