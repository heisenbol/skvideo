<?php
namespace Skar\Skvideo\ViewHelpers;

use Skar\Skvideo\Helper;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Service\FlexFormService;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class HtmlIframeViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper {
    const GOOGLE_MAPS_START_URL = 'https://www.google.com/maps/';
    /**
     * Initialize additional argument
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('bodytext', 'string', 'The HTML Markup', TRUE);
    }

    /**
     * @return string
     */
    public function render() {
        $bodytext = $this->arguments['bodytext'];

        // check if the markup contains an iframe tag.
        // in this case, replace it

        $replacementCount = 0;
        $iframeCount = 0;
        $dom = new \DOMDocument;
        $dom->loadHTML($bodytext, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); // loadHTML converts all tags to lowercase. So I do not care about case
        $iframeList = [];
        // get list of iframes separately, as replacing them on the fly messes up the dom and iframes are lost
        foreach ($dom->getElementsByTagName('iframe') as $iframe) {
            $iframeList[] = $iframe;
        }
        foreach ($iframeList as $iframe) {
            $iframeCount++;
            $iframeSrc = $iframe->getAttribute('src');
            if (substr($iframeSrc, 0, strlen(self::GOOGLE_MAPS_START_URL)) === self::GOOGLE_MAPS_START_URL) {
                $width = intval($iframe->getAttribute('width'));
                $height = intval($iframe->getAttribute('height'));
                if (!$width) {
                    $width = 600;
                }
                if (!$height) {
                    $height = 400;
                }
                $span = $dom->createElement('span');
                $span->nodeValue = $iframeSrc.' DOES match';
                $iframe->parentNode->replaceChild($span, $iframe);
                $replacementCount++;
            }
        }
        if ($replacementCount) {
            return $dom->saveHTML();
        }

        return $bodytext;
    }

}
