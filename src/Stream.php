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
     *
     * @throws  \InvalidArgumentException
     */
    public function __construct($resource){
        if(!is_resource($resource)){
            throw new \InvalidArgumentException();
        }

        if(get_resource_type($resource) !== "stream"){
            throw new \InvalidArgumentException();
        }

        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(){
        $return = "";
        $offset = null;

        try{
            if($this->isReadable()){
                if($this->isSeekable()){
                    $offset = $this->tell();
                    $this->rewind();
                }

                $return = $this->getContents();
            }
        }catch(Exception $e){

        }finally{
            if($offset !== null){
                $this->seek($offset, SEEK_SET);
            }
        }

        return $return;
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
        $return = $this->resource;
        $this->resource = null;

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(){
        if($this->resource === null){
            return null;
        }

        return fstat($this->resource)["size"] ?? null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     */
    public function tell(){
        if($this->resource === null){
            throw new Exception\StreamUnavailableException();
        }

        if(($point = ftell($this->resource)) === false){
            throw new \RuntimeException;
        }

        return $point;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(){
        if($this->resource === null){
            return true;
        }

        return feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(){
        if($this->resource !== null){
            return (bool)($this->getMetadata("seekable") ?? false);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnseekableException
     */
    public function seek($offset, $whence = SEEK_SET){
        if(!is_int($offset)){
            throw new \InvalidArgumentException();
        }

        if(!in_array($whence, [SEEK_SET, SEEK_CUR, SEEK_END])){
            throw new \InvalidArgumentException();
        }

        if($this->resource === null){
            throw new Exception\StreamUnavailableException();
        }

        if(!$this->isSeekable()){
            throw new Exception\StreamUnseekableException();
        }

        if(fseek($this->resource, $offset, $whence) !== 0){
            throw new \RuntimeException;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnseekableException
     */
    public function rewind(){
        return $this->seek(0, SEEK_SET);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(){
        if($this->resource !== null){
            $mode   = $this->getMetadata("mode");

            if($mode !== null){
                return strpos($mode, "x") !== false
                    || strpos($mode, "w") !== false
                    || strpos($mode, "a") !== false
                    || strpos($mode, "c") !== false
                    || strpos($mode, "+") !== false;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnwritableException
     */
    public function write($string){
        if(!is_scalar($string)){
            throw new \InvalidArgumentException();
        }

        if($this->resource === null){
            throw new Exception\StreamUnavailableException();
        }

        if(!$this->isWritable()){
            throw new Exception\StreamUnwritableException();
        }

        $bytes  = fwrite($this->resource, (string)$string);

        if($bytes === false){
            throw new \RuntimeException();
        }

        return $bytes;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(){
        if($this->resource !== null){
            $mode   = $this->getMetadata("mode");

            if($mode !== null){
                return strpos($mode, "r") !== false
                    || strpos($mode, "+") !== false;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnreadableException
     */
    public function read($length){
        if(!is_int($length)){
            throw new \InvalidArgumentException();
        }

        if($this->resource === null){
            throw new Exception\StreamUnavailableException();
        }

        if(!$this->isReadable()){
            throw new Exception\StreamUnreadableException();
        }

        $contents   = fread($this->resource, $length);

        if($contents === false){
            throw new \RuntimeException();
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     * @throws  Exception\StreamUnreadableException
     */
    public function getContents(){
        if($this->resource === null){
            throw new Exception\StreamUnavailableException;
        }

        if(!$this->isReadable()){
            throw new Exception\StreamUnreadableException;
        }

        $contents   = stream_get_contents($this->resource);

        if($contents === false){
            throw new \RuntimeException();
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     * @throws  Exception\StreamUnavailableException
     */
    public function getMetadata($key = null){
        if($key !== null && !is_string($key)){
            throw new \InvalidArgumentException();
        }

        if($this->resource === null){
            throw new Exception\StreamUnavailableException;
        }

        $meta   = stream_get_meta_data($this->resource);

        if($key !== null){
            $meta   = $meta[$key] ?? null;
        }

        return $meta;
    }
}