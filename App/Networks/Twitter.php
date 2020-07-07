<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter extends LatestAndGreatest {
    /**
     * Initalise the Twitter API key variable
     * @var string
     */
    protected $apiKey;

    /**
     * Initalise the Twitter API secret variable
     * @var string
     */
    protected $apiSecret;

    /**
     * Initalise the Twitter access token variable
     * @var string
     */
    protected $accessToken;

    /**
     * Initalise the Twitter access token secret variable
     * @var string
     */
    protected $accessTokenSecret;

    /**
     * Initalise the Twitter oAuth connection variable
     * @var object
     */
    protected $connection;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var object
     */
    protected $userData;

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->setCacheFilename('twitter.data.json');
    }

    /**
     * [setApiKey description]
     * @param string $apiKey [description]
     */
    public function setApiKey(string $apiKey) {
        if (!isset($apiKey) || empty($apiKey)) {
            if ($this->debug) {
                trigger_error('Twitter API key cannot be empty', E_USER_NOTICE);
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
                trigger_error('No Twitter API key set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->apiKey;
    }

    /**
     * [setApiSecret description]
     * @param string $apiSecret [description]
     */
    public function setApiSecret(string $apiSecret) {
        if (!isset($apiSecret) || empty($apiSecret)) {
            if ($this->debug) {
                trigger_error('Twitter API secret cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->apiSecret = $apiSecret;
    }

    /**
     * [setApiSecret description]
     * @param string $apiSecret [description]
     */
    public function getApiSecret() {
        if (!isset($this->apiSecret) || empty($this->apiSecret)) {
            if ($this->debug) {
                trigger_error('No Twitter API secret set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->apiSecret;
    }

    /**
     * [setAccessToken description]
     * @param string $accessToken [description]
     */
    public function setAccessToken(string $accessToken) {
        if (!isset($accessToken) || empty($accessToken)) {
            if ($this->debug) {
                trigger_error('Twitter API access token cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->accessToken = $accessToken;
    }

    /**
     * [getAccessToken description]
     * @return [type] [description]
     */
    public function getAccessToken() {
        if (!isset($this->accessToken) || empty($this->accessToken)) {
            if ($this->debug) {
                trigger_error('No Twitter API access token set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->accessToken;
    }

    /**
     * [setAccessTokenSecret description]
     * @param string $accessTokenSecret [description]
     */
    public function setAccessTokenSecret(string $accessTokenSecret) {
        if (!isset($accessTokenSecret) || empty($accessTokenSecret)) {
            if ($this->debug) {
                trigger_error('Twitter API access token secret cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->accessTokenSecret = $accessTokenSecret;
    }

    /**
     * [getAccessTokenSecret description]
     * @return [type] [description]
     */
    public function getAccessTokenSecret() {
        if (!isset($this->accessTokenSecret) || empty($this->accessTokenSecret)) {
            if ($this->debug) {
                trigger_error('No Twitter API access token secret set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->accessTokenSecret;
    }

    /**
     * [setUsername description]
     * @param string $username [description]
     */
    public function setUsername(string $username) {
        if (!isset($username) || empty($username)) {
            if ($this->debug) {
                trigger_error('Twitter username cannot be empty', E_USER_NOTICE);
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
                trigger_error('No Twitter username set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->username;
    }

    /**
     * [createConnection description]
     * @return [type] [description]
     */
    public function createConnection() {
        $apiKey = $this->getApiKey();
        $apiSecret = $this->getApiSecret();
        $accessToken = $this->getAccessToken();
        $accessTokenSecret = $this->getAccessTokenSecret();

        $connection = new TwitterOAuth(
            $apiKey,
            $apiSecret,
            $accessToken,
            $accessTokenSecret
        );

        $this->setConnection($connection);
    }

    /**
     * [setConnection description]
     * @param object $connection [description]
     */
    public function setConnection(object $connection) {
        if (!isset($connection) || empty($connection)) {
            if ($this->debug) {
                trigger_error('Twitter connection cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->connection = $connection;
    }

    /**
     * [getConnection description]
     * @return [type] [description]
     */
    public function getConnection() {
        if (!isset($this->connection) || empty($this->connection)) {
            // Create connection
            $this->createConnection();
        }

        return $this->connection;
    }

    /**
     * [getUserProfile description]
     * @return [type] [description]
     */
    public function getProfile() {
        $connection = $this->getConnection();

        if (!isset($connection) || empty($connection)) {
            if ($this->debug) {
                trigger_error('No Twitter connection found', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        $data = $connection->get('account/verify_credentials', [
            'include_rts' => false,
            'exclude_replies' => true,
            'include_entities' => false,
            'skip_status' => true,
            'include_email' => false
        ]);
        if (!isset($data) || empty($data)) {
            if ($this->debug) {
                trigger_error('No Twitter data returned', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // initalise $profile return array
        $profile = [];

        // Get profile username
        if (isset($data->screen_name)) {
            $profile['username'] = $data->screen_name;
        }

        // Get profile name
        if (isset($data->name)) {
            $profile['name'] = $data->name;
        }

        // Get profile description
        if (isset($data->description)) {
            $profile['description'] = $data->description;
        }

        // Get profile location
        if (isset($data->location)) {
            $profile['location'] = $data->location;
        }

        // Get profile picture
        if (isset($data->profile_image_url_https)) {
            // Get image as data string
            $imageDataString = @file_get_contents($data->profile_image_url_https);

            // Get image dimensions and mime type
            $imageData = getimagesizefromstring($imageDataString);

            // Build relevant array
            $profile['picture'] = [
                'width' => $imageData[0],
                'height' => $imageData[0],
                'src' => 'data:'. $imageData['mime'] .';base64,'. base64_encode($imageDataString)
            ];
        }

        // Get profile followers
        if (isset($data->followers_count)) {
            $profile['followers'] = $data->followers_count;
        }

        // Get profile friends count
        if (isset($data->friends_count)) {
            $profile['friends'] = $data->friends_count;
        }

        // Get profile favourites count
        if (isset($data->favourites_count)) {
            $profile['favourites'] = $data->favourites_count;
        }

        return $profile;
    }

    /**
     * [getPosts description]
     * @return [type] [description]
     */
    public function getPosts() {
        $connection = $this->getConnection();

        if (!isset($connection) || empty($connection)) {
            if ($this->debug) {
                trigger_error('No Twitter connection found', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // Fetch a bigger list of tweets than we actually need.
        //
        // We do this as if we were to fetch 2 ('count' => 2), it doesn't
        // guarentee to always return 2 results.
        // It would appear the api fetches the desired amount first then
        // filters the result with the defined parameters, thus removing some
        // results from the desired amount.
        $data = $this->connection->get('statuses/user_timeline', [
            'include_rts' => false,
            'exclude_replies' => true,
            'include_entities' => false,
            'tweet_mode' => 'extended',
            'count' => 40 // We deliberatly get more than required here
        ]);

        if (!isset($data) || empty($data)) {
            if ($this->debug) {
                trigger_error('No Twitter data returned', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // Shrink array
        $items = array_slice($data, 0, $this->maxResults);

        // Create usable data array
        $posts = [];
        foreach ($items as $item) {
            $posts[$item->id] = [
                'id' => $item->id,
                // 'link' =>'http://twitter.com/' . $item->user->screen_name . '/status/' . $item->id,
                'text' => $item->full_text,
                'date' => strtotime($item->created_at),
                'url' => 'https://twitter.com/statuses/' . $item->id
            ];

            // Is there media attached to the post?
            if (isset($item->extended_entities->media[0])) {
                $posts[$item->id]['media'] = [
                    'thumbnail' => $item->extended_entities->media[0]->media_url_https . ':large',
                    'width' => $item->extended_entities->media[0]->sizes->large->w,
                    'height' => $item->extended_entities->media[0]->sizes->large->h
                ];
            }

            // Get counts
            $posts[$item->id]['favourites'] = $item->favorite_count;
            $posts[$item->id]['retweets'] = $item->retweet_count;
        }

        // Remove named keys
        $posts = array_values($posts);

        return $posts;
    }
}
