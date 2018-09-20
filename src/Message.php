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

    const REGEX_NAME    = "/\A[0-9A-Z-!#$%&'*+.^_`|~]+\z/i";

    //  改行を含む値はRFC7230的には非推奨
    const REGEX_VALUE   = "/\A([\x21-\x7e]([\x20\x09]+[\x21-\x7e])?)*\z/";

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
    private $body;

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
     * Constructor
     *
     * @param   StreamInterface $body
     *  メッセージボディ
     * @param   string[]    $headers
     *  メッセージヘッダー
     * @param   string  $version
     *  メッセージプロトコルバージョン
     */
    public function __construct(
        StreamInterface $body = null,
        array $headers = [],
        string $version = static::DEFAULT_PROTOCOL_VERSION
    ){
        foreach($headers as $name => $values){
            $this->setHeader($name, $values);
        }

        $this->setBody($body ?? new Stream\MemoryStream());
        $this->setProtocolVersion($version);
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(){
        return $this->version;
    }

    /**
     *
     *
     * @param   string  $version
     *  Protocol version
     *
     * @return  $this
     *
     * @throws  \InvalidArgumentException
     */
    protected function setProtocolVersion($version){
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

        $this->version  = $version;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version){
        if($this->version === $version){
            return $this;
        }

        $clone  = clone $this;

        return $clone->setProtocolVersion($version);
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
        if(!$this->hasHeader($name)){
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

    protected function setHeader(string $name, array $values){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                ""
            );
        }

        if(1 !== preg_match(static::REGEX_HEADER_NAME, $name)){
            throw new \InvalidArgumentException(
                ""
            );
        }

        foreach($values as $index => $value){
            if(!is_string($value)){
                throw new \InvalidArgumentException(
                    ""
                );
            }

            if(1 !== preg_match(static::REGEX_HEADER_VALUE, $value)){
                throw new \InvalidArgumentException(
                    ""
                );
            }
        }

        $key                    = self::getHeaderKey($name);
        $this->headerKeys[$key] = $name;
        $this->headers[$name]   = array_values($values);

        return $this;
    }

    protected function addHeader($name, $values){
        if($this->hasHeader($name)){
            $key    = $this->headerKeys[self::getHeaderKey($name)];
            $values = array_merge($this->headers[$key], $values);
        }

        return $this->setHeader($name, (array)$values);
    }

    protected function removeHeader($name){
        if(is_string($name) && $this->hasHeader($name)){
            $key    = self::getHeaderKey($name);

            unset($this->headers[$this->headerKeys[$key]]);
            unset($this->headerKeys[$key]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value){
        if($this->hasHeader($name) && $this->getHeader($name) === $value){
            return $this;
        }

        $clone  = clone $this;

        return $clone->setHeader($name, (array)$value);
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value){
        if($this->hasHeader($name) && $this->getHeader($name) === $value){
            return $this;
        }

        $clone  = clone $this;

        return $clone->addHeader($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name){
        if(!$this->hasHeader($name)){
            return $this;
        }

        $clone  = clone $this;

        return $clone->removeHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(){
        return $this->body;
    }

    protected function setBody(StreamInterface $body){
        $this->body = $body;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body){
        if($this->body === $body){
            return $this;
        }

        $clone  = clone $this;

        return $clone->setBody($body);
    }
}