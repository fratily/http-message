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
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * Constructor.
     *
     * @param UriFactoryInterface $uriFactory
     */
    public function __construct(UriFactoryInterface $uriFactory){
        $this->uriFactory   = $uriFactory;
    }

    /**
     * Get uri factory.
     *
     * @return UriFactoryInterface
     */
    protected function getUriFactory(){
        return $this->uriFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface{
        if(!is_string($uri) && !is_subclass_of($uri, UriInterface::class)){
            $class  = UriInterface::class;
            throw new \InvalidArgumentException(
                "Second argument must be of the type string or {$class}, " . gettype($uri) . " given."
            );
        }

        return new Request(
            $method,
            is_string($uri) ? $this->getUriFactory()->createUri($uri) : $uri
        );
    }
}