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
use Psr\Http\Message\StreamInterface;

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
     * レスポンスインスタンスを生成する
     *
     * @param   int $code
     *  HTTPレスポンスステータスコード
     * @param   StreamInterface $body
     *  メッセージボディ
     * @param   string[]    $headers
     *  メッセージヘッダー
     * @param   string  $version
     *  メッセージプロトコルバージョン
     *
     * @return  static
     */
    public static function newInstance(
        int $code = 200,
        StreamInterface $body = null,
        string $headers = [],
        string $version = static::DEFAULT_PROTOCOL_VERSION
    ){
        return parent::newInstance($body, $headers, $version)
            ->withStatusCode($code)
        ;
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
                "The HTTP status code must be integer."
            );
        }

        if(!is_string($reasonPhrase)){
            throw new \InvalidArgumentException(
                "The HTTP status reason phrase must be string."
            );
        }

        if(!array_key_exists($code, Status\HttpStatus::STATUS_CODE)){
            throw new \InvalidArgumentException(
                "Status code {$code} can not be used."
            );
        }

        $reasonPhrase   = "" === $reasonPhrase ? null : $reasonPhrase;

        if($this->code === $code && $this->reasonPhrase === $reasonPhrase){
            return $this;
        }

        $clone  = clone $this;

        if($clone->code !== $code){
            $clone->code    = $code;
        }

        if($clone->reasonPhrase !== $reasonPhrase){
            $clone->reasonPhrase    = $reasonPhrase;
        }

        return $clone;
    }
}