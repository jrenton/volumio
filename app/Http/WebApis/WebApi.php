<?php

namespace App\Http\WebApis;

class WebApi
{
    private $client;
    private $headers;
    private $baseUrl;
    
    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }
    
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }
    
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    
    public function post($url, $options = [])
    {
        $response = $this->send("POST", $url, $options);
        
        return $response->getStatusCode() == 201; 
    }
    
    public function get($url, $options = [])
    {
        $response = $this->send("GET", $url, $options);
        
        return json_decode($response->getBody());
    }
    
    public function delete($url, $options = [])
    {
        $response = $this->send("DELETE", $url, $options);
        
        return $response->getStatusCode() == 204;
    }
    
    public function put($url, $options = [])
    {
        $response = $this->send("PUT", $url, $options);
        
        return $response->getStatusCode() == 200;
    }
    
    private function send($method, $url, $options = [])
    {
        if ($this->headers)
        {
            if (array_key_exists("headers", $options))
            {
                array_merge($options["headers"], $this->headers);
            }
            else
            {
                $options["headers"] = $this->headers;
            }
        }
        
        return $this->client->request($method, $this->baseUrl . $url, $options);
    }
}