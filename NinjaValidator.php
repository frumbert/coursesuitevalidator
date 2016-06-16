<?php

namespace Ninjitsu;

// TODO: write a better exception
// perhaps post a value back to coursesuite api so we get centralised logging
class ValidationException extends \Exception {}

class Validator {

    const USERNAME = 'tokenuser';
    const PASSWORD = 'GEv6mJ7wJgWR';
    const AUTHAPI = 'http://my.coursesuite.ninja/api/';
    const APP_KEY = "docninja";

    protected $url;
    protected $checked;
    protected $kind;

    protected function set_url(...$params) {
        $kind = strtolower(str_replace("verify","", $params[0]));
        array_walk($params, function (&$param) {
            $param = urlencode($param); // $param is byref
        });
        $this->url = self::AUTHAPI . implode("/", $params);
        return $this;
    }

    public function __construct($get) {
        $token = (isset($get["token"])) ? str_replace(' ', '+', $get["token"]) : false;
        $apikey = (isset($get["apikey"])) ? str_replace(' ', '+', $get["apikey"]) : false;
        $checked = false;

        if ($token) {
            self::set_url("verifyToken", self::APP_KEY, $token);
        } else if ($apikey) {
            self::set_url("verifyApiKey", $apikey);
        } else if ( isset($_GET['backdoor'])) {
            throw new ValidationException("Seriously, this was never a real thing. In fact, I'm going to crash right now.", 1);
            exit;
        } else if (!$token && !$apikey) {
            throw new ValidationException("App missing critical startup parameters; fatal error.", 1);
            exit;
        }
        return $this; // to allow chaining

    }

    public function check() {
        $resp = json_decode("{'valid':false, 'teir':0, 'api': false}");
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            curl_setopt($ch, CURLOPT_USERPWD, self::USERNAME . ":" . self::PASSWORD);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // later on, yes
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // probably need for 401 redirect?
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // we are seeking the result
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $resp = curl_exec($ch);
            if(curl_errno($ch))
                throw new ValidationException(curl_error($ch), 500);

            // validate HTTP status code (user/password credential issues)
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($status_code != 200)
                throw new ValidationException("Response with Status Code [" . $status_code . "].", 500);

        } catch(Exception $ex) {
            if ($ch != null) curl_close($ch);
            throw new ValidationException('Unable to properly download file from url=[' + $url + '] to path [' + $destination + '].', 500, $ex);
        }
        if ($ch != null) curl_close($ch);
        $checked = true;
        $this->response = json_decode($resp);
        return $this; // to allow chaining
    }

    function is_api() {
        if (!$this->checked) self::check();
        return $this->response->api;
    }

    function is_valid() {
        if (!$this->checked) self::check();
        return $this->response->valid;
    }

    function api_org() {
        if (!$this->checked) self::check();
        return $this->response->org;
    }

    function get_tier() {
        if (!$this->checked) self::check();
        return (int) $this->response->tier;
    }

    function get_response() {
        if (!$this->checked) self::check();
        return $this->response;
    }
    
    function get_username() {
	    if (!$this->checked) self::check();
	    return $this->response->username;
    }

    function get_useremail() {
	    if (!$this->checked) self::check();
	    return $this->response->useremail;
    }

}
