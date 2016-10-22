<?php

/**
 * CookPHP Framework
 *
 * @name CookPHP Framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */

namespace Helpers;

/**
 * HttpClient 使用socket模拟post、get操作,支持http、socket4、5代理
 * @author CookPHP <admin@cookphp.org>
 */
class HttpClient {

    public $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.11 Safari/537.36';
    public $useRandomCookieFile = false;
    public $randomCookieFilePrefix = 'phphc';
    public $lastpageFile = null;
    protected $ch;
    protected $_cookieFile = null;
    protected $defaults = [
        'url' => '',
        'post' => null,
        'headers' => null,
        'ref' => '',
        'header' => false,
        'nobody' => false,
        'timeout' => 15,
        'tofile' => null,
        'attempts_max' => 1,
        'attempts_delay' => 10,
        'curl' => []
    ];

    public function __construct($params = []) {
        foreach ($params as $key => $val) {
            $this->$key = $val;
        }
        $this->init();
    }

    function init() {
        if ($this->useRandomCookieFile) {
            $this->setRandomCookieFile();
        }
    }

    public function __get($name) {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        return $this;
    }

    /**
     * Runs http request to get responce headers.
     * @param string $url request url.
     * @param array $params request params.
     * @return string|boolean returns response in the usual case, true when
     * result goes to file and false if request failed.
     * @throws CException when "tofile" is defined and file is not writeable.
     */
    public function head($url, $params = []) {
        $params['url'] = $url;
        $params['header'] = true;
        $params['nobody'] = true;
        return $this->request($params);
    }

    /**
     * Runs http GET request.
     * @param string $url request url.
     * @param array $params request params.
     * @return string|boolean returns response in the usual case, true when
     * result goes to file and false if request failed.
     * @throws CException when "tofile" is defined and file is not writeable.
     */
    public function get($url, $params = []) {
        $params['url'] = $url;
        return $this->request($params);
    }

    /**
     * Runs http POST request.
     * @param string $url request url.
     * @param array $post post data.
     * @param array $params request params.
     * @return string|boolean returns response in the usual case, true when
     * result goes to file and false if request failed.
     * @throws CException when "tofile" is defined and file is not writeable.
     */
    public function post($url, $post = [], $params = []) {
        $params['url'] = $url;
        $params['post'] = $post;
        return $this->request($params);
    }

    /**
     * Downloads file.
     * @param string $url request url.
     * @param string $dest file destination.
     * @param array $params request params.
     * @return boolean true when file is downloaded and false if downloading
     * failed.
     * @throws CException when $dest file is not writeable.
     */
    public function download($url, $dest, $params = []) {
        $params['url'] = $url;
        $params['tofile'] = $dest;
        return $this->request($params);
    }

    /**
     * Runs http request.
     * @param array $params request params.
     * @return string|boolean returns response in the usual case, true when
     * result goes to file and false if request failed.
     * @throws CException when "tofile" is defined and file is not writeable.
     */
    public function request($params) {
        $params += $this->defaults;
        if (isset($this->ch)) {
            curl_close($this->ch);
            $this->ch = null;
        }
        $ch = $this->createCurl($params);
        if (isset($params['tofile'])) {
            $tofile = fopen($params['tofile'], 'wb');
            if (!$tofile) {
                throw new \Exception(__CLASS__ . " couldn't open file '{$params['tofile']}' for edit.");
            }
            curl_setopt($ch, CURLOPT_FILE, $tofile);
        }
        do {
            $res = curl_exec($ch);
        } while (
        $res === false && // 
        --$params['attempts_max'] != 0 &&
        sleep($params['attempts_delay']) !== false
        );

        if (isset($params['tofile'])) {
            fclose($tofile);
            if ($res === false) {
                unlink($params['tofile']);
            }
        }
        $this->ch = $ch;
        // Saving response content into lastpageFile
        if ($this->lastpageFile != null) {
            file_put_contents($this->lastpageFile, $res);
        }
        return $res;
    }

    /**
     * Creates multiple request
     * @param array $requests requests parameters [key] => [params array]
     * @param array $defaults default request paremeters
     * @return array http request results array [key] => [result string]
     * Requests array keys are used to differ results
     */
    public function multiRequest($requests, $defaults = []) {
        if (empty($requests)) {
            return [];
        }
        $defaults += $this->defaults;
        $mh = curl_multi_init();
        $handles = [];
        foreach ($requests as $key => $request) {
            $ch = $this->createCurl($request + $defaults);
            curl_multi_add_handle($mh, $ch);
            $handles[$key] = $ch;
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        $results = [];
        foreach ($handles as $key => $ch) {
            $results[$key] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
        return $results;
    }

    protected function createCurl($params) {
        $options = [
            CURLOPT_URL => $params['url'],
            CURLOPT_HEADER => $params['header'],
            CURLOPT_TIMEOUT => $params['timeout'],
            CURLOPT_USERAGENT => $this->useragent,
            CURLOPT_RETURNTRANSFER => !isset($params['tofile']),
            CURLOPT_NOBODY => $params['nobody'],
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_ENCODING => ''
        ];
        if (!empty($params['ref'])) {
            $options[CURLOPT_REFERER] = $params['ref'];
        }
        if ($params['post'] !== null) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params['post'];
        }
        if ($params['headers'] !== null) {
            $options[CURLOPT_HTTPHEADER] = $params['headers'];
        }
        $cookieFile = $this->getCookieFile();
        if ($cookieFile !== null) {
            $options[CURLOPT_COOKIEFILE] = $cookieFile;
            $options[CURLOPT_COOKIEJAR] = $cookieFile;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $params['curl'] + $options);
        return $ch;
    }

    # Getters #

    public function getCookieFile() {
        return $this->_cookieFile;
    }

    /**
     * Returns last request error
     * @return string 
     */
    public function getLastError() {
        return isset($this->ch) ? curl_error($this->ch) : null;
    }

    /**
     * Returns information about the last transfer.
     * @see curl_getinfo
     * @param integer $opt
     * @return mixed
     */
    public function getInfo($opt = null) {
        return isset($this->ch) ? curl_getinfo($this->ch, $opt) : null;
    }

    /**
     * Last received HTTP code.
     * @return integer
     */
    public function getHttpCode() {
        return $this->getInfo(CURLINFO_HTTP_CODE);
    }

    /**
     * Last effective url.
     * @return string
     */
    public function getEffectiveUrl() {
        return $this->getInfo(CURLINFO_EFFECTIVE_URL);
    }

    /**
     * Current cookies.
     * Warning! This function has side effects - you can't call getInfo() of
     * getLastError() after calling this function.
     * @return array
     */
    public function getCookies() {
        if (!$this->getCookieFile()) {
            return [];
        }
        unset($this->ch);
        $text = File::get($this->getCookieFile());
        $cookies = [];
        foreach (explode("\n", $text) as $line) {
            $parts = explode("\t", $line);
            if (count($parts) === 7) {
                $cookies[$parts[5]] = $parts[6];
            }
        }
        return $cookies;
    }

    # Setters #

    public function setCookieFile($fname, $clear = true) {
        $this->_cookieFile = $fname;
        if ($clear) {
            $this->clearCookieFile();
        }
    }

    public function setRandomCookieFile() {
        $fileName = tempnam(sys_get_temp_dir(), $this->randomCookieFilePrefix);
        $this->setCookieFile($fileName, true);
    }

    # Actions #

    /**
     * Creates and clears cookie file 
     */
    public function clearCookieFile() {
        $cookieFile = $this->getCookieFile();
        if ($cookieFile !== null) {
            file_put_contents($cookieFile, '');
        }
    }

    public function __destruct() {
        unset($this->ch);
        $cookieFile = $this->getCookieFile();
        if ($cookieFile !== null) {
            is_file($cookieFile) && unlink($cookieFile);
        }
    }

}
