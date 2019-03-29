<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'SK Video Content Element',
    'description' => 'TYPO3 plugin to cache thumbnails of youtube/vimeo videos and play them after user confirmation. Responsive display. Works with css_styled_content and fluid_styled_content. No other dependencies',
    'category' => 'fe',
    'author' => 'Stefanos Karasavvidis',
    'author_email' => 'sk@karasavvidis.gr',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '1',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.9',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
