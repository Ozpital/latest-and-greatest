<?php

namespace LatestAndGreatest;

class LatestAndGreatest {
    /**
     * Define default cache directory
     * @var String
     */
    protected $cacheDirectory = './cache/';

    /**
     * Register cache filename variable
     * @var String
     */
    protected $cacheFilename;

    /**
     * Define cache duration in seconds
     * @var Integer
     */
    protected $cacheDuration = (60 * 60);

    /**
     * Define default max results amount
     * @var Integer
     */
    protected $maxResults = 1;

    /**
     * Register the data variable
     * @var Array
     */
    protected $data;

    /**
     * [__construct description]
     * @param array $options [description]
     */
    public function __construct($options = []) {
        // If max results defined
        if (isset($options['max']) && !empty($options['max'])) {
            $this->setMaxResults($options['max']);
        }

        // If cache directory defined
        if (isset($options['cacheDir']) && !empty($options['cacheDir'])) {
            $this->setCacheDirectory($options['cacheDir']);
        }
    }

    /**
     * [setMaxResults description]
     * @param integer $amount [description]
     */
    public function setMaxResults($amount = 1) {
        $this->maxResults = $amount;
    }

    /**
     * [setCacheDirectory description]
     * @param [type] $directory [description]
     */
    public function setCacheDirectory($directory) {
        $this->cacheDirectory = $directory;
    }

    /**
     * Set the cache file name
     * @param string $filename A valid string that can be used as a filename for the cache
     */
    public function setCacheFilename(string $filename) {
        $this->$cacheFilename = $filename;
    }


}
