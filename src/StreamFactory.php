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
use Psr\Http\Message\StreamFactoryInterface;

/**
 *
 */
class StreamFactory implements StreamFactoryInterface{

    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $prefix;

    /**
     * Constructor
     *
     * @param   string|null  $dir
     *  Path of temporary directory.
     * @param   string  $prefix
     *  Temporary file name prefix.
     */
    public function __construct(string $dir = null, string $prefix = ""){
        if(
            null !== $dir
            && (!is_dir($dir) || !is_writable($dir) || !is_readable($dir))
        ){
            throw new \InvalidArgumentException(
                "The temporary file storage directory must be a directory"
                . " that can be read and written."
            );
        }

        $this->dir      = realpath($dir ?? sys_get_temp_dir());
        $this->prefix   = $prefix;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\CreateTemporaryFileException
     * @throws  Exception\FileWriteException
     */
    public function createStream(string $content = ""): StreamInterface{
        if(false === ($path = tempnam($this->dir, $this->prefix))){
            throw new Exception\CreateTemporaryFileException(
                "Attempted to create a temporary file in {$this->dir} but it failed."
            );
        }

        if(false === file_put_contents($path, $content)){
            throw new Exception\FileWriteException("Failed to write to {$path}");
        }

        return $this->createStreamFromFile($path, "r");
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\FileOpenException
     */
    public function createStreamFromFile(string $filename, string $mode = "r"): StreamInterface{
        if(false === ($resource = fopen($filename, $mode))){
            throw new Exception\FileOpenException(
                "{$filename} could not be opened in {$mode} mode"
            );
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface{
        if(!is_resource($resource)){
            throw new \InvalidArgumentException();
        }

        return new Stream($resource);
    }
}