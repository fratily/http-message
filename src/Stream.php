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

use Psr\Http\Message\StreamInterface;

/**
 *
 */
class Stream implements StreamInterface{

    const EXCEPTION_MSG_UNAVAILABLE = "The stream is already closed.";
    const EXCEPTION_MSG_UNREADABLE  = "Attempt to read to unreadable resource.";
    const EXCEPTION_MSG_UNSEEKABLE  = "Attempt to seek to unseekable resource.";
    const EXCEPTION_MSG_UNWRITABLE  = "Attempt to write to writable resource";

    /**
     * @var resource|null
     * @todo    このクラスを継承したクラスにもこれへのアクセスを許可する
     *          ただし、型はきちんと守らせる必要がある
     */
    private $resource;

    /**
     * Constructor
     *
     * @param   resource    $resource
     *  ストリームリソース
     */
    public function __construct($resource){
        if(
            !is_resource($resource)
            || "stream" !== get_resource_type($resource)
        ){
            throw new \InvalidArgumentException(
                "It must be a stream type resource."
            );
        }

        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(){
        return $this->getContents();
    }

    /**
     * リソースを登録する
     *
     * @param   resource    $resource
     *  ストリームリソース
     *
     * @return  void
     */
    public function attach($resource){
        if(
            !is_resource($resource)
            || "stream" !== get_resource_type($resource)
        ){
            throw new \InvalidArgumentException(
                "It must be a stream type resource."
            );
        }

        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function close(){
        $resource   = $this->detach();

        if(is_resource($resource)){
            fclose($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach(){
        $return         = $this->resource;
        $this->resource = null;

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(){
        if(null === $this->resource){
            return null;
        }

        return fstat($this->resource)["size"] ?? null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  \RuntimeException
     */
    public function tell(){
        if(null === $this->resource){
            throw new Exception\StreamUnavailableException(
                static::EXCEPTION_MSG_UNAVAILABLE
            );
        }

        if(false === ($point = ftell($this->resource))){
            throw new \RuntimeException("Failed to get file pointer position.");
        }

        return $point;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(){
        if(null === $this->resource){
            return true;
        }

        return feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(){
        if(null === $this->resource){
            return false;
        }

        return (bool)($this->getMetadata("seekable") ?? false);
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnseekableException
     * @throws  \RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET){
        if(!is_int($offset)){
            throw new \InvalidArgumentException();
        }

        if(!in_array($whence, [SEEK_SET, SEEK_CUR, SEEK_END])){
            throw new \InvalidArgumentException();
        }

        if(null === $this->resource){
            throw new Exception\StreamUnavailableException(
                static::EXCEPTION_MSG_UNAVAILABLE
            );
        }

        if(!$this->isSeekable()){
            throw new Exception\StreamUnseekableException(
                static::EXCEPTION_MSG_UNSEEKABLE
            );
        }

        if(0 !== fseek($this->resource, $offset, $whence)){
            throw new \RuntimeException("Failed to seek file pointer.");
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnseekableException
     * @throws  \RuntimeException
     */
    public function rewind(){
        return $this->seek(0, SEEK_SET);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(){
        if(null === $this->resource){
            return false;
        }

        if(null === ($mode = $this->getMetadata("mode"))){
            return false;
        }

        return
            strpos($mode, "x") !== false
            || strpos($mode, "w") !== false
            || strpos($mode, "a") !== false
            || strpos($mode, "c") !== false
            || strpos($mode, "+") !== false
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnwritableException
     * @throws  \RuntimeException
     */
    public function write($string){
        if(!is_string($string)){
            throw new \InvalidArgumentException();
        }

        if(null === $this->resource){
            throw new Exception\StreamUnavailableException(
                static::EXCEPTION_MSG_UNAVAILABLE
            );
        }

        if(!$this->isWritable()){
            throw new Exception\StreamUnwritableException(
                static::EXCEPTION_MSG_UNWRITABLE
            );
        }

        if(false === ($bytes = fwrite($this->resource, $string))){
            throw new \RuntimeException("Failed to write to the stream.");
        }

        return $bytes;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(){
        if(null === $this->resource){
            return false;
        }

        if(null === ($mode = $this->getMetadata("mode"))){
            return false;
        }

        return
            strpos($mode, "r") !== false
            || strpos($mode, "+") !== false
        ;

    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnreadableException
     * @throws  \RuntimeException
     */
    public function read($length){
        if(!is_int($length)){
            throw new \InvalidArgumentException();
        }

        if(null === $this->resource){
            throw new Exception\StreamUnavailableException(
                static::EXCEPTION_MSG_UNAVAILABLE
            );
        }

        if(!$this->isReadable()){
            throw new Exception\StreamUnreadableException(
                static::EXCEPTION_MSG_UNREADABLE
            );
        }

        if(false === ($contents = fread($this->resource, $length))){
            throw new \RuntimeException("Failed to read stream.");
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnreadableException
     * @throws  \RuntimeException
     */
    public function getContents(){
        if(null === $this->resource){
            throw new Exception\StreamUnavailableException(
                static::EXCEPTION_MSG_UNAVAILABLE
            );
        }

        if(!$this->isReadable()){
            throw new Exception\StreamUnreadableException(
                static::EXCEPTION_MSG_UNREADABLE
            );
        }

        if(false === ($contents = stream_get_contents($this->resource))){
            throw new \RuntimeException("Failed to read stream.");
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     */
    public function getMetadata($key = null){
        if($key !== null && !is_string($key)){
            throw new \InvalidArgumentException();
        }

        if(null === $this->resource){
            throw new Exception\StreamUnavailableException(
                static::EXCEPTION_MSG_UNAVAILABLE
            );
        }

        $meta   = stream_get_meta_data($this->resource);

        if(null !== $key){
            return $meta[$key] ?? null;
        }

        return $meta;
    }
}