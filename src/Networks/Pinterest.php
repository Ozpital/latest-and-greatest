<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use DOMDocument;
use Exception;

/**
 * Extend Latest And Greatest To Support Pinterest
 */
class Pinterest extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--pinterest.json';

    /**
     * @var String
     */
    protected $userName;

    /**
     * Define the API end point
     * @var String
     */
    protected $endpoint = 'http://pinterest.com/';


    /**
     * @var String
     */
    protected $userData;

    /**
     * This fires when instance created
     */
    public function __construct($options = []) {
        try {
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
        if (!$username && !getenv('PINTEREST_USERNAME')) {
            throw new Exception('No PINTEREST_USERNAME defined in your .env or username is not set in options');
        }

        if ($username) {
            $this->userName = $username;
        } else {
            $this->userName = getenv('PINTEREST_USERNAME');
        }
    }

    /**
     * Get the user name
     */
    public function getUserName() {
        return $this->userName;
    }

    /**
     * Get the endpoint
     * @return String
     */
    public function getUserEndpoint() {
        return $this->endpoint . $this->userName . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the endpoint
     * @return String
     */
    public function getUserData() {
        if (!$this->userData) {
            $this->userData = @get_meta_tags($this->getUserEndpoint());
        }

        return $this->userData;
    }

    /**
     * Get page profile array
     * @return Array
     */
    public function getProfileArray() {
        $array = [
            'username' => $this->getUserName()
        ];

        $endpointResult = $this->getUserData();
        if ($endpointResult) {
            // Get image as data string
            $imageDataString = @file_get_contents($endpointResult['og:image']);

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
     * Get statistics array
     * @return Array
     */
    public function getStatisticsArray() {
        // Get meta data from page
        $endpointResult = $this->getUserData();
        if (!$endpointResult) {
            throw new Exception('No meta data returned from endpoint');
        }

        // Create usable statistics array
        $array = [];
        if ($endpointResult['pinterestapp:followers']) {
            $array['followers'] = $endpointResult['pinterestapp:followers'];
        }
        if ($endpointResult['pinterestapp:following']) {
            $array['following'] = $endpointResult['pinterestapp:following'];
        }
        if ($endpointResult['pinterestapp:pins']) {
            $array['pins'] = $endpointResult['pinterestapp:pins'];
        }

        return $array;
    }

    /**
     * Get posts
     * @return Array
     */
    public function getPostsArray() {
        // Check if required extensions loaded
        if (!extension_loaded('xml')) {
            throw new Exception('Please enable the php xml extension');
        }

        // Check if required extension loaded
        if (!extension_loaded('dom')) {
            throw new Exception('Please enable the php dom extension');
        }

        // Get data from API
        $endpointResult = @file_get_contents($this->getUserEndpoint() . 'feed.rss');
        if (!$endpointResult) {
            throw new Exception('No data returned from endpoint');
        }

        // Convert the xml string into xml
        $feedXml = simplexml_load_string($endpointResult);

        // Convert to array
        $feedObject = json_decode(json_encode($feedXml));

        // Get pins
        $pins = $feedObject->channel->item;

        // Shrink array
        $latestPins = array_slice($pins, 0, $this->maxResults);

        // Create usable array
        $array = [];
        foreach ($latestPins as $pin) {
            // Initalise dom document
            $doc = new DOMDocument();

            // set error level
            $internalErrors = libxml_use_internal_errors(true);

            // Parse html string from pinterest
            $content = $doc->loadHTML(mb_convert_encoding($pin->description, 'HTML-ENTITIES', 'UTF-8'));

            // Get img element
            $img = $doc->getElementsByTagName('img')[0];

            // Get img dimensions
            $imgDimensions = getimagesize($img->getAttribute('src'));

            // Get description
            $description = $doc->getElementsByTagName('p')[1];

            // Restore error level
            libxml_use_internal_errors($internalErrors);

            // Populate array
            $array[] = [
                'title' => $pin->title,
                'date' => strtotime($pin->pubDate),
                'link' => $pin->link,
                'description' => $description->textContent,
                'media' => [
                    'thumbnail' => $img->getAttribute('src'),
                    'width' => $imgDimensions[0],
                    'height' => $imgDimensions[1],
                ]
            ];
        }

        // Remove named keys
        $array = array_values($array);

        return $array;
    }
}
