<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use InstagramScraper\Instagram as Scraper;

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
        // do scrape if not already done
        if (!isset($this->scrape) || empty($this->scrape)) {
            $username = $this->getUsername();

            $scraper = new Scraper();

            $scrape = $scraper->getAccount($username);

            if (!isset($scrape) || empty($scrape)) {
                return NULL;
            }

            $this->scrape = $scrape;
        }

        return $this->scrape;
    }

    /**
     * [getProfile description]
     * @return [type] [description]
     */
    public function getProfile() {
        $scrape = $this->scrape();

        // initalise $profile return array
        $profile = [];

        // Get profile id
        $profile_id = $scrape->getId();
        if (isset($profile_id)) {
            $profile['id'] = $profile_id;
        }

        // Get profile username
        $profile_username = $scrape->getUsername();
        if (isset($profile_username)) {
            $profile['username'] = $profile_username;
        }

        // Get profile name
        $profile_fullname = $scrape->getFullname();
        if (isset($profile_fullname)) {
            $profile['name'] = $profile_fullname;
        }

        // Get profile description
        $profile_biography = $scrape->getBiography();
        if (isset($profile_biography)) {
            $profile['description'] = $profile_biography;
        }

        // Get media count
        $profile_mediacount = $scrape->getMediaCount();
        if (isset($profile_mediacount)) {
            $profile['media_count'] = $profile_mediacount;
        }

        // Get follows count
        $profile_follows = $scrape->getFollowsCount();
        if (isset($profile_follows)) {
            $profile['follows'] = $profile_follows;
        }

        // Get followed by count
        $profile_followedby = $scrape->getFollowedByCount();
        if (isset($profile_followedby)) {
            $profile['followers'] = $profile_followedby;
        }

        // Get profile picture
        $profile_pic = $scrape->getProfilePicUrlHd();
        if (isset($profile_pic)) {
            // Get image as data string
            $imageDataString = @file_get_contents($profile_pic);

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
        // get instagram scrape
        $scrape = $this->scrape();

        // start posts array
        $posts = [];

        $medias = $scrape->getMedias();
        if (!isset($medias) || empty($medias)) {
            return $posts;
        }

        $items = array_slice($medias, 0, $this->getMaxResults());

        foreach ($items as $item) {
            // add id
            $id = $item->getId();
            $timestamp = $item->getCreatedTime();
            $shortcode = $item->getShortcode();

            if (
                !isset($id) || empty($id)
                || !isset($timestamp) || empty($timestamp)
                || !isset($shortcode) || empty($shortcode)
            ) {
                continue;
            }

            $posts[$id] = [
                'id' => $id,
                'date' => $timestamp,
                'url' => 'https://www.instagram.com/p/' . $shortcode
            ];

            // get media likes
            $likes = $item->getLikesCount();
            if (isset($likes) && !empty($likes)) {
                $posts[$id]['likes'] = $likes;
            }

            // add caption
            $caption = $item->getCaption();
            if (isset($caption) && !empty($caption)) {
                $posts[$id]['text'] = $caption;
            }

            // add media
            $image = $item->getImageHighResolutionUrl();
            if (isset($image) && !empty($image)) {

                // Get image as data string
                $imageDataString = @file_get_contents($image);

                // Get image dimensions and mime type
                $imageData = getimagesizefromstring($imageDataString);

                // Build relevant array
                $posts[$id]['media'] = [
                    'width' => $imageData[0],
                    'height' => $imageData[0],
                    'src' => 'data:'. $imageData['mime'] .';base64,'. base64_encode($imageDataString)
                ];
            }
        }

        // Remove named keys
        $posts = array_values($posts);

        return $posts;
    }
}
