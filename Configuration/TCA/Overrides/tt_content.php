<?php
defined('TYPO3_MODE') or die();
// Adds the content element to the "Type" dropdown
// see https://stackoverflow.com/questions/54789892/best-way-to-register-custom-content-element-to-type-dropdown why it is better to use addTcaSelectItem
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
//   array(
//      'Youtube / Vimeo Embed',
//      'skvideo_ce',
//      'EXT:skvideo/Resources/Public/Icons/Backend/ContentElement/player_icon.svg'
//   ),
//   'CType',
//   'skvideo'
//);

// Add 'newcontentelement' to tt_content.CType selector list
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:listtypetitle',
        'skvideo_ce',
        'skvideo-icon'
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
      'image' => [
         'label' => 'LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:customimagelabel',
         'config' => [
            'maxitems' => 1,
                'overrideChildTca' => [
                    'types' => [
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
//                            'showitem' => '
  //                              --palette--;;imageoverlayPalette,
    //                            --palette--;;filePalette'
                            'showitem' => '
                                crop,
                                --palette--;;filePalette'
                        ],
                    ],
                ],
         ]
      ],
      /*
      'pi_flexform' => [
         'label' => 'labeloverridepi_flexformxyz',
         'config' => [
               'type' => 'flex',
               'ds_pointerField' => 'CType',
               'ds' => [
                 'skvideo_ce' => 'FILE:EXT:skvideo/Configuration/FlexForms/Videoproperties.xml',

               ],
         ]
      ]*/
   ]
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:skvideo/Configuration/FlexForms/Videoproperties.xml',
    'skvideo_ce'
);
// Content Type Icon in Page BE Editor
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['skvideo_ce'] =  'skvideo-icon';
