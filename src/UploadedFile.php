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

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

/**
 *
 */
class UploadedFile implements UploadedFileInterface{

    /**
     * @var StreamInterface|null
     */
    private $stream;

    /**
     * @var int
     */
    private $size;

    /**
     * @var mixed
     */
    private $error;

    /**
     * @var string
     */
    private $clientFilename;

    /**
     * @var string
     */
    private $clientMediaType;

    /**
     * @var bool
     */
    private $moved  = false;

    /**
     * Constructor
     *
     * @param   string|resource|StreamInterface  $file
     * @param   int $size
     * @param   int $error
     * @param   string  $clientFilename
     * @param   string  $clientMediaType
     *
     * @throws  \InvalidArgumentException
     */
    public function __construct(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ){
        if($stream->getMetadata("wrapper_type") !== "plainfile"){
            throw new \InvalidArgumentException();
        }

        if(!is_uploaded_file($stream->getMetadata("uri"))){
            throw new \InvalidArgumentException;
        }

        if(!array_key_exists($error, Exception\UploadErrorException::ERROR_MAP)){
            throw new \InvalidArgumentException();
        }

        $this->stream           = $stream;
        $this->size             = $size;
        $this->error            = $error;
        $this->clientFilename   = $clientFilename;
        $this->clientMediaType  = $clientMediaType;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\UploadedFileException
     */
    public function getStream(){
        if($this->error !== UPLOAD_ERR_OK){
            throw new Exception\UploadErrorException(
                Exception\UploadErrorException::ERROR_MAP[$this->error]
            );
        }

        if($this->moved){
            throw new Exception\UploadFileIsMovedException(
                ""
            );
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath){
        if(!is_string($targetPath)){
            throw new \InvalidArgumentException();
        }

        if($this->error !== UPLOAD_ERR_OK){
            throw new Exception\UploadErrorException(
                Exception\UploadErrorException::ERROR_MAP[$this->error]
            );
        }

        if($this->moved){
            throw new Exception\UploadFileIsMovedException(
                ""
            );
        }

        if(($fp = fopen($targetPath, "w")) === false){
            throw new \RuntimeException;
        }

        $this->stream->rewind();

        if(stream_copy_to_stream($this->stream->detach(), $fp) === false){
            throw new \RuntimeException;
        }

        $this->moved    = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(){
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(){
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename(){
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(){
        return $this->clientMediaType;
    }
}