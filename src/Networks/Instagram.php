<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Exception;

/**
 * Extend Latest And Greatest To Support Instagram
 */
class Instagram extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--instagram.json';

    /**
     * Initalise the API secret variable
     * @var string
     */
    protected $userName;

    /**
     * Define number of results
     * @var integer
     */
    protected $maxResults = 4;

    /**
     * Define the API end point
     * @var string
     */
    protected $endpoint = 'https://api.instagram.com/v1/';

    /**
     * Prepare endpoint response variable
     * @var object
     */
    protected $endpointResponseUserData;

    /**
     * Prepare endpoint response variable
     * @var object
     */
    protected $endpointResponseUserMedia;

    /**
     * This fires when instance created
     */
    public function __construct($options = []) {
        try {
            $this->setUserName(isset($options['username'])?$options['username']:false);
            $this->setAccessToken(isset($options['access_token'])?$options['access_token']:false);
            parent::__construct($options);
        } catch (Exception $e) {
            echo '<pre>';
            echo 'Message: ' . $e->getMessage(). PHP_EOL;
            echo 'File: ' . $e->getFile(). PHP_EOL;
            echo 'Line: ' . $e->getLine(). PHP_EOL;
            echo 'Trace:' . PHP_EOL . $e->getTraceAsString(). PHP_EOL;
            echo '</pre>';
        }
    }

    /**
     * Set the page name
     * @deprecated Not required for loading profile.
     */
    public function setUserName($userName = false) {
        if ($userName) {
            $this->userName = $userName;
        } else {
            $this->userName = getenv('INSTAGRAM_USERNAME');
        }
    }

    /**
     * Get the page name
     */
    public function getUserName() {
        return $this->userName;
    }

    /**
     * Set the access token
     */
    public function setAccessToken($accessToken = false) {
        if (!$accessToken && !getenv('INSTAGRAM_ACCESS_TOKEN')) {
            throw new Exception('No INSTAGRAM_ACCESS_TOKEN defined in your .env or access_token is not set in options');
        }

        if ($accessToken) {
            $this->accessToken = $accessToken;
        } else {
            $this->accessToken = getenv('INSTAGRAM_ACCESS_TOKEN');
        }
    }

    /**
     * Get the access token
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Get the Instagram API User endpoint
     * @return String
     */
    public function getUserDataApiEndpoint() {
        $url = 'users/self';

        $query = [
            'access_token' => $this->getAccessToken()
        ];

        return $this->endpoint . $url . '/?' . urldecode(http_build_query($query));
    }

    /**
     * Get endpoint response
     * @return Object
     */
    public function getUserDataEndpointResponse() {
        if (!$this->endpointResponseUserData) {
            // Get data from API
            $endpointResult = @file_get_contents($this->getUserDataApiEndpoint());
            if (!$endpointResult) {
                throw new Exception('No data returned from endpoint');
            }

            // Convert to object
            $this->endpointResponseUserData = json_decode($endpointResult);
        }

        return $this->endpointResponseUserData;
    }

    /**
     * Get the Instagram API User endpoint
     * @return String
     */
    public function getUserMediaApiEndpoint() {
        $url = 'users/self/media/recent';

        $query = [
            'count' => $this->maxResults,
            'access_token' => $this->getAccessToken()
        ];

        return $this->endpoint . $url . '/?' . urldecode(http_build_query($query));
    }

    /**
     * Get endpoint response
     * @return Object
     */
    public function getUserMediaEndpointResponse() {
        if (!$this->endpointResponseUserMedia) {
            // Get data from API
            $endpointResult = @file_get_contents($this->getUserMediaApiEndpoint());
            if (!$endpointResult) {
                throw new Exception('No data returned from endpoint');
            }

            // Convert to object
            $this->endpointResponseUserMedia = json_decode($endpointResult);
        }

        return $this->endpointResponseUserMedia;
    }

    /**
     * Get page profile array
     * @return Array
     */
    protected function getProfileArray() {
        $array = [];

        // Get profile image data from API
        $endpointResult = $this->getUserDataEndpointResponse();

        if ($endpointResult) {

            $array['username'] = $endpointResult->data->username;

            // Get image as data string
            $imageDataString = @file_get_contents($endpointResult->data->profile_picture);


            // Get image dimensions and mime type
            $imageData = getimagesizefromstring($imageDataString);

            // Build relevant array
            $array['picture'] = [
                'width' => $imageData[0],
                'height' => $imageData[0],
                'src' => 'data:'. $imageData['mime'] .';base64,'. base64_encode($imageDataString)
            ];
        }

        return $array;
    }

    /**
     * Get releavant statistics
     * @return Array
     */
    protected function getStatisticsArray() {
        // Get api data
        $endpointResult = $this->getUserDataEndpointResponse();

        // Create statistics array
        $statistics = [
            'followers' => $endpointResult->data->counts->followed_by,
            'following' => $endpointResult->data->counts->follows
        ];

        return $statistics;
    }

    /**
     * Get an array of posts
     * @return Array
     */
    protected function getPostsArray() {
        // Get api data
        $endpointResult = $this->getUserMediaEndpointResponse();

        // Create posts array
        $posts = [];

        foreach ($endpointResult->data as $post) {
            $posts[] = [
                'id' => $post->id,
                'link' => $post->link,
                'text' => isset($post->caption->text) ? $post->caption->text : '',
                'date' => $post->created_time,
                'likes' => $post->likes->count,
                'comments' => $post->comments->count,
                'media' => [
                    'thumbnail' => $post->images->standard_resolution->url,
                    'width' => $post->images->standard_resolution->width,
                    'height' => $post->images->standard_resolution->height
                ]
            ];

        }

        return $posts;
    }
}
