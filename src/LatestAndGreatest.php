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
     * Default cache directory
     * @var String
     */
    protected $cacheDirectory = './cache/';

    /**
     * Initalise Cache Filename
     * @var String
     */
    protected $cacheFilename;

    /**
     * Default cache duration in seconds
     * @var Integer
     */
    protected $cacheDuration = (60 * 60);

    /**
     * Default max results amount
     * @var Integer
     */
    protected $maxResults = 1;

    /**
     * Initalise the data variable
     * @var Array
     */
    protected $data;

    public function __construct($options = []) {
        // If max results defined
        if (isset($options['max'])) {
            $this->maxResults = $options['max'];
        }

        // If cache directory defined
        if (isset($options['cacheDir'])) {
            $this->cacheDirectory = $options['cacheDir'];
        }
    }

    /**
     * Initalise the specific data set
     */
    public function init() {
        // Update cache
        $this->updateCache();

        // Populate data variable
        $this->getData();
    }

    /**
     * Get cache directory
     * @return String The currently defined cache directory
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
        $this->$cacheFilename = $filename;
    }

    /**
     * Get the cache filename
     * @return String The defined filename for the current cache
     */
    public function getCacheFilename() {
        if (!$cacheFilename) {
            throw new Exception('No cache filename set');
        }

        return $this->$cacheFilename;
    }

    /**
     * Update the default duration period
     * @param Integer $duration A number in seconds
     */
    public function setCacheDuration($duration) {
        if (!is_int($duration)) {
            throw new Exception('Invalid cache duration');
        }

        $this->cacheDuration = $duration;
    }

    /**
     * Get the cache duration
     * @return Integer The currently defined cache duration in seconds
     */
    public function getCacheDuration() {
        return $this->cacheDuration;
    }

    /**
     * Check is a cache update is required
     * @return boolean
     */
    public function isUpdateRequired() {
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
     * Set the max results your want to fetch from the desired API
     * @param Number $amount
     */
    public function setMaxResults($amount) {
        if (!is_int($amount)) {
            throw new Exception('Invalid number');
        }

        $this->maxResults = $amount;
    }

    /**
     * Update the cache file
     */
    public function updateCache() {
        if (!$this->isUpdateRequired()) {
            return false;
        }

        try {
            // Get current stats
            $profile = $this->getProfileArray();

            // Get current stats
            $statistics = $this->getStatisticsArray();

            // Get latest data
            $latest = $this->getPostsArray();

            // Combine arrays
            $data = [
                'profile' => $profile,
                'statistics' => $statistics,
                'latest' => $latest
            ];

            // Convert to json and save to cache file
            file_put_contents($this->getCacheDirectory() . $this->cacheFileName, json_encode($data));

        } catch(Exception $e) {
            print_r([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete the desired cache
     */
    public function deleteCache() {
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

    /**
     * Get the cached data
     * @return Array The stored data
     */
    function getData() {
        $this->data = json_decode(file_get_contents($this->getCacheDirectory() . $this->cacheFileName));
    }

    /**
     * Get profile
     * @return Array
     */
    public function getProfile() {
        if (!isset($this->data->profile)) {
            return [];
        }

        return $this->data->profile;
    }

    /**
     * Get page/profile stats
     * @return Array
     */
    public function getStats() {
        if (!isset($this->data->statistics)) {
            return [];
        }

        return $this->data->statistics;
    }

    /**
     * Get the latest posts
     * @return Array
     */
    public function getLatest() {
        if (!isset($this->data->latest)) {
            return [];
        }

        return $this->data->latest;
    }
}
