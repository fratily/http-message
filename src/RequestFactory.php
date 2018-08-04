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

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 *
 */
class RequestFactory implements RequestFactoryInterface{

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     */
    public function createRequest(string $method, $uri): RequestInterface{
        if(is_string($uri)){
            $uri    = new Uri($uri);
        }else if(!$uri instanceof UriInterface){
            throw new \InvalidArgumentException();
        }

        return new Request($method, $uri);
    }
}