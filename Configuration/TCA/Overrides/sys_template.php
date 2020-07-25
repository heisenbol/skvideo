<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('skvideo', 'Configuration/TypoScript', 'SK Video');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('skvideo', 'Configuration/TypoScript/Iframe/', 'SK Video Filter for IFRAME in HTML CE');