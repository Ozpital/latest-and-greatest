<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Exception;

/**
 * Extend Latest And Greatest To Support YouTube
 */
class YouTube extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--youtube.json';

    /**
     * Initalise the Google Api Key variable
     * @var String
     */
    protected $apiKey;

    /**
     * Initalise the Youtube Channel ID variable
     * @var String
     */
    protected $channelID;

    /**
     * Define the API end point
     * @var String
     */
    protected $endpoint = 'https://www.googleapis.com/youtube/v3/';

    /**
     * @var String
     */
    protected $userName;

    /**
     * This fires when instance created
     */
    public function __construct($options = []) {
        try {
            $this->setApiKey(isset($options['api_key'])?$options['api_key']:false);
            $this->setChannelID(isset($options['channel_id'])?$options['channel_id']:false);
            $this->setUserName(isset($options['username'])?$options['username']:false);
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
     * Set the user name
     */
    public function setUserName($username = false) {
        if (!$username && !getenv('YOUTUBE_USERNAME')) {
            throw new Exception('No YOUTUBE_USERNAME defined in your .env or username is not set in options');
        }

        if ($username) {
            $this->userName = $username;
        } else {
            $this->userName = getenv('YOUTUBE_USERNAME');
        }
    }

    /**
     * Set the API Key
     */
    public function setApiKey($apiKey = false) {
        if (!$apiKey && !getenv('GOOGLE_API_KEY')) {
            throw new Exception('No GOOGLE_API_KEY defined in your .env or api_key is not set in options');
        }

        if ($apiKey) {
            $this->apiKey = $apiKey;
        } else {
            $this->apiKey = getenv('GOOGLE_API_KEY');
        }
    }

    /**
     * Set the Channel ID
     */
    public function setChannelID($channelId = false) {
        if (!$channelId && !getenv('YOUTUBE_CHANNELID')) {
            throw new Exception('No YOUTUBE_CHANNELID defined in your .env or channel_id is not set in options');
        }

        if ($channelId) {
            $this->channelID = $channelId;
        } else {
            $this->channelID = getenv('YOUTUBE_CHANNELID');
        }

    }

    /**
     * Get the user name
     */
    public function getUserName() {
        return $this->userName;
    }

    /**
     * Get the video API enpoint
     * @return String
     */
    public function getVideoApiEndpoint() {
        $args = [
            'key' => $this->apiKey,
            'channelId' => $this->channelID,
            'part' => 'snippet',
            'maxResults' => $this->maxResults,
            'order' => 'date',
            'type' => 'video'
        ];

        return $this->endpoint . 'search?' . http_build_query($args);
    }

    /**
     * Get the video statistics API enpoint
     * @return String
     */
    public function getVideoDetailsApiEndpoint($videoId) {
        $args = [
            'key' => $this->apiKey,
            'id' => $videoId,
            'part' => 'statistics,player'
        ];

        return $this->endpoint . 'videos?' . http_build_query($args);
    }

    /**
     * Get the YouTube channel statistics API enpoint
     * @return String
     */
    public function getChannelStatisticsApiEndpoint() {
        $args = [
            'key' => $this->apiKey,
            'id' => $this->channelID,
            'part' => 'statistics'
        ];

        return $this->endpoint . 'channels?' . http_build_query($args);
    }

    /**
     * Get the YouTube channel statistics API enpoint
     * @return String
     */
    public function getChannelProfileApiEndpoint() {
        $args = [
            'key' => $this->apiKey,
            'id' => $this->channelID,
            'part' => 'snippet'
        ];

        return $this->endpoint . 'channels?' . http_build_query($args);
    }

    /**
     * Get page profile array
     * @return Array
     */
    public function getProfileArray() {
        $array = [
            'username' => $this->getUserName()
        ];

        // Get data from API
        $endpointResult = @file_get_contents($this->getChannelProfileApiEndpoint());
        if ($endpointResult) {

            $object = json_decode($endpointResult);

            // Get image as data string
            $imageDataString = @file_get_contents($object->items[0]->snippet->thumbnails->high->url);

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
     * Get the defined channels latest videos
     * @return Array
     */
    public function getPostsArray() {
        // Get data from API
        $endpointResult = @file_get_contents($this->getVideoApiEndpoint());

        if (!$endpointResult) {
            throw new Exception('No data returned from endpoint');
        }

        // Convert latest video data to object
        // Dump this if you want to see full response from API
        $object = json_decode($endpointResult);

        // Create usable data array
        $array = [];
        foreach ($object->items as $key => $video) {

            $array[$key] = [
                'videoId' => $video->id->videoId,
                'link' => 'https://www.youtube.com/watch?v=' . $video->id->videoId,
                'title' => $video->snippet->title,
                'description' => $video->snippet->description,
                'date' => strtotime($video->snippet->publishedAt),
                'thumbnail' => [
                    'src' => $video->snippet->thumbnails->high->url,
                    'width' => $video->snippet->thumbnails->high->width,
                    'height' => $video->snippet->thumbnails->high->height
                ]
            ];

            $videoDetailsResult = @file_get_contents($this->getVideoDetailsApiEndpoint($video->id->videoId));

            if (!$videoDetailsResult) {
                continue;
            }

            $videoDetailsResultObject = json_decode($videoDetailsResult);

            foreach ($videoDetailsResultObject->items as $videoItem) {
                $array[$key]['views'] = isset($videoItem->statistics->viewCount) ? $videoItem->statistics->viewCount : 0;
                $array[$key]['likes'] = isset($videoItem->statistics->likeCount) ? $videoItem->statistics->likeCount : 0;
                $array[$key]['dislikes'] = isset($videoItem->statistics->dislikeCount) ? $videoItem->statistics->dislikeCount : 0;
                $array[$key]['favourites'] = isset($videoItem->statistics->favoriteCount) ? $videoItem->statistics->favoriteCount : 0;
                $array[$key]['comments'] = isset($videoItem->statistics->commentCount) ? $videoItem->statistics->commentCount : 0;
                $array[$key]['iframe'] = $videoItem->player->embedHtml;
            }
        }

        return $array;
    }

    /**
     * Get channel statistics
     * @return Array An array of usable data
     */
    public function getStatisticsArray() {
        // Get data from API
        $endpointResult = @file_get_contents($this->getChannelStatisticsApiEndpoint());
        if (!$endpointResult) {
            throw new Exception('No data returned from endpoint');
        }

        // Convert latest video data to object
        // Note: Print this if you want to see full response from API
        $object = json_decode($endpointResult);

        // Create usable data array
        $array = [
            'videos' => $object->items[0]->statistics->videoCount,
            'views' => $object->items[0]->statistics->viewCount,
            'subscribers' => $object->items[0]->statistics->subscriberCount
        ];

        return $array;
    }
}
