<?php
defined('TYPO3') or die();
$boot = function () {
    // declare cache for video names and titles
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Skaras\Skvideo\Helper::TITLES_CACHE_NAME]??null)) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Skaras\Skvideo\Helper::TITLES_CACHE_NAME] = array();
    }

};

$boot();
unset($boot);