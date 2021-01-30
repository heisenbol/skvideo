<?php
namespace Skar\Skvideo\DataProcessing;

use Skar\Skvideo\Helper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Service\FlexFormService;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class DataProcessor implements DataProcessorInterface
{
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        // $processedData['data'] contains the tt_content row
        $result = [];
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);


        $flexformSettings = $flexFormService->convertFlexFormContentToArray($processedData['data']['pi_flexform'])['settings']??NULL;
        if (!$flexformSettings) {
            $result['error'] = 'Missing plugin settings';
        }
        $type = $flexformSettings['type'] ?? null;
        $code = trim($processedData['data']['bodytext'] ?? '');
        if (!$type) {
            $result['error'] = 'Missing video type';
        }
        if (!$code) {
            $result['error'] = 'Missing video code';
        }

        if (!($result['error']??NULL)) {
            $result['type'] = $type;

            $includetitles = $flexformSettings['includetitles'] ?? null;

            $helper = new Helper();
            $result['maxGeneratedWidth'] = $helper->getMaxGeneratedWidth(); // these come from the extension settings
            $result['maxGeneratedHeight'] = $helper->getMaxGeneratedHeight(); // these come from the extension settings

            $imgPath = $helper->getPreviewImagePath($code, $type, Helper::CONTEXT_FE);
            $result['imgPathOriginal'] = $imgPath;

            $result['includetitles'] = $includetitles;


            if ($includetitles) {
                $videoTitle = trim($flexformSettings['overridetitle'] ?? '');

                if (!$videoTitle) {
                    $titles = $helper->getTitles($code, $type);
                    $videoTitle = $titles['title']??null;
                    $videoAuthor = $titles['author']??null;
                    $result['hoverTitle'] = $videoTitle.($videoAuthor?', '.$videoAuthor:'');
                }
                else {
                    $result['hoverTitle'] = $videoTitle;
                }


                $result['videoTitle'] = $videoTitle;

            }



            $ratio = $flexformSettings['sizeratio'] ?? 43;
            $maxWidth = intval($flexformSettings['maxwidth'] ?? 0);

            if (!in_array($ratio,[43,169])) {
                $ratio = 43;
            }
            if (!$maxWidth) {
                $maxWidth = "100%";
            }
            else {
                $maxWidth = $maxWidth.'px';
            }
            $result['maxWidth'] = $maxWidth;

            if ($ratio == 169) {
                $embedWidth = 560;
                $embedHeight = 315;
            }
            else if ($ratio == 43) {
                $embedWidth = 400;
                $embedHeight = 300;
            }
            $result['embedWidth'] = $embedWidth;
            $result['embedHeight'] = $embedHeight;
            $result['ratio'] = $ratio;

            $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
            $ceSettings = $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'skvideo'
            );

            if ($type == Helper::TYPE_YOUTUBE) {
                $embedMarkup = '<iframe width="'.$embedWidth.'" height="'.$embedHeight.'" src="https://www.youtube-nocookie.com/embed/'.$code.'?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }
            else if ($type == Helper::TYPE_YOUTUBE_LIST) {
                $embedMarkup = '<iframe width="'.$embedWidth.'" height="'.$embedHeight.'" src="https://www.youtube-nocookie.com/embed/videoseries?list='.$code.'&autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }
            else if ($type == Helper::TYPE_VIMEO) {
                $embedMarkup = '<iframe src="https://player.vimeo.com/video/'.$code.'?autoplay=1" width="'.$embedWidth.'" height="'.$embedHeight.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
            }
            else {
                $embedMarkup = 'Unsupported video type '.htmlspecialchars($type, ENT_QUOTES, "UTF-8");
            }
            $result['embedMarkup'] = $embedMarkup;


            $disablerememberme = intval($ceSettings['disablerememberme']);
            $remembermedays = intval($ceSettings['remembermedays']);
            if ($remembermedays > 180 || $remembermedays < 0) {
                $remembermedays = 30;
            }
            $result['disablerememberme'] = $disablerememberme;
            $result['remembermedays'] = $remembermedays;

            $message = LocalizationUtility::translate('message', 'skvideo', null, null, null);
            if (trim($ceSettings['message']??'')) {
                $message = trim($ceSettings['message']);
            }
            // check if VIDEOPROVIDER placeholder needs to be replaced
            $providerName = '';
            switch ($type) {
                case Helper::TYPE_YOUTUBE:
                case Helper::TYPE_YOUTUBE_LIST:
                    $providerName = 'YouTube';
                    break;
                case Helper::TYPE_VIMEO:
                    $providerName = 'Vimeo';
                    break;
                default:
                    $providerName = 'Unknown provider';
            }
            $message = str_replace ( 'VIDEOPROVIDER', $providerName ,$message );

            $result['message'] = $message;


            $cancel = LocalizationUtility::translate('cancel', 'skvideo', null, null, null);
            if (trim($ceSettings['cancel']??'')) {
                $cancel = trim($ceSettings['cancel']);
            }
            $result['cancel'] = $cancel;

            $continue = LocalizationUtility::translate('continue', 'skvideo', null, null, null);
            if (trim($ceSettings['continue']??'')) {
                $continue = trim($ceSettings['continue']);
            }
            $result['continue'] = $continue;

            $rememberme = '';
            if (!$disablerememberme) {
                if ($remembermedays === 0) {
                    $rememberme = LocalizationUtility::translate('remembermesession', 'skvideo', null, null, null);
                    if (trim($ceSettings['remembermesession']??'')) {
                        $rememberme = trim($ceSettings['remembermesession']);
                    }
                }
                else {
                    $rememberme = LocalizationUtility::translate('rememberme', 'skvideo', [$remembermedays], null, null);
                    if (trim($ceSettings['rememberme']??'')) {
                        $rememberme = trim($ceSettings['rememberme']);
                    }
                }
            }
            $result['rememberme'] = $rememberme;

        }

        if (($result['error']??NULL)) {
            $result['flexformSettings'] = $flexformSettings;
            $result['settings'] = $ceSettings;
        }

        $processedData['skvideoOptions'] = $result;
        return $processedData;
    }
}