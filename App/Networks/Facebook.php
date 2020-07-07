<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Facebook\Facebook as FB;

class Facebook extends LatestAndGreatest {
    /**
     * Initalise the Facebook page ID variable
     * @var string
     */
    protected $pageId;

    /**
     * Initalise the Facebook API key variable
     * @var string
     */
    protected $appId;

    /**
     * Initalise the Facebook API secret variable
     * @var string
     */
    protected $appSecret;

    /**
     * Initalise the Facebook access token variable
     * @var string
     */
    protected $accessToken;

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->setCacheFilename('facebook.data.json');
        $this->setAccessTokenFilename('facebook.token.json');
    }

    /**
     * [setPageId description]
     * @param string $pageId [description]
     */
    public function setPageId(string $pageId) {
        if (!isset($pageId) || empty($pageId)) {
            if ($this->debug) {
                trigger_error('Facebook Page ID cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->pageId = $pageId;
    }

    /**
     * [getPageId description]
     * @param string $pageId [description]
     */
    public function getPageId() {
        if (!isset($this->pageId) || empty($this->pageId)) {
            return 'me';
        }

        return $this->pageId;
    }

    /**
     * [setApiID description]
     * @param string $appId [description]
     */
    public function setAppID(string $appId) {
        if (!isset($appId) || empty($appId)) {
            if ($this->debug) {
                trigger_error('Facebook App ID cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->appId = $appId;
    }

    /**
     * [getApiID description]
     * @param string $appId [description]
     */
    public function getAppID() {
        if (!isset($this->appId) || empty($this->appId)) {
            if ($this->debug) {
                trigger_error('No Facebook App ID set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->appId;
    }

    /**
     * [setApiSecret description]
     * @param string $appSecret [description]
     */
    public function setAppSecret(string $appSecret) {
        if (!isset($appSecret) || empty($appSecret)) {
            if ($this->debug) {
                trigger_error('Facebook App secret cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->apiSecret = $appSecret;
    }

    /**
     * [setApiSecret description]
     * @param string $appSecret [description]
     */
    public function getAppSecret() {
        if (!isset($this->apiSecret) || empty($this->apiSecret)) {
            if ($this->debug) {
                trigger_error('No Facebook App secret set', E_USER_NOTICE);
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
                trigger_error('Facebook API access token cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->accessToken = $accessToken;
    }

    /**
     * [getDefinedAccessToken description]
     * @return [type] [description]
     */
    public function getDefinedAccessToken() {
        if (!isset($this->accessToken) || empty($this->accessToken)) {
            if ($this->debug) {
                trigger_error('No Facebook API access token set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->accessToken;
    }

    /**
     * [hasStoredAccessToken description]
     * @return [type] [description]
     */
    public function getAccessToken() {
        $cacheDirectory = $this->getCacheDirectory();
        $accessTokenFilename = $this->getAccessTokenFilename();

        // refresh stored access token
        $this->refreshAccessToken();

        // get stored access token
        $accessTokenJson = file_get_contents($cacheDirectory . $accessTokenFilename);
        $accessTokenData = json_decode($accessTokenJson);

        if (!isset($accessTokenData->access_token) || empty($accessTokenData->access_token)) {
            if ($this->debug) {
                trigger_error('No stored Facebook access token found', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $accessTokenData->access_token;
    }

    /**
     * [refreshAccessToken description]
     * @return [type] [description]
     */
    public function refreshAccessToken() {
        $cacheDirectory = $this->getCacheDirectory();
        $accessTokenFilename = $this->getAccessTokenFilename();

        if (!file_exists($cacheDirectory . $accessTokenFilename)) {
            touch($cacheDirectory . $accessTokenFilename);
        }

        $accessTokenJson = file_get_contents($cacheDirectory . $accessTokenFilename);
        $accessTokenData = json_decode($accessTokenJson);

        $existingAccessToken = $this->getDefinedAccessToken();
        if (isset($accessTokenData) && !empty($accessTokenData)) {
            $existingAccessToken = $accessTokenData->access_token;

            // if current time more than 7 days before expiry time, use exisiting
            if (strtotime('now') < strtotime('-7 days', ($accessTokenData->refresh_time + $accessTokenData->expires_in))) {
                return false;
            }
        }

        $connection = $this->getConnection();

        if (!isset($connection) || empty($connection)) {
            if ($this->debug) {
                trigger_error('No Facebook connection found', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        $appId = $this->getAppID();
        $appSecret = $this->getAppSecret();

        // populate basic fields
        $fields = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $existingAccessToken
        ];

        // get profile data
        $token_response = $connection->get('/oauth/access_token?'.http_build_query($fields, '', '&'), $existingAccessToken);
        $token_json = $token_response->getBody();
        if (!isset($token_json) || empty($token_json)) {
            return NULL;
        }

        // convert to data object
        $data = json_decode($token_json, true);

        // add expires_at
        $data['refresh_time'] = strtotime('now');

        // Convert to json and save to cache file
        if (!file_put_contents($this->getCacheDirectory() .  $this->getAccessTokenFilename(), json_encode($data))) {
            if ($this->debug) {
                trigger_error('Token cache did not successfully update', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return true;
    }

    /**
     * [createConnection description]
     * @return [type] [description]
     */
    public function createConnection() {
        $appId = $this->getAppID();
        $appSecret = $this->getAppSecret();

        $connection = new Fb([
            'app_id' => $appId,
            'app_secret' => $appSecret
        ]);

        $this->setConnection($connection);
    }

    /**
     * [setConnection description]
     * @param object $connection [description]
     */
    public function setConnection(object $connection) {
        if (!isset($connection) || empty($connection)) {
            if ($this->debug) {
                trigger_error('Facebook connection cannot be empty', E_USER_NOTICE);
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
                trigger_error('No Facebook connection found', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // get access token
        $accessToken = $this->getAccessToken();

        // get page id
        $pageId = $this->getPageid();

        // populate basic fields
        $fields = ['id', 'name', 'about', 'location'];

        // if page, add addition fields to request
        if ($pageId !== 'me') {
            $fields = array_merge($fields, [
                'fan_count'
            ]);
        }

        // get profile data
        $profile_response = $connection->get('/'.$pageId.'?fields='.implode(',', $fields), $accessToken);
        $profile_json = $profile_response->getBody();
        if (!isset($profile_json) || empty($profile_json)) {
            return NULL;
        }

        //convert to data object
        $data = json_decode($profile_json);

        // initalise $profile return array
        $profile = [];

        // Get profile name
        if (isset($data->name)) {
            $profile['name'] = $data->name;
        }

        // Get profile about
        if (isset($data->about)) {
            $profile['about'] = $data->about;
        }

        // Get profile location
        $location = [];
        if (isset($data->location->city) && !empty($data->location->city)) {
            $location[] = $data->location->city;
        }
        if (isset($data->location->city) && !empty($data->location->city)) {
            $location[] = $data->location->country;
        }
        if (isset($location) && !empty($location)) {
            $profile['location'] = implode(', ', $location);
        }

        // Get profile followers
        if (isset($data->fan_count)) {
            $profile['likes'] = $data->fan_count;
        }

        // Get profile picture
        $picture_response = $connection->get('/'.$pageId.'/picture?redirect=0&type=large', $accessToken);
        $picture_json = $picture_response->getBody();
        if (!isset($picture_json) || empty($picture_json)) {
            return NULL;
        }

        // merge picture data into $data array
        $picture_data = json_decode($picture_json);

        if (isset($picture_data->data->url)) {
            // Get image as data string
            $imageDataString = @file_get_contents($picture_data->data->url);

            // Get image dimensions and mime type
            $imageData = getimagesizefromstring($imageDataString);

            // Build relevant array
            $profile['picture'] = [
                'width' => $imageData[0],
                'height' => $imageData[0],
                'src' => 'data:'. $imageData['mime'] .';base64,'. base64_encode($imageDataString)
            ];
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
                trigger_error('No Facebook connection found', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // get access token
        $accessToken = $this->getAccessToken();

        // get page id
        $pageId = $this->getPageid();

        // get post data
        $posts_response = $connection->get('/'.$pageId.'/posts?fields=id,message,created_time,attachments', $accessToken);
        $posts_json = $posts_response->getBody();
        if (!isset($posts_json) || empty($posts_json)) {
            return NULL;
        }

        //convert to data object
        $data = json_decode($posts_json);

        if (isset($data->data)) {
            $data = $data->data;
        }

        if (empty($data)) {
            return NULL;
        }

        // Shrink array
        $items = array_slice($data, 0, $this->getMaxResults());

        // Create usable data array
        $posts = [];
        foreach ($items as $item) {
            $posts[$item->id] = [];

            // add id and url to post
            if (isset($item->id) && !empty($item->id)) {
                $posts[$item->id]['id'] = $item->id;
                $posts[$item->id]['url'] = 'https://www.facebook.com/' . $item->id;
            }

            // add description text
            if (isset($item->message) && !empty($item->message)) {
                $posts[$item->id]['text'] = $item->message;
            }

            // add date
            if (isset($item->created_time) && !empty($item->created_time)) {
                $posts[$item->id]['date'] = strtotime($item->created_time);
            }

            // Is there media attached to the post? add it
            if (isset($item->attachments->data[0]->media->image) && !empty($item->attachments->data[0]->media->image)) {
                $posts[$item->id]['media'] = [
                    'thumbnail' => $item->attachments->data[0]->media->image->src,
                    'width' => $item->attachments->data[0]->media->image->width,
                    'height' => $item->attachments->data[0]->media->image->height
                ];
            }
        }

        // Remove named keys
        $posts = array_values($posts);

        return $posts;
    }
}
