<?php
// Adds the content element to the "Type" dropdown
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
   array(
      'Youtube / Vimeo Embed',
      'skvideo_ce',
      'EXT:skvideo/Resources/Public/Icons/Backend/ContentElement/player_icon.svg'
   ),
   'CType',
   'skvideo'
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
         'label' => 'Youtube/Vimeo Video Code',
         'config' => [
            'type'=>'input',
            'enableRichtext' => false
         ],
      ],
      'image' => [
         'label' => 'Custom preview image',
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
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $fields);

