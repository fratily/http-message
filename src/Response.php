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

use Psr\Http\Message\ResponseInterface;

/**
 *
 */
class Response extends Message implements ResponseInterface{

    /**
     * @var int
     */
    private $code;

    /**
     * @var string|null
     */
    private $reasonPhrase;

    /**
     * Constructor.
     *
     * @param int    $statusCode
     * @param string $reasonPhrase
     */
    public function __construct(int $statusCode = 200, string $reasonPhrase = null){
        $this->code         = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
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
        return
            $this->reasonPhrase
            ?? Status\HttpStatus::PHRASES[$this->code]
            ?? "Undefined"
        ;
    }

    /**
     * {@inheritoc}
     */
    public function withStatus($code, $reasonPhrase = ""){
        if(!is_int($code)){
            throw new \InvalidArgumentException(
                "First argument must be of the type int, " . gettype($code) . " given."
            );
        }

        if(!is_string($reasonPhrase)){
            throw new \InvalidArgumentException(
                "Second argument must be of the type string, " . gettype($reasonPhrase) . " given."
            );
        }

        if(100 > $code || 599 < $code){
            throw new \InvalidArgumentException(
                "HTTP status code is between 100 and 599, {$code} given."
            );
        }

        $reasonPhrase   = "" === $reasonPhrase ? null : $reasonPhrase;

        if($this->code === $code && $this->reasonPhrase === $reasonPhrase){
            return $this;
        }

        $clone                  = clone $this;
        $clone->code            = $code;
        $clone->reasonPhrase    = $reasonPhrase;

        return $clone;
    }
}