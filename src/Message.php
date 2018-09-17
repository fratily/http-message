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
    protected $headers    = [];

    /**
     * @var string[]
     */
    protected $headerKeys = [];

    /**
     * @var StreamInterface
     */
    protected $body;

    /**
     * @var string
     */
    protected $version;

    /**
     * メッセージインスタンスを生成する
     *
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
        StreamInterface $body = null,
        array $headers = [],
        string $version = static::DEFAULT_PROTOCOL_VERSION
    ){
        return (new static())
            ->withBody($body ?? new Stream\MemoryStream())
            ->withHeaders($headers)
            ->withProtocolVersion($version)
        ;
    }

    /**
     * ヘッダーネームが正しいか確認する
     *
     * @param   string  $name
     *
     * @return  bool
     */
    protected static function validHeaderName($name){
        static $cache   = [];

        if(!array_key_exists($name, $cache)){
            if(!is_string($name)){
                throw new \InvalidArgumentException(
                    "Header name must be a string."
                );
            }

            if(1 !== preg_match(static::REGEX_HEADER_NAME, $name)){
                throw new \InvalidArgumentException(
                    "'{$name}' is inappropriate as the header name."
                );
            }

            $cache[$name]   = true;
        }
    }

    /**
     * ヘッダー値もしくはそのリストが正しいか確認する
     *
     * @param   string[]|string $values
     *
     * @return  bool
     */
    protected static function validHeaderValue($values){
        static $cache   = [];

        $single = !is_array($values);

        foreach((array)$values as $index => $value){
            if(array_key_exists($value, $cache)){
                continue;
            }

            if(!is_string($value)){
                throw new \InvalidArgumentException(
                    "Header value must be a string or an array of strings."
                );
            }

            if(1 !== preg_match(static::REGEX_HEADER_VALUE, $value)){
                $info   = $single ? "" : "(index: {$index})";

                throw new \InvalidArgumentException(
                    "'{$value}'{$info} is inappropriate as a header value."
                );
            }

            $cache[$value]  = true;
        }
    }

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
     */
    protected function __construct(){}

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
    public function hasHeader($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                ""
            );
        }

        return array_key_exists(self::getHeaderKey($name), $this->headerKeys);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                ""
            );
        }

        return $this->headers[$this->headerKeys[self::getHeaderKey($name)]];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                ""
            );
        }

        return implode(",", $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value){
        static::validHeaderName($name);
        static::validHeaderValue($value);

        if($this->hasHeader($name) && $this->getHeader($name) === $value){
            return $this;
        }

        $clone  = clone $this;
        $clone  = $clone->withoutHeader($name);

        $clone->headerKeys[self::getHeaderKey($name)]   = $name;
        $clone->headers[$name]                          = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value){
        static::validHeaderName($name);
        static::validHeaderValue($value);

        if(!$this->hasHeader($name)){
            return $this->withHeader($name, $value);
        }

        $clone  = clone $this;
        $key    = self::getHeaderKey($name);

        $clone->headers[$clone->headerKeys[$key]]   = array_merge(
            $clone->headers[$clone->headerKeys[$key]],
            array_values((array)$value)
        );

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name){
        static::validHeaderName($name);

        if(!$this->hasHeader($name)){
            return $this;
        }

        $clone  = clone $this;
        $key    = self::getHeaderKey($name);

        unset($clone->headers[$clone->headerKeys[$key]]);
        unset($clone->headerKeys[$key]);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(){
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
    }
}