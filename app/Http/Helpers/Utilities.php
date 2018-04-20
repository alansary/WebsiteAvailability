<?php

namespace App\Http\Helpers;

class Utilities
{
    /**
     * function to check if a URL is up or down
     *
     * @param  string $domain URL to check if up or down
     * @return bool           returns the active status of the URL
     */
    public static function curlInit($domain)
    {
       $curlInit = curl_init($domain);
       curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
       curl_setopt($curlInit,CURLOPT_HEADER,true);
       curl_setopt($curlInit,CURLOPT_NOBODY,true);
       curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

       // get response
       $response = curl_exec($curlInit);

       // closing the connection
       curl_close($curlInit);

       // return the response
       return ($response) ? true : false;
    }
}