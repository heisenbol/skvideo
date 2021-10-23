<?php
namespace Skaras\Skvideo;

/**
 * Userfunc to render alternative label for media elements
 */
class ExtensionConfiguration
{
    public static function getSetting($key, $default = NULL) {

        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        );
        $backendConfiguration = $extensionConfiguration->get('skvideo');
        $setting = $backendConfiguration[$key]??$default;
        if ($setting) { // check here also that it is not empty
            return $setting;
        }
        return $default;

    }




}
