<?php
defined('TYPO3_MODE') || die();

$boot = function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['skvideo_skvideo_ce'] =
       \Skar\Skvideo\Hooks\PageLayoutView\SkvideoPreviewRenderer::class;

    // declare cache for video names and titles
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Skar\Skvideo\Helper::TITLES_CACHE_NAME])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Skar\Skvideo\Helper::TITLES_CACHE_NAME] = array();
    }
};

$boot();
unset($boot);