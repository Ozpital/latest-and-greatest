<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;

class YouTube extends LatestAndGreatest {
    /**
     * Initalise the YouTube API key variable
     * @var string
     */
    protected $apiKey;

    /**
     * Initalise the YouTube API key variable
     * @var string
     */
    protected $channelID;

    /**
     * @var string
     */
    protected $username;

    /**
     * Initalise the YouTube oAuth connection variable
     * @var object
     */
    protected $connection;

    /**
     * @var object
     */
    protected $userData;

    /**
     * Define the API url
     * @var string
     */
    protected $apiUrl = 'https://www.googleapis.com/youtube/v3/';

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->setCacheFilename('youtube.data.json');
    }

    /**
     * [getApiKey description]
     * @param string $apiSecret [description]
     */
    public function getApiUrl() {
        if (!isset($this->apiUrl) || empty($this->apiUrl)) {
            if ($this->debug) {
                trigger_error('No YouTube API url set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->apiUrl;
    }

    /**
     * [setApiKey description]
     * @param string $apiKey [description]
     */
    public function setApiKey(string $apiKey) {
        if (!isset($apiKey) || empty($apiKey)) {
            if ($this->debug) {
                trigger_error('YouTube API key cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->apiKey = $apiKey;
    }

    /**
     * [getApiKey description]
     * @param string $apiSecret [description]
     */
    public function getApiKey() {
        if (!isset($this->apiKey) || empty($this->apiKey)) {
            if ($this->debug) {
                trigger_error('No YouTube API key set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->apiKey;
    }

    /**
     * [setChannelID description]
     * @param string $apiKey [description]
     */
    public function setChannelID(string $channelID) {
        if (!isset($channelID) || empty($channelID)) {
            if ($this->debug) {
                trigger_error('YouTube channel ID cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->channelID = $channelID;
    }

    /**
     * [getChannelID description]
     * @param string $apiSecret [description]
     */
    public function getChannelID() {
        if (!isset($this->channelID) || empty($this->channelID)) {
            if ($this->debug) {
                trigger_error('No YouTube channel ID set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->channelID;
    }

    /**
     * [setUsername description]
     * @param string $username [description]
     */
    public function setUsername(string $username) {
        if (!isset($username) || empty($username)) {
            if ($this->debug) {
                trigger_error('YouTube username cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->username = $username;
    }

    /**
     * [getUsername description]
     * @param  string $username [description]
     * @return [type]           [description]
     */
    public function getUsername() {
        if (!isset($this->username) || empty($this->username)) {
            if ($this->debug) {
                trigger_error('No YouTube username set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->username;
    }

    /**
     * Get the video API url
     * @return string
     */
    public function getVideoApiUrl() {
        $apiUrl = $this->getApiUrl();
        $apiKey = $this->getApiKey();
        $channelID = $this->getChannelID();
        $maxResults = $this->getMaxResults();

        return $apiUrl . 'search?' . http_build_query([
            'key' => $apiKey,
            'channelId' => $channelID,
            'maxResults' => $maxResults,
            'order' => 'date',
            'type' => 'video',
            'part' => 'snippet'
        ]);
    }

    /**
     * Get the video details API url
     * @param  array  $videoIds An array of video ids
     * @return string an api endpoint
     */
    public function getVideoDetailsApiUrl(array $videoIds) {
        if (!isset($videoIds) || empty($videoIds)) {
            return NULL;
        }

        $apiUrl = $this->getApiUrl();
        $apiKey = $this->getApiKey();

        return $apiUrl . 'videos?' . http_build_query([
            'key' => $apiKey,
            'id' => implode(',', $videoIds),
            'part' => 'statistics,player'
        ]);
    }

    /**
     * Get the channel API url
     * @return string
     */
    public function getChannelApiUrl() {
        $apiUrl = $this->getApiUrl();
        $apiKey = $this->getApiKey();
        $channelID = $this->getChannelID();

        return $apiUrl . 'channels?' . http_build_query([
            'key' => $apiKey,
            'id' => $channelID,
            'part' => 'snippet,statistics'
        ]);
    }

    /**
     * [getProfile description]
     * @return [type] [description]
     */
    public function getProfile() {
        // get response from channel api ul
        $response = @file_get_contents($this->getChannelApiUrl());

        if (!isset($response) || empty($response)) {
            if ($this->debug) {
                trigger_error('No YouTube data returned from url', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // convert json response to array
        $data = json_decode($response);

        // initalise $profile return array
        $profile = [];

        // Get profile username
        $username = $this->getUsername();
        if (isset($username) && !empty($username)) {
            $profile['username'] = $username;
        }

        // Get channel title
        if (isset($data->items[0]->snippet->title)) {
            $profile['title'] = $data->items[0]->snippet->title;
        }

        // Get profile description
        if (isset($data->items[0]->snippet->description)) {
            $profile['description'] = $data->items[0]->snippet->description;
        }

        // Is there an image defined?
        if (isset($data->items[0]->snippet->thumbnails->default->url)) {
            // Get image as data string
            $imageDataString = @file_get_contents($data->items[0]->snippet->thumbnails->default->url);

            // Get image dimensions and mime type
            $imageData = getimagesizefromstring($imageDataString);

            // Add picture array to profile array
            if (isset($imageData[0]) && isset($imageData[1])) {
                $profile['picture'] = [
                    'width' => $imageData[0],
                    'height' => $imageData[1],
                    'src' => 'data:'. $imageData['mime'] .';base64,'. base64_encode($imageDataString)
                ];
            }
        }

        // Get total videos
        if (isset($data->items[0]->statistics->videoCount)) {
            $profile['videos'] = $data->items[0]->statistics->videoCount;
        }

        // Get total views
        if (isset($data->items[0]->statistics->viewCount)) {
            $profile['views'] = $data->items[0]->statistics->viewCount;
        }

        // Get total comments
        if (isset($data->items[0]->statistics->commentCount)) {
            $profile['comments'] = $data->items[0]->statistics->commentCount;
        }

        // Get total subscribers
        if (isset($data->items[0]->statistics->subscriberCount)) {
            $profile['subscribers'] = $data->items[0]->statistics->subscriberCount;
        }

        return $profile;
    }

    /**
     * [getPosts description]
     * @return [type] [description]
     */
    public function getPosts() {
        // get response from video api url
        $response = @file_get_contents($this->getVideoApiUrl());

        if (!isset($response) || empty($response)) {
            if ($this->debug) {
                trigger_error('No YouTube data returned from url', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // convert json response to array
        $data = json_decode($response);

        // Shrink array
        $items = array_slice($data->items, 0, $this->maxResults);

        // initalise $profile return array
        $posts = [];

        foreach ($items as $item) {
            $posts[$item->id->videoId] = [
                'id' => $item->id->videoId,
                'link' => 'https://www.youtube.com/watch?v=' . $item->id->videoId,
                'title' => $item->snippet->title,
                'description' => $item->snippet->description,
                'date' => strtotime($item->snippet->publishedAt),
                'thumbnail' => [
                    'src' => $item->snippet->thumbnails->high->url,
                    'width' => $item->snippet->thumbnails->high->width,
                    'height' => $item->snippet->thumbnails->high->height
                ]
            ];
        }

        // add video details/stats to posts array
        $posts = $this->addDetailsToPosts($posts);

        // remove named keys for consistency
        $posts = array_values($posts);

        return $posts;
    }

    /**
     * [addDetails description]
     * @param array $posts [description]
     */
    public function addDetailsToPosts(array $posts) {
        // Video IDs
        $videoIds = array_column($posts, 'id');

        if (!isset($videoIds) || empty($videoIds)) {
            return $posts;
        }

        // get response from video details api url
        $response = @file_get_contents($this->getVideoDetailsApiUrl($videoIds));

        if (!isset($response) || empty($response)) {
            if ($this->debug) {
                trigger_error('No YouTube data returned from url', E_USER_NOTICE);
                die();
            }

            return $posts;
        }

        // convert json response to array
        $data = json_decode($response);

        // if no items returned, return the posts array untouched
        if (!isset($data->items) || empty($data->items)) {
            return $posts;
        }

        // add detail data to posts array
        foreach ($data->items as $item) {
            $posts[$item->id]['views'] = isset($item->statistics->viewCount) ? $item->statistics->viewCount : 0;
            $posts[$item->id]['likes'] = isset($item->statistics->likeCount) ? $item->statistics->likeCount : 0;
            $posts[$item->id]['dislikes'] = isset($item->statistics->dislikeCount) ? $item->statistics->dislikeCount : 0;
            $posts[$item->id]['favourites'] = isset($item->statistics->favoriteCount) ? $item->statistics->favoriteCount : 0;
            $posts[$item->id]['comments'] = isset($item->statistics->commentCount) ? $item->statistics->commentCount : 0;
            $posts[$item->id]['iframe'] = $item->player->embedHtml;
        }

        return $posts;
    }
}
