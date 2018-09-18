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

    /**
     * TODO: 様々なスキームを登録する
     */
    const DEFAULT_PORT  = [
        "http"  => 80,
        "https" => 443,
    ];

    const REGEX_SCHEME  = "`\A[a-z][0-9a-z-+.]*\z`i";

    const REGEX_USERINFO    = "`\A(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:])*"
        . "\z`i"
    ;

    const REGEX_HOST    = "`\A(\[(::(([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(:([0-9a-f"
        . "]|[1-9a-f][0-9a-f]{1,3})){0,5})?|([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(::"
        . "(([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(:([0-9a-f]|[1-9a-f][0-9a-f]{1,3}))"
        . "{0,4})?|:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(::(([0-9a-f]|[1-9a-f][0-9a"
        . "-f]{1,3})(:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})){0,3})?|:([0-9a-f]|[1-9a"
        . "-f][0-9a-f]{1,3})(::(([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(:([0-9a-f]|[1-"
        . "9a-f][0-9a-f]{1,3})){0,2})?|:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(::(([0"
        . "-9a-f]|[1-9a-f][0-9a-f]{1,3})(:([0-9a-f]|[1-9a-f][0-9a-f]{1,3}))?)?|"
        . ":([0-9a-f]|[1-9a-f][0-9a-f]{1,3})(::([0-9a-f]|[1-9a-f][0-9a-f]{1,3})"
        . "?|(:([0-9a-f]|[1-9a-f][0-9a-f]{1,3})){3})))))|v[0-9a-f]\.([0-9a-z-._"
        . "~!$&'()*+,;=:])+)\]|(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=])+)\z`"
        . "i"
    ;

    const REGEX_PATH    = "`\A(/(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@])*)"
        . "*\z`i"
    ;

    const REGEX_QUERY   = "`\A(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@/?[\]]"
        . ")*\z`i"
    ;

    const REGEX_FRAGMENT    = "`\A(%[0-9a-f][0-9a-f]|[0-9a-z-._~!$&'()*+,;=:@/?"
        . "])*\z`i"
    ;

    const URLENCODE_RFC3986 = "rfc3986";

    const URLENCODE_FORM    = "application/x-www-form-urlencoded";

    /**
     * @var string
     */
    private $scheme     = "";

    /**
     * @var string
     */
    private $userinfo   = "";

    /**
     * @var string
     */
    private $host       = "";

    /**
     * @var int|null
     */
    private $port       = null;

    /**
     * @var string
     */
    private $path       = "";

    /**
     * @var string
     */
    private $query      = "";

    /**
     * @var string
     */
    private $fragment   = "";

    /**
     * URIインスタンスを生成する
     *
     * @param   string  $scheme
     *  スキーム名
     * @param   string  $userinfo
     *  認証情報
     * @param   string  $host
     *  ホスト名
     * @param   int $port
     *  ポート番号
     * @param   string  $path
     *  パス
     * @param   string  $query
     *  クエリ
     * @param   string  $fragment
     *  フラグメント
     *
     * @return  static
     */
    public static function newInstance(
        string $scheme = "",
        string $userinfo = "",
        string $host = "",
        int $port = null,
        string $path = "",
        string $query = "",
        string $fragment = ""
    ){
        $userinfo   = explode(":", $userinfo, 2);

        return (new static())
            ->withScheme($scheme)
            ->withUserInfo($userinfo[0], $userinfo[1] ?? null)
            ->withHost($host)
            ->withPort($port)
            ->withPath($path)
            ->withQuery($query)
            ->withFragment($fragment)
        ;
    }

    /**
     * ポートがスキームのデフォルトポートか判定する
     *
     * @param   string  $scheme
     *  スキーム文字列
     * @param   int $port
     *  ポート番号
     *
     * @return  bool
     */
    public static function isDefaultPort(string $scheme, int $port){
        $scheme = strtolower($scheme);

        return
            array_key_exists($scheme, static::DEFAULT_PORT)
            && static::DEFAULT_PORT[$scheme] === $port
        ;
    }

    /**
     * Constructor
     */
    protected function __construct(){}

    /**
     * {@inheritdoc}
     */
    public function __toString(){
        return
            ("" === $this->getScheme() ? "" : $this->getScheme() . "://")
            . $this->getAuthority()
            . $this->getPath()
            . ("" === $this->getQuery() ? "" : "?" . $this->getQuery())
            . ("" === $this->getFragment() ? "" : "#" . $this->getFragment())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(){
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme){
        if(!is_string($scheme)){
            throw new InvalidArgumentException();
        }

        if("" !== $scheme && 1 !== preg_match(static::REGEX_SCHEME, $scheme)){
            throw new \InvalidArgumentException();
        }

        if($this->scheme === $scheme){
            return $this;
        }

        $clone          = clone $this;
        $clone->scheme  = $scheme;

        return $clone;
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
        return $this->userinfo;
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

        if(null !== $password && !is_string($password)){
            throw new InvalidArgumentException();
        }

        $userinfo   = implode(
            ":",
            [
                $user,
                "" === $user ? null : $password
            ]
        );

        if(1 !== preg_match(self::REGEX_USERINFO, $user)){
            throw new \InvalidArgumentException();
        }

        if($this->userinfo == $userinfo){
            return $this;
        }

        $clone              = clone $this;
        $clone->userinfo    = $userinfo;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(){
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host){
        if(!is_string($host)){
            throw new \InvalidArgumentException();
        }

        if("" !== $host && 1 !== preg_match(static::REGEX_HOST, $host)){
            throw new \InvalidArgumentException();
        }

        if($this->host === $host){
            return $this;
        }

        $clone          = clone $this;
        $clone->host    = $host;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(){
        if(null === $this->port || "" === $this->getHost()){
            return null;
        }

        return static::isDefaultPort($this->getScheme(), $this->port)
            ? null
            : $this->port
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port){
        if(null !== $port && !is_int($port)){
            throw new \InvalidArgumentException();
        }

        if(null !== $port && (1 > $port || 65535 < $port)){
            throw new \InvalidArgumentException();
        }

        if($this->port === $port){
            return $this;
        }

        $clone          = clone $this;
        $clone->port    = $port;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(){
        return ("/" === substr($this->path, 0, 1) ? "" : "/") . $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path){
        if(!is_string($path)){
            throw new \InvalidArgumentException();
        }

        if("" !== $path && 1 !== preg_match(static::REGEX_PATH, $path)){
            throw new \InvalidArgumentException();
        }

        if($this->path === $path){
            return $this;
        }

        $clone          = clone $this;
        $clone->path    = $path;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(){
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query){
        if(!is_string($query)){
            throw new \InvalidArgumentException();
        }

        if("" !== $query && 1 !== preg_match(static::REGEX_QUERY, $query)){
            throw new \InvalidArgumentException();
        }

        if($this->query === $query){
            return $this;
        }

        $clone          = clone $this;
        $clone->query   = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(){
        return $this->fragment;
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

        if("" !== $fragment && 1 !== preg_match(static::REGEX_FRAGMENT, $fragment)){
            throw new \InvalidArgumentException();
        }

        if($this->fragment === $fragment){
            return $this;
        }

        $clone              = clone $this;
        $clone->fragment    = $fragment;

        return $clone;
    }
}