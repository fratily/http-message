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
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

/**
 *
 */
class ServerRequestFactory implements ServerRequestFactoryInterface{

    /**
     * @var UriFactoryInterface
     */
    private $uriFactory         = null;

    /**
     * @var UploadedFileFactoryInterface
     */
    private $uploadFileFactory  = null;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory      = null;

    /**
     * URIファクトリを取得する
     *
     * @return  UriFactoryInterface
     */
    public function getUriFactory(){
        if(null === $this->uriFactory){
            $this->uriFactory   = new UriFactory();
        }

        return $this->uriFactory;
    }

    /**
     * URIファクトリを設定する
     *
     * @param   UriFactoryInterface $factory
     *  URIファクトリインスタンス
     *
     * @return  void
     */
    public function setUriFactory(UriFactoryInterface $factory){
        $this->uriFactory   = $factory;
    }

    /**
     * ストリームファクトリを取得する
     *
     * @return  StreamFactoryInterface
     */
    public function getStreamFactory(){
        if($this->streamFactory === null){
            $this->streamFactory    = new StreamFactory();
        }

        return $this->streamFactory;
    }

    /**
     * ストリームファクトリを設定する
     *
     * @param   StreamFactoryInterface  $factory
     *
     * @return  void
     */
    public function setStreamFactory(StreamFactoryInterface $factory){
        $this->streamFactory    = $factory;
    }

    /**
     * アップロードファイルファクトリを取得する
     *
     * @return  UploadedFileFactoryInterface
     */
    public function getUploadFileFactory(){
        if($this->uploadFileFactory === null){
            $this->uploadFileFactory    = new UploadedFileFactory();
        }

        return $this->uploadFileFactory;
    }

    /**
     * アップロードファイルファクトリを設定する
     *
     * @param   UploadedFileFactoryInterface    $factory
     *
     * @return  void
     */
    public function setUploadFileFactory(UploadedFileFactoryInterface $factory){
        $this->uploadFileFactory    = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     */
    public function createServerRequest(
        string $method,
        $uri,
        array $serverParams = []
    ): ServerRequestInterface{
        if(is_string($uri)){
            $uri    = $this->getUriFactory()->createUri($uri);
        }else if(!$uri instanceof UriInterface){
            throw new \InvalidArgumentException();
        }

        return new ServerRequest(
            $method,
            $uri,
            $serverParams,
            self::getUploadedFiles($_FILES),
            $_COOKIE,
            $_GET,
            self::getProtocolVersion($serverParams)
        );
    }

    /**
     *
     *
     * @param   mixed[] $files
     * @param   UploadedFileFactoryInterface    $factory
     *
     * @return  UploadedFileInterface
     *
     * @throws  \InvalidArgumentException
     */
    private static function getUploadedFiles(array $files){
        $return = [];

        foreach($files as $name => $value){
            if($value instanceof UploadedFileInterface){
                $return[$name]  = $value;
            }else if(is_array($value)){
                if(isset($value["error"]) && isset($value["tmp_name"])){
                    $return[$name]  = static::createUplodFile($value);
                }else{
                    $return[$name]  = static::getUploadedFiles($value);
                }
            }else{
                throw new \InvalidArgumentException();
            }
        }

        return $return;
    }

    /**
     *
     *
     * @param   mixed[] $value
     * @param   UploadedFileFactoryInterface    $factory
     *
     * @return  UploadedFileInterface
     */
    private static function createUplodFile(array $value){
        if(is_array($value["error"])){
            return self::createUploadNestFile($value, $factory);
        }

        return $this->getUploadFileFactory()->createUploadedFile(
            $this->getStreamFactory()->createStreamFromFile($value["tmp_name"]),
            $value["size"],
            $value["error"],
            $value["name"],
            $value["type"]
        );
    }

    /**
     *
     *
     * @param   mixed[] $files
     * @param   UploadedFileFactoryInterface    $factory
     *
     * @return  UploadedFileInterface[]
     */
    private static function createUploadNestFile(array $files){
        $return = [];

        foreach(array_keys($files["error"]) as $key){
            $info   = [
                "tmp_name"  => $files["tmp_name"][$key],
                "size"      => $files["size"][$key],
                "error"     => $files["error"][$key],
                "name"      => $files["name"][$key],
                "type"      => $files["type"][$key],
            ];

            $return[$key]   = self::createUplodFile($info);
        }

        return $return;
    }

    private static function getProtocolVersion($server = null){
        $server = $server ?? $_SERVER;

        if(!isset($server["SERVER_PROTOCOL"])){
            return "1.1";
        }else if(!(bool)preg_match(
                "`\AHTTP/(?<ver>[1-9][0-9]*(\.[1-9][0-9]*)?)\z`",
                $server["SERVER_PROTOCOL"], $m
            )
        ){
            throw new \InvalidArgumentException();
        }

        return $m["ver"];
    }
}