<?php

/**
 * Based on the Varnish Admin Socket class used in the Terpentine extension for Magento.
 * @link https://github.com/nexcess/magento-turpentine
 *
 * This was in turn based on Tim Whitlock's VarnishAdminSocket.php from php-varnish
 * @link https://github.com/timwhitlock/php-varnish
 *
 * Pieces from both resources above were used to fit our needs.
 */

/**
 * Nexcess.net Turpentine Extension for Magento
 * Copyright (C) 2012  Nexcess.net L.L.C.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Copyright (c) 2010 Tim Whitlock.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Spatie\Varnish;

class VarnishSocket
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
     * Authentication challenge length
     */
    const VARN_CHALLENGE_LENGTH = 32;


    /**
     * Varnish control command contains the status code and content length
     */
    const VARN_CONTROL_COMMAND_REGEX = '/^(\d{3}) (\d+)/';

    /**
     * The socket used to connect to Varnish and a timeout in seconds.
     */
    protected $varnishSocket = null;
    protected $socketTimeout = 10;

    /**
     * Limits for reading and writing to and from the socket.
     */
    const READ_CHUNK_SIZE = 1024;
    const WRITE_MAX_SIZE = 16 * self::READ_CHUNK_SIZE;

    /**
     * Connect to the Varnish socket and authenticate when needed.
     * @param string $host
     * @param int $port
     * @param string $secret
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function connect($host, $port, $secret = '')
    {
        // Open socket connection
        $this->varnishSocket = fsockopen(
            $host, $port,
            $errno, $errstr,
            $this->socketTimeout
        );

        if (! is_resource($this->varnishSocket)) {
            throw new \Exception(sprintf(
                'Failed to connect to Varnish on %s:%d, error %d: %s',
                $host, $port, $errno, $errstr
            ));
        }

        // Set stream options
        stream_set_blocking($this->varnishSocket, true);
        stream_set_timeout($this->varnishSocket, $this->socketTimeout);

        // Read first data from socket
        $data = $this->read();

        // Authenticate using secret if authentication is required
        // https://varnish-cache.org/docs/trunk/reference/varnish-cli.html#authentication-with-s
        if ($data['code'] === self::VARN_AUTH) {
            // The challenge is a random 32-character string
            $challenge = substr($data['content'], 0, self::VARN_CHALLENGE_LENGTH);

            // Generate the authentication token based on the challenge and secret
            $token = $this->calculateAuthToken($challenge, $secret);

            // Authenticate using token
            $data = $this->command(sprintf('auth %s', $token));

            if ($data['code'] !== self::VARN_OK) {
                throw new \Exception(sprintf(
                    'Varnish admin authentication failed: %s',
                    $data['content']
                ));
            }
        }

        return $this->isConnected();
    }

    /**
     * Check if we're connected to Varnish socket.
     *
     * @return bool
     */
    public function isConnected()
    {
        return ! is_null($this->varnishSocket);
    }

    /**
     * @param $challenge
     * @param $secret
     * @return string
     */
    private function calculateAuthToken($challenge, $secret) {
        // Ensure challenge ends with a newline
        $challenge = $this->ensureNewline($challenge);
        return hash('sha256',
            sprintf('%s%s%s',
                $challenge,
                $secret,
                $challenge
            ));
    }

    /**
     * @param $data
     * @return string
     */
    private function ensureNewline($data) {
        if (! preg_match('/\n$/', $data)) {
            $data .= "\n";
        }
        return $data;
    }

    /**
     * @return array|bool|string
     *
     * @throws \Exception
     */
    private function read()
    {
        $code = null;
        $len = -1;

        $callback = function($data) {
            return preg_match(self::VARN_CONTROL_COMMAND_REGEX, $data);
        };
        $response = $this->readChunks(null, $callback);
        // Varnish will output a code and a content length, followed by the actual content
        if (preg_match(self::VARN_CONTROL_COMMAND_REGEX, $response, $match)) {
            $code = (int) $match[1];
            $len = (int) $match[2];
        }

        // Failed to get code from socket
        if ($code === null) {
            throw new \Exception(
                'Failed to read response code from Varnish socket'
            );
        } else {
            $response = [
                'code' => $code,
                'content' => $this->readChunks($len),
            ];

            return $response;
        }
    }

    /**
     * Read content with length $length. When no length is given,
     * we set the length to 1 so that a single chunk is read.
     *
     * @param integer $length
     * @return string
     *
     * @throws \Exception
     */
    private function readChunks($length = null) {
        $response = '';
        while (! feof($this->varnishSocket)) {
            // Read chunk from socket and append to response
            $chunk = $this->readSingleChunk();

            // Check for empty response and timeout
            if (empty($chunk)) {
                $meta = stream_get_meta_data($this->varnishSocket);
                if ($meta['timed_out']) {
                    throw new \Exception(
                        'Varnish socket connection timed out'
                    );
                }
            }
            $response .= $chunk;

            if (($length === null && preg_match(self::VARN_CONTROL_COMMAND_REGEX, $response)) ||
                ($length !== null && strlen($response) >= $length)) {
                break;
            }
        }

        var_dump($response);
        return $response;
    }

    /**
     * @return bool|string
     */
    private function readSingleChunk() {
        return fgets($this->varnishSocket, self::READ_CHUNK_SIZE);
    }

    /**
     * Write data to the socket input stream.
     *
     * @param string $data
     *
     * @return VarnishSocket
     *
     * @throws \Exception
     */
    private function write($data)
    {
        echo "Writing $data \n";
        $data = $this->ensureNewline($data);
        if (strlen($data) >= self::WRITE_MAX_SIZE) {
            throw new \Exception(sprintf(
                'Data to write to Varnish socket is too large (max %d chars)',
                self::WRITE_MAX_SIZE
            ));
        }

        // Write data to socket
        $bytes = fwrite($this->varnishSocket, $data);
        if ($bytes !== strlen($data)) {
            throw new \Exception('Failed to write to Varnish socket');
        }

        return $this;
    }

    /**
     * Write a command to the socket with a trailing line break and get response straight away.
     *
     * @param string $cmd
     * @param int $ok
     *
     * @return array
     *
     * @throws \Exception
     */
    public function command($cmd, $ok = self::VARN_OK)
    {
        $response = $this->write($cmd)->read();
        if ($response['code'] !== $ok) {
            throw new \Exception(
                sprintf(
                    "Command '%s' responded %d: '%s'",
                    $cmd, $response['code'], $response['content']
                ),
                $response['code']
            );
        }

        return $response;
    }

    /**
     * Brutal close, doesn't send quit command to varnishadm.
     *
     * @return void
     */
    public function close()
    {
        is_resource($this->varnishSocket) && fclose($this->varnishSocket);
        $this->varnishSocket = null;
    }

    /**
     * Graceful close, sends quit command.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function quit()
    {
        try {
            $this->command('quit', self::VARN_CLOSE);
        } finally {
            $this->close();
        }
    }
}
