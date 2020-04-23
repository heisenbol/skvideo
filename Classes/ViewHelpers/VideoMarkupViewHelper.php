<?php
namespace Skar\Skvideo\ViewHelpers;

use Skar\Skvideo\Helper;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Service\FlexFormService;
class VideoMarkupViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Initialize additional argument
     */
    public function initializeArguments()
    {
    	parent::initializeArguments();
        $this->registerArgument('data', 'array', 'The CE', TRUE);
        $this->registerArgument('images', 'array', 'The images of the CE', FALSE);
    }

	/**
	* @return string
	*/
	public function render() {
        $row = $this->arguments['data'];
        $customPreviewImages = $this->arguments['images']??null;

        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $settings = $flexFormService->convertFlexFormContentToArray($row['pi_flexform'])['settings']??NULL;
        if (!$settings) {
          return 'Missing plugin settings';
        }

        $type = $settings['type'] ?? null;
        $includetitles = $settings['includetitles'] ?? null;

        $code = trim($row['bodytext'] ?? '');
        if (!$type) {
          return 'Missing video type';
        }
        if (!$code) {
          return 'Missing video code';
        }

        $helper = new Helper();
        if ($customPreviewImages && count($customPreviewImages) > 0) {
            $imgSrc = $helper->getCustomPreviewImageUrl($customPreviewImages[0], Helper::CONTEXT_FE); 
        }
        else {
            $imgSrc = $helper->getPreviewImageUrl($code, $type, Helper::CONTEXT_FE); 
        }
        

        $titlesMarkup = '';
        $hoverTitleEscaped = '';
        if ($includetitles) {
            $videoTitle = trim($settings['overridetitle'] ?? '');

            if (!$videoTitle) {
                $titles = $helper->getTitles($code, $type); 
                $videoTitle = $titles['title']??null;
                $videoAuthor = $titles['author']??null;
                $hoverTitleEscaped = htmlspecialchars($videoTitle, ENT_QUOTES, "UTF-8" ).', '.htmlspecialchars($videoAuthor, ENT_QUOTES, "UTF-8" );
            }
            else {
                $hoverTitleEscaped = htmlspecialchars($videoTitle, ENT_QUOTES, "UTF-8" );
            }
            
            if ($videoTitle) {
                $titlesMarkup = '<div class="sk-video-titlecontainer" title="'.$hoverTitleEscaped.'">'.htmlspecialchars($videoTitle, ENT_QUOTES, "UTF-8" ).'</div>';
            }
        }



        $ratio = $settings['sizeratio'] ?? 43;
        $maxWidth = intval($settings['maxwidth'] ?? 0);

        if (!in_array($ratio,[43,169])) {
            $ratio = 43;
        }
        if (!$maxWidth) {
            $maxWidth = "100%";
        }
        else {
            $maxWidth = $maxWidth.'px';
        }

        $previewImageMarkup = '<img src="'.$imgSrc.'" alt="'.$hoverTitleEscaped.'">';

        if ($ratio == 169) {
            $embedWidth = 560;
            $embedHeight = 315;
        }
        else if ($ratio == 43) {
            $embedWidth = 400;
            $embedHeight = 300;
        }

        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $settings = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'skvideo'
        );

        if ($type == Helper::TYPE_YOUTUBE) {
            $embedMarkup = '<iframe width="'.$embedWidth.'" height="'.$embedHeight.'" src="https://www.youtube-nocookie.com/embed/'.$code.'?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
        else if ($type == Helper::TYPE_VIMEO) {
            $embedMarkup = '<iframe src="https://player.vimeo.com/video/'.$code.'?autoplay=1" width="'.$embedWidth.'" height="'.$embedHeight.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        }
        else {
            $embedMarkup = 'Unsupported video type '.htmlspecialchars($type, ENT_QUOTES, "UTF-8");
        }
        $playButtonMarkup = "<div title='$hoverTitleEscaped' class='sk-video-playbutton' data-cancel='".htmlspecialchars($settings['cancel'], ENT_QUOTES, "UTF-8")."' data-continue='".htmlspecialchars($settings['continue'], ENT_QUOTES, "UTF-8")."' data-rememberme='".htmlspecialchars($settings['rememberme'], ENT_QUOTES, "UTF-8")."' data-message='".htmlspecialchars($settings['message'], ENT_QUOTES, "UTF-8")."' data-videomarkup='".htmlspecialchars($embedMarkup, ENT_QUOTES, "UTF-8")."'></div>";

        return '<div class="sk-video-supercontainer" style="max-width:'.$maxWidth.'"><div class="sk-video-container ratio'.$ratio.'">'.$previewImageMarkup.$titlesMarkup.$playButtonMarkup.'</div></div>';
	}

}

