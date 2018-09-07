<?php
/**
 * Created by PhpStorm.
 * User: mathias
 * Date: 07/09/18
 * Time: 10:23
 */

namespace Spatie\Varnish;


class VarnishResponse
{
    /**
     * Varnish return codes (from vcli.h)
     * https://github.com/varnishcache/varnish-cache/blob/master/include/vcli.h#L42.
     */
    const VARN_SYNTAX = 100;
    const VARN_UNKNOWN = 101;
    const VARN_UNIMPL = 102;
    const VARN_TOOFEW = 104;
    const VARN_TOOMANY = 105;
    const VARN_PARAM = 106;
    const VARN_AUTH = 107;
    const VARN_OK = 200;
    const VARN_TRUNCATED = 201;
    const VARN_CANT = 300;
    const VARN_COMMS = 400;
    const VARN_CLOSE = 500;

    /**
     * Varnish control command contains the status code and content length
     * e.g. 107 59
     */
    const CONTROL_COMMAND_REGEX = '/^(\d{3}) (\d+)/';

    /**
     * Authentication challenge length
     */
    const CHALLENGE_LENGTH = 32;

    /**
     * @var $code int: The varnish return code
     */
    private $code = null;

    /**
     * @var $length int: The length of the following content
     */
    private $length = null;

    /**
     * @var $content string: The actual content of the response
     */
    private $content = '';

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function appendContent(string $content)
    {
        $this->content .= $content;
    }

    /**
     * @return bool
     */
    public function isAuthRequest() {
        return $this->getCode() === VarnishResponse::VARN_AUTH;
    }

    /**
     * @return bool|string
     */
    public function getAuthChallenge() {
        return substr($this->content, 0, self::CHALLENGE_LENGTH);
    }

    /**
     * @param $chunk
     * @return bool
     */
    public function parseControlCommand($chunk) {
        // Varnish will output a code and a content length, followed by the actual content
        if (preg_match(self::CONTROL_COMMAND_REGEX, $chunk, $match)) {
            $this->setCode((int) $match[1]);
            $this->setLength((int) $match[2]);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function contentLengthReached() {
        return ! ($this->getLength() === null || strlen($this->getContent()) < $this->getLength());
    }
}