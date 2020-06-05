<?php
defined('TYPO3_MODE') || die();

$boot = function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['skvideo_skvideo_ce'] =
       \Skar\Skvideo\Hooks\PageLayoutView\SkvideoPreviewRenderer::class;

    // declare cache for video names and titles
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Skar\Skvideo\Helper::TITLES_CACHE_NAME])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Skar\Skvideo\Helper::TITLES_CACHE_NAME] = array();
    }


    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'skvideo-icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        ['source' => 'EXT:skvideo/Resources/Public/Icons/Backend/ContentElement/player_icon.svg']
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    mod.wizards.newContentElement.wizardItems.common {
       elements {
          skvideo_ce {
             iconIdentifier = skvideo-icon
             title = SK Youtube/Vimeo  Video
             description = Embed a Youtube/Vimeo Video
             tt_content_defValues {
                CType = skvideo_ce
             }
          }
       }
       show := addToList(skvideo_ce)
    }
    ');
};

$boot();
unset($boot);