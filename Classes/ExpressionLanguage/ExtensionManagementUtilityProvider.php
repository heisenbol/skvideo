<?php
namespace Skar\Skvideo\ExpressionLanguage;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class ExtensionManagementUtilityProvider
{
    /**
     * @param string $extensionKey
     * @return bool
     */
    public function isLoaded($extensionKey): bool
    {
        return ExtensionManagementUtility::isLoaded($extensionKey);
    }

    /**
     * @param $val
     * @return int
     */
    public function intVal($val): int
    {
        return intval($val);
    }
}