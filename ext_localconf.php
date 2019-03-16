<?php
defined('TYPO3_MODE') || die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['skvideo_skvideo_ce'] =
   \Skar\Skvideo\Hooks\PageLayoutView\SkvideoPreviewRenderer::class;