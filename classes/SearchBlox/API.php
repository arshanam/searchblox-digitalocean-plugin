<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class API
{
    private static $oAuthToken = "";
    
    public static $response;
    
    private static $curlSetopt = array(
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Content-type: application/json'
        )
    );

    private static function verifyAuth()
    {
        self::$oAuthToken = get_option('rw_oauth_token');
        
        if (!self::$oAuthToken) {
            return false;
        }
        
        return true;
    }
    
    private static function returnAuth()
    {
        return "Authorization: Bearer " . self::$oAuthToken;
    }

    private static function addAuthorization()
    {
        self::$curlSetopt[CURLOPT_HTTPHEADER][] = self::returnAuth();
    }

    public static function get($url, $data = array())
    {
        $url = self::generateURL($url, $data);

        $ch = curl_init();
        
        if (array_key_exists(CURLOPT_POST, self::$curlSetopt))
            unset(self::$curlSetopt[CURLOPT_POST]);
        
        if (array_key_exists(CURLOPT_POSTFIELDS, self::$curlSetopt))
            unset(self::$curlSetopt[CURLOPT_POSTFIELDS]);

        self::$curlSetopt[CURLOPT_URL] = $url;
        self::addAuthorization();

        curl_setopt_array($ch, self::$curlSetopt);

        self::$response = curl_exec($ch);
        curl_close($ch);

        return new self;
    }

    public function post($url, $data = array())
    {
        $url = self::generateURL($url, $data);
        
        $ch = curl_init();
        
        self::$curlSetopt[CURLOPT_URL] = $url;
        self::$curlSetopt[CURLOPT_POST] = true;
        self::$curlSetopt[CURLOPT_POSTFIELDS] = json_encode($data);
        self::addAuthorization();

        curl_setopt_array($ch, self::$curlSetopt);
        
        self::$response = curl_exec($ch);
        curl_close($ch);

        return new self;        
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
    
    public function generateURL($request, $data = array())
    {
        if (!$request || !self::verifyAuth()) return;
        
        $url = rtrim(self::apiURL($request), '/');

        return $url;
    }
    
    private static function apiURL($append = '')
    {
        return 'https://api.digitalocean.com/v2/' .  rtrim($append, '/');
    }
}