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
 * @todo    ヘッダーのHostは必ず1行目になければならない（外部に任せるかこちらでやってしまうか）
 */
class Message implements MessageInterface{

    const DEFAULT_PROTOCOL_VERSION = "1.1";

    const PROTOCOL_VERSION  = [
        "1.0"   => true,
        "1.1"   => true,
        "2.0"   => true,
    ];

    const REGEX_HEADER_NAME     = "/\A[0-9A-Z-!#$%&'*+.^_`|~]+\z/i";

    //  改行を含む値はRFC7230的には非推奨
    const REGEX_HEADER_VALUE    = "/\A([\x21-\x7e]([\x20\x09]+[\x21-\x7e])?)*\z/";

    /**
     * @var string[][]
     */
    private $headers    = [];

    /**
     * @var string[]
     */
    private $headerKeys = [];

    /**
     * @var StreamInterface
     */
    private $body       = null;

    /**
     * @var string
     */
    private $version;

    /**
     * ヘッダーキーを取得する
     *
     * @param   string  $name
     *  ヘッダー名
     *
     * @return  string
     */
    private static function getHeaderKey(string $name){
        return strtolower($name);
    }

    /**
     * ヘッダーの名前と値のバリデーションを行う
     *
     * @param   mixed   $name
     *  ヘッダー名
     * @param   mixed   $value
     *  ヘッダーの値
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     */
    private static function withHeaderValidation($name, $value){
        if(!is_string($name) || 1 !== preg_match(static::REGEX_HEADER_NAME, $name)){
            throw new \InvalidArgumentException(
                ""
            );
        }

        if(!is_string($value) && !is_array($value)){
            throw new \InvalidArgumentException(
                ""
            );
        }

        $value  = is_string($value) ? [$value] : $value;

        foreach($value as $_value){
            if(1 !== preg_match(static::REGEX_HEADER_VALUE, $_value)){
                throw new \InvalidArgumentException(
                    ""
                );
            }
        }
    }

    public function __construct(){
        $this->version  = static::DEFAULT_PROTOCOL_VERSION;
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
                ""
            );
        }

        if(!array_key_exists($version, self::PROTOCOL_VERSION)){
            throw new \InvalidArgumentException(
                ""
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
        if(!is_string($name) || !$this->hasHeader($name)){
            return [];
        }

        return $this->headers[$this->headerKeys[self::getHeaderKey($name)]];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name){
        return implode(",", $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name){
        if(!is_string($name)){
            return false;
        }

        return array_key_exists(self::getHeaderKey($name), $this->headerKeys);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value){
        if($this->hasHeader($name) && $this->getHeader($name) === $value){
            return $this;
        }

        static::withHeaderValidation($name, $value);

        $clone  = clone $this;
        $key    = self::getHeaderKey($name);
        $value  = is_string($value) ? [$value] : $value;

        $clone->headerKeys[$key] = $name;
        $clone->headers[$name]   = array_values($value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value){
        if(!$this->hasHeader($name)){
            return $this->withHeader($name, $value);
        }

        static::withHeaderValidation($name, $value);

        $clone  = clone $this;
        $key    = self::getHeaderKey($name);

        $clone->headers[$this->headerKeys[$key]]    = array_merge(
            $clone->headers[$this->headerKeys[$key]],
            array_values(is_string($value) ? [$value] : $value)
        );

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name){
        if(!$this->hasHeader($name)){
            return $this;
        }

        static::withHeaderValidation($name, []);

        $clone  = clone $this;
        $key    = self::getHeaderKey($name);

        unset($clone->headers[$this->headerKeys[$key]]);
        unset($clone->headerKeys[$key]);

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

        $clone          = clone $this;
        $clone->body    = $body;

        return $clone;
    }
}