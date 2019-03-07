<?php

class CoursesuiteValidatorException extends \Exception
{
    public function __construct($message, $code, $stack = null)
    {
        echo "<h1>Error $code</h1><p>$message</p>";
        echo "<p><a href='" . getenv("HOME_URL") . "'>" . getenv("HOME_URL") . "</a></p>";
        if (null !== $stack) {
            var_dump($stack);
        }

        exit; // no further output
    }
}

class CoursesuiteValidator
{

    private $debug;
    private $verify_tls = true;

    function __construct($debug = false, $verify = true) {
        $this->debug = $debug;
        $this->verify_tls = $verify;
    }

    public function Validate($get) {

        $result = new \stdClass();
        $result->valid = false;

        $result->licence = new \stdClass();
        $result->licence->tier = 0;
        $result->licence->seats = 1;
        $result->licence->remaining = 1;

        $result->code = new \stdClass();
        $result->code->minified = true;
        $result->code->debug = false;

        $result->api = new \stdClass();
        $result->api->bearer = null;
        $result->api->publish = "";
        $result->api->header = new \stdClass();
        $result->api->header->html = null;
        $result->api->header->css = null;

        $result->user = new \stdClass();
        $result->user->container = "";
        $result->user->email = "";

        $result->app = new \stdClass();
        $result->app->addons = array();

        if (isset($get["hash"])) {
            try {
                $ch = curl_init();
                if ($this->debug) echo "GET ", str_replace('{hash}', $get["hash"], getenv("AUTHAPI_URL")), PHP_EOL, PHP_EOL;
                if ($this->debug) curl_setopt($ch, CURLOPT_HEADERFUNCTION, "self::debug");
                curl_setopt($ch, CURLOPT_URL, str_replace('{hash}', $get["hash"], getenv("AUTHAPI_URL")));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-NinjaValidator: true"));
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                curl_setopt($ch, CURLOPT_USERPWD, getenv("AUTHAPI_USER") . ":" . getenv("AUTHAPI_PASSWORD"));
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verify_tls);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_tls);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $resp = curl_exec($ch);
                if ($this->debug) echo $resp, PHP_EOL;

                if (curl_errno($ch)) {
                    throw new CoursesuiteValidatorException(curl_error($ch), 500);
                }
                // validate HTTP status code (user/password credential issues)
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($status_code != 200) {
                    throw new CoursesuiteValidatorException("Response with Status Code [" . $status_code . "].", $status_code);
                }
            } catch (Exception $ex) {
                throw new CoursesuiteValidatorException('Unable to properly download file from url=['+$url+'] to path ['+$destination+'].', 500, $ex);
            } finally {
                if ($ch != null) {
                    curl_close($ch);
                }
            }
            $result = json_decode($resp);
        }
        $result->home = getenv("HOME_URL");
        return $result;
    }

       private function debug($curl, $header_line)
        {
            echo $header_line;
            return strlen($header_line);
        }
}
