<?php
namespace main;

class GiphyApi
{

    private $apiKey;
    private $endpoint = 'http://api.giphy.com/v1/gifs';

    private $httpStatus;


    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
    public function getHttpStatus() : int
    {
        return isset($this->httpStatus) ? $this->httpStatus : 404;
    }

    /**
     * @param  array  $params Possible parameters
     *          q - search query term or phrase
     *          limit - (optional) number of results to return, maximum 100. Default 25.
     *          offset - (optional) results offset, defaults to 0.
     *          rating - (optional) limit results to those rated (y,g, pg, pg-13 or r).
     *          lang - (optional) specify default country for regional content;
     *              format is 2-letter ISO 639-1 country code. See list of supported langauges here
     *          fmt - (optional) return results in html or json format
     *              (useful for viewing responses as GIFs to debug/test)
     * @return array      JSON String
     */
    public function searchGifs(array $params) : string
    {
        return $this->call('search', $params);
    }

    /**
     * Call cUrl Request
     * @param  string $method
     * @param  array  $params
     * @return [type]         [description]
     */
    private function call(string $method, array $params) : string
    {
        if (empty($method)) {
            $this->httpStatus = 404;
            $response = json_encode([
                'meta' => [
                    'status' => 404,
                    'msg' => 'Please choose method'
                ]
            ]);
        } else {
            $url = $this->endpoint . '/' . $method;
            $params['api_key'] = $this->apiKey;
            $url .= '?' . http_build_query($params);
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
            ];

            $resource = curl_init();
            curl_setopt_array($resource, $options);

            $response = curl_exec($resource);
            $this->httpStatus = curl_getinfo($resource, CURLINFO_HTTP_CODE);

            curl_close($resource);
        }
        return $response;
    }
}
