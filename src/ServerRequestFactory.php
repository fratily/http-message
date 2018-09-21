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
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 *
 */
class ServerRequestFactory implements ServerRequestFactoryInterface{

    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

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
     *  URIファクトリ
     *
     * @return  $this
     */
    public function setUriFactory(UriFactoryInterface $factory){
        $this->uriFactory   = $factory;

        return $this;
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
        }

        if(!is_subclass_of($uri, UriInterface::class)){
            $class  = UriInterface::class;

            throw new \InvalidArgumentException(
                "URI must be a string or an instance of a class"
                . " that implements {$class}."
            );
        }

        return new ServerRequest($method, $uri, $serverParams);
    }
}