<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class API
{
    private static $oAuthToken = "";
    
    public static $response;
    
    public static $chInfo = null;
    
    private static $curlSetopt = array(
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        )
    );

    public static function __callStatic($method, $args)
    {
        $_method = strtolower($method);

        preg_match_all('~^(get|post|delete)$~', $_method, $matches);

        if (!empty($matches[1][0])) {
            $custom_request = strtoupper($matches[1][0]);
            self::$curlSetopt[CURLOPT_CUSTOMREQUEST] = $custom_request;

            $curlSetopt = self::$curlSetopt;

            switch ($custom_request) {
                case "POST":
                    $curlSetopt[CURLOPT_POST] = true;
                    if (isset($args[1])) {
                        $curlSetopt[CURLOPT_POSTFIELDS] = json_encode($args[1]);
                    }
                    break;
                case "DELETE":
                    $curlSetopt[CURLOPT_HTTPHEADER] = array(
                        'Content-Type: application/x-www-form-urlencoded'
                    );
                    break;
                default:
                    break;
            }

            self::$curlSetopt = $curlSetopt;

            self::request($args[0]);
        }

        return new self;
    }

    private static function returnAuth()
    {
        self::$oAuthToken = get_option('rw_oauth_token');
        return "Authorization: Bearer " . self::$oAuthToken;
    }

    private static function addAuthorization()
    {
        if (!in_array(self::returnAuth(), self::$curlSetopt[CURLOPT_HTTPHEADER]))
            self::$curlSetopt[CURLOPT_HTTPHEADER][] = self::returnAuth();
    }
    
    private static function request($url)
    {
        $url = self::generateURL($url);

        $ch = curl_init();

        self::$curlSetopt[CURLOPT_URL] = $url;

        // Authorization Bearer
        self::addAuthorization();

        curl_setopt_array($ch, self::$curlSetopt);

        self::$response = curl_exec($ch);
        self::$chInfo = curl_getinfo($ch);
        
        curl_close($ch);
    }

    public function jsonDecode($array = true)
    {
        if (self::$response) {
            self::$response = json_decode(self::$response, $array);
        }

        return $this;
    }
    
    public function getResponse()
    {
        return self::$response;
    }
    
    public static function generateURL($request)
    {
        if (!$request) return;
        
        $url = rtrim(self::apiURL($request), '/');

        return $url;
    }
    
    private static function apiURL($append = '')
    {
        return 'https://api.digitalocean.com/v2/' .  rtrim($append, '/');
    }
}