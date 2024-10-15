<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'SK Video Content Element',
    'description' => 'TYPO3 content element to cache thumbnails of Youtube & Vimeo videos and play them after user confirmation. Options to remember user decision via cookie. Responsive display. Works with and without fluid_styled_content. No other dependencies.',
    'category' => 'fe',
    'author' => 'Stefanos Karasavvidis',
    'author_email' => 'sk@karasavvidis.gr',
    'state' => 'stable',
    'internal' => '',
    'clearCacheOnLoad' => true,
    'version' => '3.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Skaras\\Skvideo\\' => 'Classes'
        ],
    ],
];
