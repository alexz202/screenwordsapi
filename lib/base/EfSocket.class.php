<?php
class EfSocket {
    protected $host;
    protected $port;
    protected $timeout;
    protected $socket;

    /**
     * Construction, initialzie socket connection settings.
     * @param string $servers serialized array of settings
     * @param int $timeout seconds
     * @return void
     */
    public function __construct($servers, $timeout = 0.5) {
        // initialize socket connection settings
        $server = $this->randomReadHost($servers);
        $this->host = $server['name'];
        $this->port = $server['port'];
        $this->timeout = $timeout;
        // register shutdown function to release file resource
        register_shutdown_function(array($this, 'close'));
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    // API FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Send message.
     * @param string $message
     * @return void
     */
    public function send($message) {
        $this->validate($message);
        $this->open();
        $this->write($message);
        $this->close();
    }

    /**
     * Exchange message.
     * @param string $message
     * @return string $response
     */
    public function exchange($message) {
        $response = '';

        $this->validate($message);
        $this->open();
        $this->write($message);
        $response = $this->read();
        $this->close();

        return $response;
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    // BASE FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Open socket connection or return existing connection.
     * @return resource $socket
     */
    protected function open() {
        if (!$this->socket) {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            if (!$this->socket) {
                throw new OssException("Ef Socket;socket open failed");
            }
        }
        return $this->socket;
    }

    /**
     * Write data into socket.
     * @param string $data
     * @return boolean
     */
    protected function write($data) {
        if (fwrite($this->socket, $data)) {
            return TRUE;
        } else {
            echo $data;
        }
    }

    /**
     * Read data from socket.
     * @return string
     */
    protected function read() {
        return fgets($this->socket);
    }

    /**
     * Close this socket.
     * @return boolean
     */
    public function close() {
        if ($this->socket
            && is_resource($this->socket)) {
            fclose($this->socket);
            $this->socket = NULL;
        }
    }

    /**
     * Validate message type valid or not.
     * @param string $data
     * @return void
     */
    protected function validate($data) {
        if (!is_string($data)
            && !is_numeric($data)
            && !is_bool($data)) {
            // modified by ccsong
            throw new OssException("Ef Socket data to be transport is valid");
        }
    }

    /**
     * Read random host config from array of settings.
     * @param string $hosts serialized array of settings
     * @return array 'name => host, port => port'
     */
    protected function randomReadHost($hosts) {
        return $hosts[rand(0, count($hosts) - 1)];
    }
}
