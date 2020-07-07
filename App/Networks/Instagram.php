<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;

class Instagram extends LatestAndGreatest {
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $scrape;

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->setCacheFilename('instagram.data.json');
    }

    /**
     * [setUsername description]
     * @param string $userID [description]
     */
    public function setUserID(string $userID) {
        if (!isset($userID) || empty($userID)) {
            if ($this->debug) {
                trigger_error('Instagram userID cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->userID = $userID;
    }

    /**
     * [getUsername description]
     * @param  string $userID [description]
     * @return [type]           [description]
     */
    public function getUserID() {
        if (!isset($this->userID) || empty($this->userID)) {
            if ($this->debug) {
                trigger_error('No Instagram userID set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->userID;
    }

    /**
     * [setUsername description]
     * @param string $userID [description]
     */
    public function setUsername(string $username) {
        if (!isset($username) || empty($username)) {
            if ($this->debug) {
                trigger_error('Instagram username cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->username = $username;
    }

    /**
     * [getUsername description]
     * @param  string $userID [description]
     * @return [type]           [description]
     */
    public function getUsername() {
        if (!isset($this->username) || empty($this->username)) {
            if ($this->debug) {
                trigger_error('No Instagram username set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->username;
    }

    /**
     * [scrapeInstagram description]
     * @param  string $username [description]
     * @return [type]           [description]
     */
    public function scrape() {
        // get username
        $username = $this->getUsername();

        // do scrape if not already done
        if (!isset($this->scrape) || empty($this->scrape)) {
            $source = file_get_contents('http://instagram.com/' . $username);
            $re = '/<script[^>]*>(.*?)<\/script>/m';
            preg_match_all($re, $source, $matches, PREG_SET_ORDER, 0);

            // loop through matches
            foreach ($matches as $match) {
                // we only need to use the second array item
                if (empty($match[1])) {
                    continue;
                }

                // find required script content
                if (substr($match[1], 0, 18) !== 'window._sharedData') {
                    continue;
                }

                // convert script content (which should be json) to usable data
                $json = substr(substr($match[1], 21), 0, -1);
                $data = json_decode($json);

                // check for valid node
                if (!isset($data->entry_data->ProfilePage[0]->graphql->user)) {
                    continue;
                }

                // push node into global scrape
                $this->scrape = $data->entry_data->ProfilePage[0]->graphql->user;
            }
        }

        return $this->scrape;
    }

    /**
     * [getProfile description]
     * @return [type] [description]
     */
    public function getProfile() {
        $data = $this->scrape();

        // initalise $profile return array
        $profile = [];

        // Get profile username
        if (isset($data->username)) {
            $profile['username'] = $data->username;
        }

        // Get profile name
        if (isset($data->full_name)) {
            $profile['name'] = $data->full_name;
        }

        // Get profile description
        if (isset($data->biography)) {
            $profile['description'] = $data->biography;
        }

        // Get media count
        if (isset($data->edge_owner_to_timeline_media->count)) {
            $profile['media_count'] = $data->edge_owner_to_timeline_media->count;
        }

        // Get profile picture
        if (isset($data->profile_pic_url_hd)) {
            // Get image as data string
            $imageDataString = @file_get_contents($data->profile_pic_url_hd);

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
        if (isset($data->edge_followed_by->count)) {
            $profile['followers'] = $data->edge_followed_by->count;
        }

        return $profile;
    }

    /**
     * [getPosts description]
     * @return [type] [description]
     */
    public function getPosts() {
        // get instagram scrape
        $scrape = $this->scrape();

        // start posts array
        $posts = [];

        if (!isset($scrape->edge_owner_to_timeline_media->edges) || empty($scrape->edge_owner_to_timeline_media->edges)) {
            return $posts;
        }

        $items = array_slice($scrape->edge_owner_to_timeline_media->edges, 0, $this->getMaxResults());

        foreach ($items as $item) {
            if (!isset($item->node) || empty($item->node)) {
                continue;
            }

            // add id
            $posts[$item->node->id] = [
                'id' => $item->node->id,
                'date' => $item->node->taken_at_timestamp,
                'url' => 'https://www.instagram.com/p/' . $item->node->shortcode
            ];

            // add caption
            if (
                isset($item->node->edge_media_to_caption->edges[0]->node->text)
                && !empty($item->node->edge_media_to_caption->edges[0]->node->text)
            ) {
                $posts[$item->node->id]['text'] = $item->node->edge_media_to_caption->edges[0]->node->text;
            }

            // add media
            if (
                isset($item->node->display_url)
                && !empty($item->node->display_url)
            ) {
                $posts[$item->node->id]['media'] = [
                    'thumbnail' => $item->node->display_url,
                    'width' => $item->node->dimensions->width,
                    'height' => $item->node->dimensions->height
                ];
            }

            // get media likes
            $posts[$item->node->id]['likes'] = $item->node->edge_liked_by->count;
        }

        // Remove named keys
        $posts = array_values($posts);

        return $posts;
    }
}
