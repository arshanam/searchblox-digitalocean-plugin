<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class API
{
    private static $client_id = "";
    private static $apikey = "";
    
    public static $response;
    
    private static function verifyAuth()
    {
        self::$client_id = get_option('rw_client_id');
        self::$apikey = get_option('rw_api_key');
        
        if (!self::$client_id || !self::$apikey) {
            return false;
        }
        
        return true;
    }
    
    private static function returnAuth()
    {
        return array (
            'client_id' => self::$client_id,
            'api_key' => self::$apikey
        );
    }
    
    public static function get($url, $data = array())
    {
        $url = self::generateURL($url, $data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
        
        $url = rtrim(self::apiURL($request), '/') . '/?';
        
        $params = array_merge(self::returnAuth(), $data);
        
        $url = $url . http_build_query($params);

        return $url;
    }
    
    private static function apiURL($append = '')
    {
        return 'https://api.digitalocean.com/v1/' .  rtrim($append, '/');
    }
}