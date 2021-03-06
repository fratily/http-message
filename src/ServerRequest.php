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
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 */
class ServerRequest extends Request implements ServerRequestInterface{

    /**
     * @var mixed[]
     */
    private $serverParameters;

    /**
     * @var UploadedFileInterface[]
     */
    private $uploadedFiles;

    /**
     * @var null|mixed[]
     */
    private $cookies    = null;

    /**
     * @var null|mixed[]
     */
    private $queries    = null;

    /**
     * @var null|mixed[]|object
     */
    private $parsedBody = null;

    /**
     * @var mixed[]
     */
    private $attributes = [];

    /**
     * Constructor.
     *
     * @param string       $method
     * @param UriInterface $uri
     * @param mixed[]|null $serverParameters
     */
    public function __construct(
        string $method,
        UriInterface $uri,
        array $serverParameters = null
    ){
        parent::__construct($method, $uri);

        $this->serverParameters = $serverParameters ?? $_SERVER;
    }

    /**
     * Validate uploaded files.
     *
     * @param array  $uploadedFiles
     * @param string $index
     *
     * @return void
     */
    protected static function validUploadedFiles(array $uploadedFiles, string $index = ""){
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
        return $this->serverParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(){
        if(null === $this->cookies){
            $this->cookies  = $_COOKIE;
        }

        return $this->cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies){
        if($this->cookies === $cookies){
            return $this;
        }

        $clone          = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(){
        if(null === $this->queries){
            $this->queries  = $_GET;
        }

        return $this->queries;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $queries){
        if($this->queries === $queries){
            return $this;
        }

        $clone          = clone $this;
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
        static::validUploadedFiles($uploadedFiles);

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
        if(null === $this->parsedBody && "GET" !== $this->getMethod()){
            $this->parsedBody   = $_POST;
        }

        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data){
        if(null !== $data && !is_array($data) && !is_object($data)){
            throw new \InvalidArgumentException(
                "Argument must be of the type null or array or object, " . gettype($data) . " given."
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
                "First argument must be of the type string, " . gettype($name) . " given."
            );
        }

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
                "First argument must be of the type string, " . gettype($name) . " given."
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
                "Argument must be of the type string, " . gettype($name) . " given."
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