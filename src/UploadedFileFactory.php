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
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 *
 */
class UploadedFileFactory implements UploadedFileFactoryInterface{

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * Constructor.
     *
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(StreamFactoryInterface $streamFactory){
        $this->streamFactory    = $streamFactory;
    }

    /**
     * Get stream factory.
     *
     * @return StreamFactoryInterface
     */
    protected function getStreamFactory(): StreamFactoryInterface{
        return $this->streamFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface{
        return new UploadedFile(
            $stream,
            $size,
            $error,
            $clientFilename,
            $clientMediaType
        );
    }

    /**
     * Create a new uploaded files.
     *
     * @param mixed[] $files
     *
     * @return UploadedFileInterface[]
     *
     * @throws \InvalidArgumentException
     */
    public function createUploadedFiles(array $files){
        $instances = [];

        foreach($files as $name => $value){
            if($value instanceof UploadedFileInterface){
                $instances[$name]  = $value;
                continue;
            }

            if(!is_array($value)){
                $class  = UploadedFileInterface::class;

                throw new \InvalidArgumentException(
                    "The file list must be an associative array whose value is"
                    . " an instance of a class implementing {$class} or an array"
                    . " having file information."
                    . " But the value of index A does not apply to them."
                );
            }

            $instances[$name]  = isset($value["error"]) && isset($value["tmp_name"])
                ? $this->createUploadedFileWithNest($value)
                : $this->createUploadedFiles($value)
            ;
        }

        return $instances;
    }

    /**
     *
     *
     * @param mixed[] $value
     *
     * @return UploadedFileInterface|UploadedFileInterface[]
     */
    private function createUploadedFileWithNest(array $value){
        if(is_array($value["error"])){
            return $this->createNestUploadedFile($value);
        }

        return $this->createUploadedFile(
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
     * @param mixed[] $files
     *
     * @return UploadedFileInterface[]
     */
    private function createNestUploadedFile(array $files){
        $result = [];

        foreach(array_keys($files["error"]) as $key){
            $result[$key]   = $this->createUploadedFileWithNest(
                [
                    "tmp_name"  => $files["tmp_name"][$key],
                    "size"      => $files["size"][$key],
                    "error"     => $files["error"][$key],
                    "name"      => $files["name"][$key],
                    "type"      => $files["type"][$key],
                ]
            );
        }

        return $result;
    }
}