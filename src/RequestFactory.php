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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 *
 */
class RequestFactory implements RequestFactoryInterface{

    /**
     * @var UriFactoryInterface|null
     */
    private $uriFactory;

    /**
     * Constructor
     *
     * @param   UriFactoryInterface $uriFactory
     *  URIファクトリー
     */
    public function __construct(UriFactoryInterface $uriFactory = null){
        $this->uriFactory   = $uriFactory;
    }

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
     * URIファクトリを登録する
     *
     * @param   UriFactoryInterface $uriFactory
     *  URIファクトリ
     *
     * @return  $this;
     */
    public function setUriFactory(UriFactoryInterface $uriFactory){
        $this->uriFactory   = $uriFactory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface{
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

        return Request::newInstance($method, $uri);
    }
}