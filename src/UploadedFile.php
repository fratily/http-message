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

    const ERROR_MAP = [
        UPLOAD_ERR_OK           => "The file uploaded with success.",
        UPLOAD_ERR_INI_SIZE     => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
        UPLOAD_ERR_FORM_SIZE    => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
        UPLOAD_ERR_PARTIAL      => "The uploaded file was only partially uploaded.",
        UPLOAD_ERR_NO_FILE      => "No file was uploaded.",
        UPLOAD_ERR_NO_TMP_DIR   => "Missing a temporary folder.",
        UPLOAD_ERR_CANT_WRITE   => "Failed to write file to disk.",
        UPLOAD_ERR_EXTENSION    => "A PHP extension stopped the file upload.",
    ];

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
        if("plainfile" !== $stream->getMetadata("wrapper_type")){
            throw new \InvalidArgumentException();
        }

        if(!is_uploaded_file($stream->getMetadata("uri"))){
            throw new \InvalidArgumentException;
        }

        if(!array_key_exists($error, static::ERROR_MAP)){
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
        if(UPLOAD_ERR_OK !== $this->error){
            throw new Exception\UploadErrorException(
                static::ERROR_MAP[$this->error]
            );
        }

        if($this->moved){
            throw new Exception\UploadFileIsMovedException(
                "The uploaded file has already been moved."
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
                static::ERROR_MAP[$this->error]
            );
        }

        if($this->moved){
            throw new Exception\UploadFileIsMovedException(
                "The uploaded file has already been moved."
            );
        }

        if(is_dir($targetPath)){
            throw new Exception\UploadFileMoveException(
                "Failed to move the file because the target file path is a directory."
            );
        }

        if(false === ($target = fopen($targetPath, "w"))){
            throw new Exception\UploadFileMoveException(
                "Failed to get resource of target file."
            );
        }

        $this->stream->rewind();

        if(stream_copy_to_stream($this->stream->detach(), $target) === false){
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