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

use Psr\Http\Message\{
    ResponseInterface,
    StreamInterface
};

/**
 *
 */
class Response extends Message implements ResponseInterface{

    /**
     * HTTP status code
     *
     * @var int
     */
    private $code;

    /**
     * Constructor
     *
     * @param   int $code
     * @param   mixed[] $headers
     * @param   StreamInterface $body
     *
     * @throws  \InvalidArgumentException
     */
    public function __construct(
        int $code = 200,
        array $headers = [],
        StreamInterface $body = null
    ){
        if(!Status\HttpStatus::isCode($code)){
            throw new \InvalidArgumentException();
        }

        if($body !== null && (!$body->isWritable() || !$body->isReadable())){
            throw new \InvalidArgumentException();
        }

        $this->code = $code;

        parent::__construct($headers, $body ?? new Stream\MemoryStream());
    }

    /**
     * {@inheritoc}
     */
    public function getStatusCode(){
        return $this->code;
    }

    /**
     * {@inheritoc}
     */
    public function getReasonPhrase(){
        return Status\HttpStatus::PHRASES[$this->code];
    }

    /**
     * {@inheritoc}
     */
    public function withStatus($code, $reasonPhrase = ""){
        if(!is_int($code) || !Status\HttpStatus::isCode($code)){
            throw new \InvalidArgumentException();
        }

        if(!is_string($reasonPhrase)){
            throw new \InvalidArgumentException();
        }

        if($this->code === $code){
            $return = $this;
        }else{
            $return         = clone $this;
            $return->code   = $code;
        }

        return $return;
    }
}