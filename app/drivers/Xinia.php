<?php

namespace Oishy\Driver;

use \Requests;

/**
 * Simple YouTube Public API Wrapper
 *
 * @author Miraz Mac <mirazmac@gmail.com>
 */
class Xinia
{
    /**
     * Youtube Api v3 Server Key
     *
     * @var string
     */
    protected $api_key;

    protected $page_info;

    public $extraParams = [];

    /**
     * Create a new Xinia instance
     *
     * @param string $api_key Valid YouTube v3 API Key
     */
    public function __construct($api_key)
    {
        if (empty($api_key) || !is_string($api_key)) {
            throw new \InvalidArgumentException("Please provide a valid YouTube v3 API Key!");
        }
        $this->api_key = $api_key;
    }

    public function search(array $params = [], $api = 'search')
    {
        // What kind of sorcery is this? *_*
        if (empty($params)) {
            return false;
        }
        $api_url = 'https://www.googleapis.com/youtube/v3/search';
        if ($api !== 'search') {
            $api_url = 'https://www.googleapis.com/youtube/v3/videos';
        }

        $api_data = $this->apiRequest($api_url, $params);

        return $this->decodeList($api_data);
    }

    public function getChannelInfo($id)
    {
        $api_url = 'https://www.googleapis.com/youtube/v3/channels';
        $params = [
            'id' => $id,
            'part' => 'id,snippet'
        ];
        $api_data = $this->apiRequest($api_url, $params);
        $response = json_decode($api_data);

        if (!$response || !empty($response->error)) {
            return false;
        }

        if (isset($response->items[0])) {
            return $response->items[0];
        }
        return false;
    }

    public function getStat($key, $fallback = false)
    {
        if (isset($this->page_info->{$key})) {
            return $this->page_info->{$key};
        }
        return $fallback;
    }

    protected function decodeList(&$api_data)
    {
        $response = json_decode($api_data);

        if (!$response || !empty($response->error)) {
            return [];
        }

        $page_info = [
            'resultsPerPage' => $response->pageInfo->resultsPerPage,
            'totalResults'   => $response->pageInfo->totalResults,
            'kind'           => $response->kind,
            'etag'           => $response->etag,
            'prevPageToken'     => null,
            'nextPageToken'     => null
        ];

        if (isset($response->prevPageToken)) {
            $page_info['prevPageToken'] = $response->prevPageToken;
        }

        if (isset($response->nextPageToken)) {
            $page_info['nextPageToken'] = $response->nextPageToken;
        }
        $this->page_info = (object)$page_info;
        return $response->items;
    }

    /**
     * Make a Request to API
     *
     * @param  string $url    The API Endpoint
     * @param  array  $params URL Query Parameters as array
     * @return mixed
     */
    protected function apiRequest($url, array $params = [])
    {
        // Set default headers
        $headers = [
            'Accept-Encoding' => 'gzip',
            'User-Agent' => 'Xinia/1.0 (+http://github.com/MirazMac/Xinia) (gzip)',
            'Referer' => $_SERVER['HTTP_HOST']
        ];

        // Add API key to the request
        $params['key'] = $this->api_key;

        $params = array_merge($params, $this->extraParams);

        //d($params);exit();

        // Build the final URL
        $url = $url . '?' . http_build_query($params);
        // Make the request
        try {
            $response = Requests::get($url, $headers);
        } catch (\Requests_Exception $e) {
            // ssh!
            return false;
        }

        return $response->body;
    }
}
