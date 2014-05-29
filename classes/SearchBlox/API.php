<?php
namespace SearchBlox;

if (!defined("ABSPATH")) exit;

class API
{
    private static $client_id = "";
    private static $apikey = "";
    
    public static $response;

    public static function __callStatic($method, $args)
    {
        $client_id = get_option('rw_client_id');
        $apikey = get_option('rw_api_key');

        if ($client_id && $apikey) {
            self::$client_id = $client_id;
            self::$apikey = $apikey;
        }

        return call_user_func_array(get_called_class() . '::' . $method, $args);
    }

    protected static function get($url, $data = array())
    {
        $url = rtrim(self::apiURL($url), '/') . '/?';

        if (!self::$client_id || !self::$apikey) {
            return;
        }
        
        $params = array_merge(array (
            'client_id' => self::$client_id,
            'api_key' => self::$apikey
        ), $data);
        
        $url = $url . http_build_query($params);

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
    
    private static function apiURL($append = '')
    {
        return 'https://api.digitalocean.com/v1/' .  rtrim($append, '/');
    }
}