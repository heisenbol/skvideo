<?php
use TYPO3\CMS\Core\Resource\FileType;

defined('TYPO3') or die();

// Add 'newcontentelement' to tt_content.CType selector list
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:listtypetitle',
        'value' => 'skvideo_ce',
        'icon' => 'skvideo-icon'
    ],
    'textmedia',
    'after'
);

$GLOBALS['TCA']['tt_content']['types']['skvideo_ce'] = array(
   'showitem' => '
      --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
         --palette--;;general,
         --palette--;;headers,
         bodytext,
         pi_flexform,
         image,
      --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
         --palette--;;frames,
         --palette--;;appearanceLinks,
      --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
         --palette--;;language,
      --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
         --palette--;;hidden,
         --palette--;;access,
      --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
         categories,
      --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
         rowDescription,
      --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
   ',
   'columnsOverrides' => [
      'bodytext' => [
         'label' => 'LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:codefieldlabel',
         'config' => [
            'type'=>'input',
            'enableRichtext' => false
         ],

      ],
       'pi_flexform' => [
           'config' => [
               'ds' => 'FILE:EXT:skvideo/Configuration/FlexForms/Videoproperties.xml',
           ]
       ],
      'image' => [
         'label' => 'LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:customimagelabel',
         'config' => [
            'maxitems' => 1,
                'overrideChildTca' => [
                    'types' => [
                        FileType::IMAGE->value => [
                            'showitem' => '
                                crop,
                                --palette--;;filePalette'
                        ],
                    ],
                ],
         ]
      ],
   ]
);

// Content Type Icon in Page BE Editor
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['skvideo_ce'] =  'skvideo-icon';
