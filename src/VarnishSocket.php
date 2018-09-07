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
     * The socket used to connect to Varnish and a timeout in seconds.
     */
    protected $varnishSocket = null;
    protected $socketTimeout = 10;

    /**
     * Limits for reading and writing to and from the socket.
     */
    const CHUNK_SIZE = 1024;
    const WRITE_MAX_SIZE = 16 * self::CHUNK_SIZE;

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
        self::socketConnect($host, $port);
        self::authenticate($secret);
        return $this->isConnected();
    }

    /**
     * @param $host
     * @param $port
     * @throws \Exception
     */
    private function socketConnect($host, $port) {
        $this->varnishSocket = fsockopen(
            $host, $port,
            $errno, $errstr,
            $this->socketTimeout
        );

        if (! self::isConnected()) {
            throw new \Exception(sprintf(
                'Failed to connect to Varnish on %s:%d, error %d: %s',
                $host, $port, $errno, $errstr
            ));
        }

        // Set stream options
        stream_set_blocking($this->varnishSocket, true);
        stream_set_timeout($this->varnishSocket, $this->socketTimeout);
    }

    /**
     * @param $secret
     * @throws \Exception
     */
    private function authenticate($secret) {
        // Read first response from socket
        $response = $this->read();

        // Authenticate using secret if authentication is required
        // https://varnish-cache.org/docs/trunk/reference/varnish-cli.html#authentication-with-s
        if ($response->isAuthRequest()) {
            // Generate the authentication token based on the challenge and secret
            $token = $this->calculateAuthToken($response->getAuthChallenge(), $secret);

            // Authenticate using token
            $response = $this->command(
                sprintf('auth %s', $token)
            );

            if ($response->getCode() !== VarnishResponse::VARN_OK) {
                throw new \Exception(sprintf(
                    'Varnish admin authentication failed: %s',
                    $response->getContent()
                ));
            }
        }
    }

    /**
     * Check if we're connected to Varnish socket.
     *
     * @return bool
     */
    public function isConnected()
    {
        if (is_resource($this->varnishSocket)) {
            $meta = stream_get_meta_data($this->varnishSocket);
            return ! ($meta['eof'] || $meta['timed_out']);
        }
        return false;
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
     * @return VarnishResponse
     *
     * @throws \Exception
     */
    private function read()
    {
        if (!$this->isConnected()) {
            throw new \Exception('Cannot read from Varnish socket because it\'s not connected');
        }

        // Read data from socket
        $response = self::readChunks();

        // Failed to get code from socket
        if ($response->getCode() === null) {
            throw new \Exception(
                'Failed to read response code from Varnish socket'
            );
        }

        return $response;
    }

    /**
     * @param VarnishResponse $response
     * @return VarnishResponse
     * @throws \Exception
     */
    private function readChunks(VarnishResponse $response = null) {
        if ($response === null) {
            $response = new VarnishResponse();
        }

        while (self::continueReading($response)) {
            $chunk = self::readSingleChunk();

            // Given content length
            if ($response->hasLength()) {
                $response->appendContent($chunk);
                continue;
            }

            // No content length given, expecting code + content length response
            if ($response->parseControlCommand($chunk)) {
                // Read actual content with given length
                return self::readChunks($response);
            }
        }

        return $response;
    }

    /**
     * Determine whether we should continue to read from the Varnish socket
     * - There is still data on the socket to read
     * - We have not reached the given content length
     *
     * @param VarnishResponse $response
     * @return bool
     */
    private function continueReading(VarnishResponse $response) {
        return ! feof($this->varnishSocket) && ! $response->finishedReading();
    }

    /**
     * Check if Varnish socket has timed out
     *
     * @throws \Exception
     */
    private function checkSocketTimeout() {
        $meta = stream_get_meta_data($this->varnishSocket);
        if ($meta['timed_out']) {
            throw new \Exception(
                'Varnish socket connection timed out'
            );
        }
    }

    /**
     * Read a single chunk from the Varnish socket
     *
     * @return bool|string
     * @throws \Exception
     */
    private function readSingleChunk() {
        $chunk = fgets($this->varnishSocket, self::CHUNK_SIZE);

        // Check for socket timeout when an empty chunk is returned
        if (empty($chunk)) {
            self::checkSocketTimeout();
        }

        return $chunk;
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
        if (!$this->isConnected()) {
            throw new \Exception('Cannot write to Varnish socket because it\'s not connected');
        }
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
     * @return VarnishResponse
     *
     * @throws \Exception
     */
    public function command($cmd, $ok = VarnishResponse::VARN_OK)
    {
        $response = $this->write($cmd)->read();
        if ($response->getCode() !== $ok) {
            throw new \Exception(
                sprintf(
                    "Command '%s' responded %d: '%s'",
                    $cmd, $response->getCode(), $response->getContent()
                ),
                $response->getCode()
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
        if (self::isConnected()) {
            fclose($this->varnishSocket);
        }
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
            $this->command('quit', VarnishResponse::VARN_CLOSE);
        } finally {
            $this->close();
        }
    }
}
