<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;

return [
    // Icon identifier
    'skvideo-icon' => [
        // Icon provider class
        'provider' => BitmapIconProvider::class,
        // The source SVG for the SvgIconProvider
        'source' => 'EXT:skvideo/Resources/Public/Icons/Backend/ContentElement/player_icon.svg',
    ],
];

