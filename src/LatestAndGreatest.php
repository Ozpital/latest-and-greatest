<?php

namespace LatestAndGreatest;

use Exception;

/**
 * Latest And Greatest
 *
 * Connect to your social media and grab information to use in your site.
 * Including:
 * - Latest Post(s)
 * - Follower/Subscriber count
 */
class LatestAndGreatest {
    /**
     * Default cache state
     * @var string
     */
    protected $cacheEnabled = true;

    /**
     * Default output data type, default to json object type
     * @var string
     */
    protected $objectOutput = true;

    /**
     * Default cache directory
     * @var string
     */
    protected $cacheDirectory = './cache/';

    /**
     * Initalise Cache Filename
     * @var string
     */
    protected $cacheFilename;

    /**
     * Default cache duration in seconds
     * @var integer
     */
    protected $cacheDuration = (60 * 60);

    /**
     * Default max results amount
     * @var integer
     */
    protected $maxResults = 1;

    /**
     * Initalise the data variable
     * @var array
     */
    protected $data;

    public function __construct($options = [])
    {
        // If max results defined
        if (isset($options['max'])) {
            $this->maxResults = $options['max'];
        }

        // If cache state is defined
        if (isset($options['cache'])) {
            $this->cacheEnabled = $options['cache'];
        }

        // If cache directory defined
        if (isset($options['cacheDir'])) {
            $this->cacheDirectory = $options['cacheDir'];
        }

        if (isset($options['objectOutput'])) {
            $this->objectOutput = $options['objectOutput'];
        }
        // Populate data variable
        $this->data = $this->fetchData();
    }

    /**
     * Initalise the specific data set
     * @deprecated class initialised in construct.
     */
    public function init() {
    }

    /**
     * Check is a cache update is required
     * @return boolean
     */
    protected function isUpdateRequired() {
        // Does the file exist?
        if (!file_exists($this->getCacheDirectory() . $this->cacheFileName)) {
            // Make file
            touch($this->getCacheDirectory() . $this->cacheFileName);
            return true;
        }

        // Is it empty?
        if (!filesize($this->getCacheDirectory() . $this->cacheFileName)) {
           return true;
        }

        // Is the file age over the threshold?
        if ((time() - $this->getCacheDuration()) > filemtime($this->getCacheDirectory() . $this->cacheFileName)) {
            return true;
        }

        return false;
    }

    /**
     * Get cached data
     * @return object
     */
    protected function getCachedData(){
        return json_decode(file_get_contents($this->getCacheDirectory() . $this->cacheFileName), true);
    }

    /**
     * Update the cache file
     */
    protected function updateCache($data = []) {
        // Convert to json and save to cache file
        file_put_contents($this->getCacheDirectory() . $this->cacheFileName, json_encode($data, JSON_FORCE_OBJECT));
    }

    /**
     * Delete the desired cache
     */
    protected function deleteCache() {
        // Does the file exist?
        if (file_exists($this->getCacheDirectory() . $this->cacheFileName)) {
            // Delete the file
            unlink($this->getCacheDirectory() . $this->cacheFileName);
        }
        // Empty data variable
        if ($this->data) {
            unset($this->data);
        }
    }

    protected function fetchData() {
        $data = [
            'profile' => [],
            'statistics' => [],
            'latest' => []
        ];

        if ($this->cacheEnabled && !$this->isUpdateRequired()) {
            $cachedData = $this->getCachedData();
            // Get current stats
            $data['profile'] = $this->convertOutputData($cachedData['profile']);
            // Get current stats
            $data['statistics'] = $this->convertOutputData($cachedData['statistics']);
            // Get latest data
            $data['latest'] = $this->convertOutputData($cachedData['latest']);
        } else {
            // Get current stats
            $data['profile'] = $this->convertOutputData($this->getProfileArray());
            // Get current stats
            $data['statistics'] = $this->convertOutputData($this->getStatisticsArray());
            // Get latest data
            $data['latest'] = $this->convertOutputData($this->getPostsArray());
            if ($this->cacheEnabled) {
                $this->updateCache($data);
            }
        }

        return $data;
    }

    /**
     * Get proffered data type
     * Implement backwards compatibility
     * @return mixed The converted data
     */
    protected function convertOutputData($array){
        if($this->objectOutput){
            $array = $this->arrayToObject($array);
        }
        return $array;
    }
    /**
     * convert array to object
     * @return object The stored data
     */
    protected function arrayToObject($array){
        // Json conversion enforces recersive conversion
        return json_decode(json_encode($array), FALSE);
    }
    /**
     * Get the cached data
     * @return array The stored data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get profile
     * @return array
     */
    public function getProfile() {
        return $this->data['profile'];
    }

    /**
     * Get page/profile stats
     * @return array
     */
    public function getStats() {
        return $this->data['statistics'];
    }

    /**
     * Get the latest posts
     * @return array
     */
    public function getLatest() {
        return $this->data['latest'];
    }


    /**
     * Get cache directory
     * @return string The currently defined cache directory
     * @throws Exception Invalid cache directory
     */
    public function getCacheDirectory() {
        if (!is_dir($this->cacheDirectory)) {
            throw new Exception('Invalid cache directory');
        }

        return $this->cacheDirectory;
    }

    /**
     * Set the cache file name
     * @param String $filename A valid string that can be used as a filename for the cache
     */
    public function setCacheFilename($filename) {
        $this->cacheFilename = $filename;
    }

    /**
     * Get the cache filename
     * @return string The defined filename for the current cache
     * @throws Exception No cache filename set
     */
    public function getCacheFilename() {
        if (!$this->cacheFilename) {
            throw new Exception('No cache filename set');
        }

        return $this->cacheFilename;
    }

    /**
     * Update the default duration period
     * @param integer $duration A number in seconds
     * @throws Exception Invalid cache duration
     */
    public function setCacheDuration($duration) {
        if (!is_int($duration)) {
            throw new Exception('Invalid cache duration');
        }

        $this->cacheDuration = $duration;
    }

    /**
     * Set the max results your want to fetch from the desired API
     * @param integer $amount
     * @throws Exception Invalid number
     */
    public function setMaxResults($amount) {
        if (!is_int($amount)) {
            throw new Exception('Invalid number');
        }

        $this->maxResults = $amount;
    }

    /**
     * Get the cache duration
     * @return integer The currently defined cache duration in seconds
     */
    public function getCacheDuration() {
        return $this->cacheDuration;
    }

    /**
     * Get page profile array
     * @return array
     */
    protected function getProfileArray() {
        return [];
    }

    /**
     * Get releavant statistics
     * @return array
     */
    protected function getStatisticsArray() {
        return [];
    }

    /**
     * Get an array of posts
     * @return array
     */
    protected function getPostsArray() {
        return [];
    }
}
