<?php
/**
 *	XML-RPC client - improved RPC client based on PEAR package XML-RPC
 * 
 *	@author     Karel Kozlik
 *	@version    $Id: xml_rpc_patch.php,v 1.5 2007/02/14 16:36:39 kozlik Exp $
 *	@package    XML_RPC
 */ 

/**
 * The methods and properties for submitting XML RPC requests
 *
 * This class extending class XML_RPC_Client, it useing CURL library for
 * http connections.
 *
 * Main advantages are:
 *  - supported digest authentification
 *  - supported authentification by SSL certificates
 *  - permanent connections
 * Disadvantage:
 *  - dependency on CURL library
 *
 * You may found more info (specialy for SSL related settings) in documentation
 * of CURL extension:
 * @link http://www.php.net/manual/en/function.curl-setopt.php
 *
 * @category   Web Services
 * @package    XML_RPC
 * @author     Karel Kozlik <karel@iptel.org>
 */
class XML_RPC_Client_curl extends XML_RPC_Client
{
    var $extra_headers   = array();
    var $ssl_cert        = null;
    var $ssl_cert_passw  = null;
    var $ssl_key         = null;
    var $ssl_key_passw   = null;
    var $ssl_ca          = null;
    var $ssl_ciphers     = null;
    var $ssl_version     = null;
    var $ssl_verify_host = null;

	var $http_codes = array(
		100 => "Continue",
		101 => "Switching Protocols",
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		306 => "(Unused)",
		307 => "Temporary Redirect",
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested Range Not Satisfiable",
		417 => "Expectation Failed",
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported"
	);

    /**
     *  Add extra http header to request
     *
     *  Example:
     *  <code>
     *  $client = new XML_RPC_Client_curl('/', 'http://example.org');
     *  $client -> addHTTPHeader("X-My-Header: abc");
     *  </code>
     *
     *  @param  string  $header
     */
    function addHTTPHeader($header)
    {
        $this->extra_headers[] = $header;
    }

    /**
     *  Set the name of a file holding one or more certificates to verify the peer with.
     *
     *  @param  string  $file
     */
    function setSSLCA($file)
    {
        $this->ssl_ca = $file;
    }

    /**
     *  Set the name of a file containing a PEM formatted certificate
     *
     *  @param  string  $file
     *  @param  string  $password   The password required to use the certificate (if any)
     */
    function setSSLCert($file, $password=null)
    {
        $this->ssl_cert = $file;
        $this->ssl_cert_passw = $password;
    }

    /**
     *  The name of a file containing a private SSL key
     *
     *  @param  string  $file
     *  @param  string  $password   The secret password needed to use the private SSL key (if any)
     */
    function setSSLKey($file, $password=null)
    {
        $this->ssl_key = $file;
        $this->ssl_key_passw = $password;
    }

    /**
     *  A list of ciphers to use for SSL.
     *
     *  For example, RC4-SHA and TLSv1 are valid cipher lists.
     *  You'll find more details about cipher lists on this URL:
     *  @link http://www.openssl.org/docs/apps/ciphers.html
     *
     *  @param  string  $ciphers
     */
    function setSSLCipherList($ciphers)
    {
        $this->ssl_ciphers = $ciphers;
    }

    /**
     *  The SSL version (2 or 3) to use.
     *
     *  By default PHP will try to determine this itself, although in
     *  some cases you must set this manually.
     *
     *  @param  int     $version
     */
    function setSSLVersion($version)
    {
        $this->ssl_version = $version;
    }

    /**
     *  1 to check the existence of a common name in the SSL peer certificate.
     *  2 to check the existence of a common name and also verify that it
     *  matches the hostname provided.
     *
     *  @param  int         $vh
     */
    function setSSLVerifyHost($vh)
    {
        $this->ssl_verify_host = $vh;
    }

    /**
     *  Set the SSL params to CURL session
     *
     *  @access     private
     *  @param  resource    $ch
     */
    function curl_ssl_settings(&$ch)
    {
        if ($this->ssl_ca) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->ssl_ca);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if ($this->ssl_cert) {
            curl_setopt($ch, CURLOPT_SSLCERT, $this->ssl_cert);
        }

        if ($this->ssl_cert_passw) {
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->ssl_cert_passw);
        }

        if ($this->ssl_key) {
            curl_setopt($ch, CURLOPT_SSLKEY, $this->ssl_key);
        }

        if ($this->ssl_key_passw) {
            curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->ssl_key_passw);
        }

        if ($this->ssl_ciphers) {
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, $this->ssl_ciphers);
        }

        if ($this->ssl_version) {
            curl_setopt($ch, CURLOPT_SSLVERSION, $this->ssl_version);
        }

        if (!is_null($this->ssl_verify_host)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->ssl_verify_host);
        }
    }

    /**
     *  Set an extra options to CURL session
     *
     *  This method should be overwriten in child class
     *
     *  @param  resource    $ch
     */
    function curl_extra_settings(&$ch)
    {
    }

    /**
     * Transmit the RPC request via HTTP protocol
     *
     * Requests should be sent using XML_RPC_Client send() rather than
     * calling this method directly.
     *
     * @param object $msg       the XML_RPC_Message object
     * @param string $server    the server to send the request to
     * @param int    $port      the server port send the request to
     * @param int    $timeout   how many seconds to wait for the request
     *                           before giving up
     * @param string $username  a user name for accessing the RPC server
     * @param string $password  a password for accessing the RPC server
     *
     * @return object  an XML_RPC_Response object.  0 is returned if any
     *                  problems happen.
     *
     * @access protected
     * @see XML_RPC_Client::send()
     */
    function sendPayloadHTTP10($msg, $server, $port, $timeout = 0,
                               $username = '', $password = '')
    {
        global $XML_RPC_str, $XML_RPC_err;

        // Pre-emptive BC hacks for fools calling sendPayloadHTTP10() directly
        if ($username != $this->username) {
            $this->setCredentials($username, $password);
        }

        /*
         *  Constructor of XML_RPC_Client changes the https:// protocol to ssl://
         *  but ssl:// protocol is not known for CURL. So change the ssl:// back
         *  to https://
         */
        if ($this->protocol == 'ssl://') {
            $protocol = 'https://';
        } else {
            $protocol = $this->protocol;
        }

        /* Format the URL to connect */
        $url = $protocol.$server;
        if ($port) {
            $url .= ":".$port;
        }
        $url .= $this->path;

        /* Initialize CURL session */
        $ch = curl_init($url);

        if ($this->debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, "PEAR XML_RPC");

        if ($timeout > 0) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }

        /* Set proxy */
        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxy_port);
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);

            if ($this->proxy_user) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$this->proxy_user:$this->proxy_pass");
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_ANY);
            }
        }

        /* set credentials */
        if ($this->username) {
            curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        }

        /* change options for SSL */
        $this->curl_ssl_settings($ch);

        // Only create the payload if it was not created previously
        if (empty($msg->payload)) {
            $msg->createPayload();
        }

        /* set http method to POST and add the body of request */
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg->payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* add extra http headers to request */
        if ($this->extra_headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->extra_headers);
        }

        /* change some extra options of CURL session */
        $this->curl_extra_settings($ch);

        /* execute CURL session */
        $data = curl_exec($ch);

        /* is there an error during session execution? */
        if (false === $data) {
            $this->errno  = curl_errno($ch);
            $this->errstr = curl_error($ch);

            $this->raiseError('Connection to RPC server '
                              . $server . ':' . $port
                              . ' failed. ' . $this->errstr,
                              XML_RPC_ERROR_CONNECTION_FAILED);
            curl_close($ch);
            /*
             * Just raising the error without returning it is strange,
             * but keep it here for backwards compatibility.
             */
            return 0;
        }

        /* check if server returned 200 OK */
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
                $errcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                error_log('HTTP error code: ' . $errcode);

                $this->errno  = $XML_RPC_err['http_error'];
                $this->errstr = $XML_RPC_str['http_error'] . 
				                ' (' . $errcode . 
								  ( isset($this->http_codes[$errcode]) ? 
								    ' '.$this->http_codes[$errcode] : '' ) .
								')';

                $this->raiseError($this->errstr, $this->errno);
                curl_close($ch);
                return 0;
        }

        /* close CURL session */
        curl_close($ch);

        /*
         *  Fake HTTP header for $msg->parseResponse method
         *  Do not use curl_setopt($ch, CURLOPT_HEADER, 1) to return
         *  headers in $data variable, because when digest authentication is
         *  used, the $data variable contains headers of two http requests
         *  and $msg->parseResponse method is not able to parse it correctly.
         */
        $data = "HTTP/1.0 200 OK\r\n\r\n".$data;

        /* parse the response */
        $resp = $msg->parseResponse($data);

        return $resp;
    }
}

/**
 *	This class overides method parseResponseFile() from class XML_RPC_Message
 *	
 *	The diference is that this class parse content-length header and read only
 *	number of bytes specified by this header from server. This method do not 
 *	wait to closing the TCP connection by the server.
 *
 *	@package    XML_RPC
 */
class XML_RPC_Message_patched extends XML_RPC_Message{

    function parseResponseFile($fp){

		//are there still data to read?		
		$continue_reading = true;
		//read headers or body of response?
		$read_body = false;
		//length of body of response
		$content_length = null;
		
		$last_line = "";
		//counter of received bytes of body
		$received = 0;
		//number of bytes readed by function fread()
		$max_recv_len = 8192;
		//number of bytes readed by function fread() on last call of this function
		$recv_len = $max_recv_len;

        $ipd = '';

        while ($continue_reading) {
        	if (false === ($data = @fread($fp, $recv_len))) break;
        	if (! strlen($data)) break;

			// save received data for parseResponse method
            $ipd .= $data;

			if ($read_body){
				//reading body of response
				
				//count how many bytes are received
				$received += strlen($data);
			}
			else {
				//reading headers of response

				//concat received data with end of previously received data (probably incoplete line)
				$data = $last_line.$data;
				//split received data to lines
				$lines = explode("\n", $data);

				//don't process last line now (may be incoplete), save it for later processing
				$last_line = $lines[count($lines)-1];
				unset ($lines[count($lines)-1]);

				//walk through lines
				foreach($lines as $k => $v){
					// stop reading headers on empty line
					if (trim($v) == "") {
						$read_body = true;
						continue;
					}

					// if reading body of response
					if($read_body){
						//only count length of received response
						$received += strlen($v);
						
						//add "\n" striped by function explode()
						$received++;
						continue;
					}
					
					// parse header content-length
					if (preg_match('/^Content-Length:(.*)$/i', $v, $regs)){
						$regs[1] = trim($regs[1]);
						if (!is_numeric($regs[1])) continue;
						
						$content_length = $regs[1];
					}
					
				}
				
				// if reading body of response
				if($read_body){
					// add length of $last_line
					$received += strlen($last_line);
				}
				
			}
			
			// is there still data to read
			if (!is_null($content_length) and ($received >= $content_length)){
				$continue_reading = false;
			}

			// length of last batch of data
			if (!is_null($content_length) and ($content_length - $received < $max_recv_len)){
				$recv_len = $content_length - $received;
			}
        }
        
        
        return $this->parseResponse($ipd);
    }

}

?>
