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

/**
 *
 */
class Uri implements UriInterface{

    const SCHEME    = 1;
    const USERINFO  = 2;
    const HOST      = 3;
    const PORT      = 4;
    const PATH      = 5;
    const QUERY     = 6;
    const FRAGMENT  = 7;

    const SCHEME_PORT_MAP   = [
        "http"      => 80,
        "https"     => 443
    ];

    const URLENCODE_RFC3986 = "rfc3986";

    const URLENCODE_FORM    = "application/x-www-form-urlencoded";

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $userinfo;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $fragment;

    /**
     * バリデーション
     *
     * @param   int $parts
     *  バリデーションするパーツ
     * @param   string  $text
     *  対象文字列
     *
     * @return  bool
     *
     * @throws  \InvalidArgumentException
     */
    protected static function validate(int $parts, string $text = null){
        switch($parts){
            case self::SCHEME:
                return $text === null
                    || array_key_exists($text, self::SCHEME_PORT_MAP)
                ;

            case self::USERINFO:
                return $text === null
                    || (bool)preg_match(
                        "`\A(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:])*\z`i",
                        $text
                    )
                ;

            case self::HOST:
                return $text === null
                    || (bool)preg_match(
                        "`\A(\[(::(([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(:([0-9a-f]|"
                        . "[1-9a-f][0-9a-f]{1,3})){0,5})?|([0-9a-f]|[1-9a-f][0-"
                        . "9a-f]{1,3})(::(([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(:([0"
                        . "-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,4})?|:([0-9a-f]|[1-"
                        . "9a-f][0-9a-f]{1,3})(::(([0-9a-f]|[1-9a-f][0-9a-f]{1,"
                        . "3})(:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,3})?|:([0-9"
                        . "a-f]|[1-9a-f][0-9a-f]{1,3})(::(([0-9a-f]|[1-9a-f][0-"
                        . "9a-f]{1,3})(:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,2})"
                        . "?|:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(::(([0-9a-f]|[1-"
                        . "9a-f][0-9a-f]{1,3})(:([0-9a-f]|[1-9a-f][0-9a-f]{1,3}"
                        . "))?)?|:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(::([0-9a-f]|"
                        . "[1-9a-f][0-9a-f]{1,3})?|(:([0-9a-f]|[1-9a-f][0-9a-f]"
                        . "{1,3})){3})))))|v[0-9a-f]\.([0-9a-z-._~!$&'()*+,;=:]"
                        . ")+)\]|(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=])+)"
                        . "\z`i",
                        $text
                    )
                ;

            case self::PORT:
                return $text === null
                    || (bool)preg_match("`\A[1-9][0-9]*\z`", $text)
                ;

            case self::PATH:
                return $text === null
                    || (bool)preg_match(
                        "`\A(/(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@])*)*\z`i",
                        $text
                    )
                ;

            case self::QUERY:
                return $text === null
                    || (bool)preg_match(
                        "`\A(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@/?[\]])*\z`i",
                        $text
                    )
                ;

            case self::FRAGMENT:
                return $text === null
                    || (bool)preg_match(
                        "`\A(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@/?])*\z`i",
                        $text
                    )
                ;
        }

        throw new \InvalidArgumentException("Undefine parts.");
    }

    /**
     * ポートがスキームのデフォルトポートか判定する
     *
     * @param   string  $scheme
     * @param   int $port
     *
     * @return  bool
     */
    public static function isStandardPort(string $scheme, int $port){
        if(!isset(static::SCHEME_PORT_MAP[strtolower($scheme)])){
            return false;
        }

        return static::SCHEME_PORT_MAP[strtolower($scheme)] === $port;
    }

    /**
     * URLエンコードを行う
     *
     * @param   string  $str
     * @param   mixed   $mode
     *      URLエンコードのタイプ
     *
     * @return  string
     *
     * @throws  \InvalidArgumentException
     */
    public static function urlEncode(string $str, $mode = self::URLENCODE_RFC3986){
        switch($mode){
            case self::URLENCODE_RFC3986:
                return rawurlencode($str);

            case self::URLENCODE_FORM:
                return urlencode($str);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Constructor
     *
     * @param   string  $scheme
     *  scheme
     * @param   string  $userinfo
     *  user info
     * @param   string  $host
     *  host
     * @param   int $port
     *  port
     * @param   string  $path
     *  path
     * @param   string  $query
     *  query
     * @param   string  $fragment
     *  fragment
     *
     * @throws  \InvalidArgumentException
     */
    public function __construct(
        string $scheme = null,
        string $userinfo = null,
        string $host = null,
        int $port = null,
        string $path = null,
        string $query = null,
        string $fragment = null
    ){
        if(!self::validate(self::SCHEME, $scheme)){
            throw new \InvalidArgumentException("Invalid scheme.");
        }

        if(!self::validate(self::USERINFO, $userinfo)){
            throw new \InvalidArgumentException("Invalid userinfo.");
        }

        if(!self::validate(self::HOST, $host)){
            throw new \InvalidArgumentException("Invalid host.");
        }

        if(!self::validate(self::PORT, $port)){
            throw new \InvalidArgumentException("Invalid port.");
        }

        if(!self::validate(self::PATH, $path)){
            throw new \InvalidArgumentException("Invalid path.");
        }

        if(!self::validate(self::QUERY, $query)){
            throw new \InvalidArgumentException("Invalid query.");
        }

        if(!self::validate(self::FRAGMENT, $fragment)){
            throw new \InvalidArgumentException("Invalid fragment.");
        }

        $this->scheme   = $scheme;
        $this->userinfo = $userinfo;
        $this->host     = $host;
        $this->port     = $port;
        $this->path     = $path === "" ? null : $path;
        $this->query    = $query === "" ? null : $query;
        $this->fragment = $fragment === "" ? null : $fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(){
        $authority  = $this->getAuthority();

        return ($this->scheme !== null ?$this->scheme . ":" : "")
            . ($authority !== "" ? "//{$authority}" : "")
            . $this->getPath()
            . ($this->query !== null ? "?" . $this->query : "")
            . ($this->fragment !== null ? "#" . $this->fragment : "");
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(){
        return $this->scheme ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(){
        $userinfo   = $this->getUserInfo();
        $host       = $this->getHost();
        $port       = $this->getPort();

        return ($userinfo !== "" ? "{$userinfo}@" : "") . $host
            . ($port !== null ? ":{$port}" : "");
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(){
        return $this->userinfo ?? "";
    }

    /**
     *
     * @return  string
     */
    public function getUser(){
        if($this->userinfo === null
            || ($pos = strpos($this->userinfo ?? "", ":")) === false
        ){
            return $this->userinfo ?? "";
        }

        return substr($this->userinfo, 0, $pos);
    }

    /**
     *
     * @return  string
     */
    public function getPassword(){
        if($this->userinfo === null
            || ($pos = strpos($this->userinfo ?? "", ":")) === false
        ){
            return "";
        }

        return substr($this->userinfo, $pos + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(){
        return $this->host ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(){
        if($this->port === null){
            return null;
        }

        return static::isStandardPort($this->getScheme(), $this->port)
            ? null : $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(){
        return $this->path ?? "/";
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(){
        return $this->query ?? "";
    }

    /**
     * パースしたクエリ配列を返す
     *
     * @return  mixed[]
     */
    public function getParsedQuery(){
        mb_parse_str($this->query ?? "", $return);
        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(){
        return $this->fragment ?? "";
    }


    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme){
        if(!is_string($scheme)){
            throw new InvalidArgumentException();
        }

        if(!self::validate(self::SCHEME, $scheme)){
            throw new \InvalidArgumentException("Invalid scheme.");
        }

        if($scheme === ""){
            $scheme = null;
        }

        if($scheme === $this->scheme){
            $return = $this;
        }else{
            $return = clone $this;
            $return->scheme = $scheme;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     */
    public function withUserInfo($user, $password = null){
        if(!is_string($user)){
            throw new InvalidArgumentException();
        }

        if($password !== null && !is_string($password)){
            throw new InvalidArgumentException();
        }

        if($user === ""){
            $userinfo   = null;
        }else{
            $userinfo   = self::urlEncode($user, self::URLENCODE_RFC3986);

            if($password !== null){
                $userinfo   .= ":" . self::urlEncode($password, self::URLENCODE_RFC3986);
            }

            if(!self::validate(self::USERINFO, $userinfo)){
                throw new \InvalidArgumentException("Invalid userinfo.");
            }
        }

        if($userinfo === $this->userinfo){
            $return = $this;
        }else{
            $return = clone $this;
            $return->userinfo   = $userinfo;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host){
        if(!is_string($host)){
            throw new \InvalidArgumentException();
        }

        if(!self::validate(self::HOST, $host)){
            throw new \InvalidArgumentException("Invalid host.");
        }

        if($host === $this->host){
            $return = $this;
        }else{
            $return = clone $this;
            $return->host   = $host;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port){
        if($port !== null && !is_int($port)){
            throw new \InvalidArgumentException();
        }

        if(!self::validate(self::PORT, $port)){
            throw new \InvalidArgumentException("Invalid port.");
        }

        if($port === $this->port){
            $return = $this;
        }else{
            $return = clone $this;
            $return->port   = $port;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path){
        if(!is_string($path)){
            throw new \InvalidArgumentException();
        }

        if(!self::validate(self::PATH, $path)){
            throw new \InvalidArgumentException("Invalid path.");
        }

        if($path === $this->path){
            $return = $this;
        }else{
            $return = clone $this;
            $return->path   = $path;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query){
        if(!is_string($query)){
            throw new \InvalidArgumentException();
        }

        if(!self::validate(self::QUERY, $query)){
            throw new \InvalidArgumentException("Invalid query.");
        }

        if($query === $this->query){
            $return = $this;
        }else{
            $return = clone $this;
            $return->query  = $query;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     *
     * @throws  \InvalidArgumentException
     */
    public function withFragment($fragment){
        if(!is_string($fragment)){
            throw new \InvalidArgumentException();
        }

        if(!self::validate(self::FRAGMENT, $fragment)){
            throw new \InvalidArgumentException("Invalid fragment.");
        }

        if($fragment === $this->fragment){
            $return = $this;
        }else{
            $return = clone $this;
            $return->fragment   = $fragment;
        }

        return $return;
    }
}