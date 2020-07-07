# Latest & Greatest
Get stats and latest posts from various social media, including Facebook, Twitter, Instagram, YouTube and Pinterest.

---
---

## Installation
### Composer
1. Add the project repository to your project composer.json:
```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/weareindi/latest-and-greatest.git"
    }
]
```

2. Require the **latest-and-greatest** project
```
"require": {
    "weareindi/latest-and-greatest": "*"
}
```

3. Run composer update in the terminal
```
composer update
```

This should fetch the project ready for use.

---
---

## Usage
### Quick Start
```
<?php

require_once('../vendor/autoload.php');

use LatestAndGreatest\Networks\Twitter;
use LatestAndGreatest\Networks\YouTube;
use LatestAndGreatest\Networks\Instagram;
use LatestAndGreatest\Networks\Facebook;

// Global cache directory
$cacheDirectory = './lag';

// Twitter
$twitter = new Twitter();
$twitter->setDebug(false);
$twitter->setDisableCache(false);
$twitter->setCacheDirectory($cacheDirectory);
$twitter->setMaxResults(4);
$twitter->setApiKey('XXXX');
$twitter->setApiSecret('XXXX');
$twitter->setAccessToken('XXXX');
$twitter->setAccessTokenSecret('XXXX');
$twitter->setUserName('XXXX');
print_r('<pre>');
print_r($twitter->getData());
print_r('<pre>');

// YouTube
$youtube = new YouTube();
$youtube->setDebug(false);
$youtube->setDisableCache(false);
$youtube->setCacheDirectory($cacheDirectory);
$youtube->setMaxResults(4);
$youtube->setApiKey('XXXX');
$youtube->setChannelID('XXXX');
$youtube->setUserName('XXXX');
print_r('<pre>');
print_r($youtube->getData());
print_r('<pre>');

// Instagram
$instagram = new Instagram();
$instagram->setDebug(false);
$instagram->setDisableCache(false);
$instagram->setCacheDirectory($cacheDirectory);
$instagram->setMaxResults(4);
$instagram->setUsername('XXXX');
print_r('<pre>');
print_r($instagram->getData());
print_r('<pre>');

// Facebook
$facebook = new Facebook();
$facebook->setDebug(false);
$facebook->setDisableCache(false);
$facebook->setCacheDirectory($cacheDirectory);
$facebook->setMaxResults(4);
$facebook->setPageId('XXXX');
$facebook->setAppId('XXXX');
$facebook->setAppSecret('XXXX');
$facebook->setAccessToken('XXXX');
print_r('<pre>');
print_r($facebook->getData());
print_r('<pre>');
```
