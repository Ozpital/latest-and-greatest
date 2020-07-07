<?php

namespace LatestAndGreatest;

class LatestAndGreatest {
    /**
     * Register cache directory variable
     * @var string
     */
    protected $cacheDirectory;

    /**
     * Register cache filename variable
     * @var string
     */
    protected $cacheFilename;

    /**
     * Define cache duration in seconds
     * @var int
     */
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Register cached access token filename variable
     * @var string
     */
    protected $accessTokenFilename;

    /**
     * Define default max results amount
     * @var int
     */
    protected $maxResults;

    /**
     * Define the data variable
     * @var array
     */
    protected $data;

    /**
     * Define the default $debug variable
     * @var bool
     */
    protected $debug = false;

    /**
     * Define the default $debug variable
     * @var bool
     */
    protected $disableCache = false;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * [__construct description]
     */
    public function __construct() {}

    /**
     * [setDebug description]
     * @param bool $debug [description]
     */
    public function setDebug(bool $debug) {
        if (!isset($debug) || empty($debug)) {
            return false;
        }

        $this->debug = $debug;
    }

    /**
     * [getDebug description]
     * @return [type] [description]
     */
    public function getDebug() {
        if (!isset($this->debug) || empty($this->debug)) {
            return false;
        }

        return $this->debug;
    }

    /**
     * [setDebug description]
     * @param bool $debug [description]
     */
    public function setDisableCache(bool $disableCache) {
        if (!isset($disableCache) || empty($disableCache)) {
            return false;
        }

        $this->disableCache = $disableCache;
    }

    /**
     * [getDebug description]
     * @return [type] [description]
     */
    public function getDisableCache() {
        if (!isset($this->disableCache) || empty($this->disableCache)) {
            return false;
        }

        return $this->disableCache;
    }

    /**
     * [setMaxResults description]
     * @param int $amount [description]
     */
    public function setMaxResults(int $amount = NULL) {
        $maxResults = 1;

        if (isset($amount) && !empty($amount)) {
            $maxResults = $amount;
        }

        if ($amount < 1) {
            $maxResults = 1;
        }

        if ($amount > 5) {
            $maxResults = 5;
        }

        $this->maxResults = $maxResults;
    }

    /**
     * [getMaxResults description]
     * @return [type] [description]
     */
    public function getMaxResults() {
        if (!isset($this->maxResults) || empty($this->maxResults)) {
            return 1;
        }

        return $this->maxResults;
    }

    /**
     * [setCacheDirectory description]
     * @param string $cacheDirectory [description]
     */
    public function setCacheDirectory(string $cacheDirectory) {
        if (!isset($cacheDirectory) || empty($cacheDirectory)) {
            if ($this->debug) {
                trigger_error('Cache directory cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Get cache directory
     * @return string The currently defined cache directory
     */
    public function getCacheDirectory() {
        if (!is_dir($this->cacheDirectory) && !mkdir($this->cacheDirectory, 0755, true)) {
            if ($this->debug) {
                trigger_error('No cache directory found or cannot be created', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        // add trailing slash
        return rtrim($this->cacheDirectory, '/') . '/';
    }

    /**
     * Set the cache file name
     * @param string $cacheFilename A valid string that can be used as a filename for the cache
     */
    public function setCacheFilename(string $cacheFilename) {
        if (!isset($cacheFilename) || empty($cacheFilename)) {
            if ($this->debug) {
                trigger_error('Cache filename cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->cacheFilename = $cacheFilename;
    }

    /**
     * Get the cache filename
     * @param string $cacheFilename
     */
    public function getCacheFilename() {
        if (!isset($this->cacheFilename) || empty($this->cacheFilename)) {
            if ($this->debug) {
                trigger_error('No cache filename set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->cacheFilename;
    }

    /**
     * Define the default cache duration period
     * @param int $duration A number in seconds
     */
    public function setCacheDuration(int $cacheDuration) {
        if (!isset($cacheDuration) || empty($cacheDuration)) {
            if ($this->debug) {
                trigger_error('Cache duration cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->cacheDuration = $cacheDuration;
    }

    /**
     * Get the cache duration
     * @return int The currently defined cache duration in seconds
     */
    public function getCacheDuration() {
        if (!isset($this->cacheDuration) || empty($this->cacheDuration)) {
            if ($this->debug) {
                trigger_error('No cache duration set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->cacheDuration;
    }
    
    /**
     * Set the cached access token file name
     * @param string $accessTokenFilename A valid string that can be used as a filename for the access token cache
     */
    public function setAccessTokenFilename(string $accessTokenFilename) {
        if (!isset($accessTokenFilename) || empty($accessTokenFilename)) {
            if ($this->debug) {
                trigger_error('Access token filename cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->accessTokenFilename = $accessTokenFilename;
    }

    /**
     * Get the cached access token filename
     * @param string $accessTokenFilename
     */
    public function getAccessTokenFilename() {
        if (!isset($this->accessTokenFilename) || empty($this->accessTokenFilename)) {
            if ($this->debug) {
                trigger_error('No access token cache filename set', E_USER_NOTICE);
                die();
            }

            return NULL;
        }

        return $this->accessTokenFilename;
    }

    /**
     * Check is a cache update is required
     * @return bool
     */
    public function isUpdateRequired() {
        // Get cache directory
        $cacheDirectory = $this->getCacheDirectory();

        // Get cache filename
        $cacheFilename = $this->getCacheFilename();

        // Get cache duration
        $cacheDuration = $this->getCacheDuration();

        // If cache file doesn't exist, create it
        if (!file_exists($cacheDirectory . $cacheFilename)) {
            touch($cacheDirectory . $cacheFilename);
            return true;
        }

        // Is there any data in the file?
        if (!filesize($cacheDirectory . $cacheFilename)) {
           return true;
        }

        // Is the file age over the threshold?
        if ((time() - $cacheDuration) > filemtime($cacheDirectory . $cacheFilename)) {
            return true;
        }

        // Have we disabled cache?
        if ($this->getDisableCache()) {
            return true;
        }

        // Cache update not required
        return false;
    }

    /**
     * Update the cache file
     */
    public function updateCache() {
        if (!$this->isUpdateRequired()) {
            return false;
        }

        $profile = $this->getProfile();
        $posts = $this->getPosts();

        $data = [
            'profile' => $profile,
            'posts' => $posts
        ];

        // Convert to json and save to cache file
        if (!file_put_contents($this->getCacheDirectory() .  $this->getCacheFilename(), json_encode($data))) {
            if ($this->debug) {
                trigger_error('Cache did not successfully update', E_USER_NOTICE);
                die();
            }
        }
    }

    /**
     * Set the cached data
     * @return array The stored data
     */
    function setData() {
        $this->data = json_decode(file_get_contents($this->getCacheDirectory() . $this->getCacheFilename()));
    }

    /**
     * Get the cached data
     * @return array The stored data
     */
    function getData() {
        $this->updateCache();
        $this->setData();

        return $this->data;
    }

    /**
     * [setRedirectUri description]
     * @param string $redirectUri [description]
     */
    public function setRedirectUri(string $redirectUri) {
        if (!isset($redirectUri) || empty($redirectUri)) {
            if ($this->debug) {
                trigger_error('Redirect URI cannot be empty', E_USER_NOTICE);
                die();
            }
        }

        $this->redirectUri = $redirectUri;
    }

    /**
     * [getRedirectUri description]
     * @param string $redirectUri [description]
     */
    public function getRedirectUri() {
        if (!isset($this->redirectUri) || empty($this->redirectUri)) {
            return 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        return $this->redirectUri;
    }
}
