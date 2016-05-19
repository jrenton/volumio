<?php

namespace App\Volumio\WebApis;

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
    
    public function post($url, $body, $options = [])
    {
        $response = $this->send("POST", $url, $body, $options);
        
        return $response->getStatusCode() == 201; 
    }
    
    public function get($url, $options = [])
    {
        $response = $this->send("GET", $url, null, $options);
        
        return json_decode($response->getBody());
    }
    
    public function delete($url, $options = [])
    {
        $response = $this->send("DELETE", $url, null, $options);
        
        return $response->getStatusCode() == 204;
    }
    
    public function put($url, $body, $options = [])
    {
        $response = $this->send("PUT", $url, $body, $options);
        
        return $response->getStatusCode() == 200;
    }
    
    private function send($method, $url, $body, $options = [])
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
        
        if ($body)
        {
            $options["json"] = $body;
        }
        
        return $this->client->request($method, $this->baseUrl . $url, $options);
    }
}