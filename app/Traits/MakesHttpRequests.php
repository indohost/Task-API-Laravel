<?php

namespace App\Traits;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests as ConcernsMakesHttpRequests;

trait MakesHttpRequests
{
    use ConcernsMakesHttpRequests;

    /**
     * Visit the given URI with a GET request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @param  int  $options
     * @param  array  $data
     * @return \Illuminate\Testing\TestResponse
     */
    public function getJson($uri, array $headers = [], $options = 0, array $data = [])
    {
        return $this->json('GET', $uri, $data, $headers, $options);
    }
}
