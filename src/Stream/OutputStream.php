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
namespace Fratily\Http\Message\Stream;

use Fratily\Http\Message\Stream;
use Fratily\Http\Message\Exception;

/**
 *
 */
class OutputStream extends Stream{

    /**
     * Constructor
     *
     * @throws  \RuntimeException
     */
    public function __construct(){
        if(($resource = fopen("php://output", "w")) === false){
            throw new \RuntimeException;
        }

        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(){
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  Exception\StreamUnavailableException
     */
    public function tell(){
        if(null == $this->resource){
            throw new Exception\StreamUnavailableException;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(){
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(){
        return false;
    }
}