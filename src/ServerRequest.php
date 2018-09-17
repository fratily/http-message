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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 */
class ServerRequest extends Request implements ServerRequestInterface{

    /**
     * @var mixed[]
     */
    private $sereverParameters;

    /**
     * @var UploadedFileInterface[]
     */
    private $uploadedFiles;

    /**
     * @var mixed[]
     */
    private $cookies;

    /**
     * @var mixed[]
     */
    private $queries;

    /**
     * @var null|mixed[]
     */
    private $parsedBody;

    /**
     * @var mixed[]
     */
    private $attributes;

    public static function newInstance(
        string $method,
        UriInterface $uri,
        array $serverParameters = [],
        StreamInterface $body = null,
        string $headers = [],
        array $cookies = [],
        array $queries = [],
        array $uploadFiles = [],
        array $attributes = [],
        string $version = static::DEFAULT_PROTOCOL_VERSION
    ){
        $instance   = parent::newInstance($method, $uri, $body, $headers, $version)
            ->withCookieParams($cookies)
            ->withQueryParams($queries)
            ->withUploadedFiles($uploadFiles)
            ->withParsedBody($instance->getBody())
        ;

        $instance->serverParameters = $serverParameters;
        $instance->attributes       = $attributes;

        return $instance;
    }

    private static function validUploadedFiles(array $uploadedFiles, string $index = ""){
        foreach($uploadedFiles as $key => $file){
            $_index = "" === $index ? $key : ($index . "." . $key);

            if(is_array($file)){
                self::validUploadedFiles($file, $_index);
                continue;
            }

            if(!is_subclass_of($file, UploadedFileInterface::class)){
                $class  = UploadedFileInterface::class;

                throw new \InvalidArgumentException(
                    "Of the uploaded files, '{$_index}' is not an instance of"
                    . " the class implementing {$class}"
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams(){
        return $this->sereverParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(){
        return $this->cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies){
        // ミドルウェアがクッキーの値をパースして再設定する可能性がある
//        foreach($cookies as $key => $val){
//            if(!is_scalar($val)){
//                $type   = gettype($val);
//
//                throw new \InvalidArgumentException(
//                    "The cookie list must be an associative array with scalar"
//                    . " type values. The value of index {$key} is type {$type}."
//                );
//            }
//        }

        if($this->cookies === $cookies){
            return $this;
        }

        $clone  = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(){
        return $this->queries;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $queries){
        if($this->queries === $queries){
            return $this;
        }

        $clone  = clone $this;
        $clone->queries = $queries;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(){
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles){
        self::validUploadedFiles($uploadedFiles);

        if($this->uploadedFiles === $uploadedFiles){
            return $this;
        }

        $clone                  = clone $this;
        $clone->uploadedFiles   = $uploadedFiles;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody(){
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data){
        if(null !== $data && !is_array($data) && !is_object($data)){
            throw new \InvalidArgumentException(
                "Parsed body must be null, array or object."
            );
        }

        if($this->parsedBody === $data){
            return $this;
        }

        $clone              = clone $this;
        $clone->parsedBody  = $data;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(){
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     */
    public function getAttribute($name, $default = null){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "Attribute name must be a string"
            );
        }

        // ??を使うと属性値がnullかつデフォルト値が非nullの場合に正しく値が取得できない。
        return array_key_exists($name, $this->attributes)
            ? $this->attributes[$name]
            : $default
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     */
    public function withAttribute($name, $value){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "Attribute name must be a string"
            );
        }

        if(
            array_key_exists($name, $this->attributes)
            && $this->attributes[$name] === $value
        ){
            return $this;
        }

        $clone                      = clone $this;
        $clone->attributes[$name]   = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     */
    public function withoutAttribute($name){
        if(!is_string($name)){
            throw new \InvalidArgumentException(
                "Attribute name must be a string"
            );
        }

        if(!array_key_exists($name, $this->attributes)){
            return $this;
        }

        $clone  = clone $this;

        unset($clone->attributes[$name]);

        return $clone;
    }
}