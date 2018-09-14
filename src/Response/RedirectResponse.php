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
namespace Fratily\Http\Message\Response;

use Fratily\Http\Message\Response;
use Psr\Http\Message\UriInterface;

/**
 *
 */
class RedirectResponse extends Response{

    /**
     * Constructor
     *
     * @param   UriInterface    $uri
     *  リダイレクト先URIインスタンス
     * @param   int $code
     *  リダイレクトレスポンスステータス
     * @param   string[][]|string[] $headers
     *  レスポンスヘッダー
     * @param   bool $absolute
     *  LocationヘッダーのURIを絶対パスにするか
     */
    public function __construct(
        UriInterface $uri,
        int $code = 302,
        array $headers = [],
        bool $absolute = true
    ){
        if($absolute){
            if(
                null === $uri->getScheme()
                || null === $uri->getHost()
            ){
                throw new \InvalidArgumentException(
                    ""
                );
            }
        }

        if($absolute){
            $path   = (string)$uri;
        }else{
            $path   = $uri->getPath()
                . (null === $uri->getQuery() ? "" : "?" . $uri->getQuery())
                . (null === $uri->getFragment() ? "" : "#" . $uri->getFragment())
            ;
        }

        parent::__construct($code, array_merge($headers, ["Location" => $path]));
    }
}