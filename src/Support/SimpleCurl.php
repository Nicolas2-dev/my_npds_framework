<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class SimpleCurl
{

    /**
     * Undocumented function
     *
     * @param [type] $url
     * @param array $params
     * @return void
     */
    public static function get($url, $params = array())
    {
        $url = $url . '?' . http_build_query($params, '', '&');
        $ch = curl_init();

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        );
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Undocumented function
     *
     * @param [type] $url
     * @param array $fields
     * @return void
     */
    public static function post($url, $fields = array())
    {
        $ch = curl_init();

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_POST => true,
            CURLOPT_USERAGENT => "Npds Agent",
        );
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Undocumented function
     *
     * @param [type] $url
     * @param array $fields
     * @return void
     */
    public static function put($url, $fields = array())
    {
        $post_field_string = http_build_query($fields);
        $ch = curl_init($url);

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $post_field_string
            );
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
    
}
