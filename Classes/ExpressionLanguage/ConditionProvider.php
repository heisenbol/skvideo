<?php
namespace Skar\Skvideo\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ConditionProvider
 */
class ConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageVariables = [
            'skextension' => GeneralUtility::makeInstance(ExtensionManagementUtilityProvider::class),
        ];
    }
}


