<?php
/**
 * FratilyPHP Http Message
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Http\Message\Status;

/**
 *
 */
class MethodNotAllowed extends HttpStatus{

    const STATUS_CODE   = 405;

    /**
     * @var string[]
     */
    private $allowed;

    /**
     * 許可されているHTTPメソッドリストを取得する
     *
     * @return  string[]
     */
    public function getAllowed(){
        return $this->allowed ?? ["GET", "HEAD"];
    }

    /**
     * 許可されているHTTPメソッドリストを設定する
     *
     * @param   string[]    $allowed
     *
     * @return  void
     */
    public function setAllowed(array $allowed){
        $this->allowed  = array_unique(
            array_map(
                "strtoupper",
                array_filter(
                    $allowed,
                    "is_string"
                )
            )
        );

        if(empty($this->allowed)){
            $this->allowed  = null;
        }
    }
}