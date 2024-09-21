<?php
namespace Skaras\Skvideo;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Utility\PathUtility;
/**
 * Contains a preview rendering for the page module of CType="skvideo_skvideo_ce"
 */
class Helper
{
    const TYPE_YOUTUBE = 'YOUTUBE';
    const TYPE_VIMEO = 'VIMEO';
    const TYPE_YOUTUBE_LIST = 'YOUTUBE PLAYLIST';
    const FILE_PREFIX_YOUTUBE = 'yt_';
    const FILE_PREFIX_VIMEO = 'vi_';
    const FILE_PREFIX_YOUTUBE_PL = 'yp_';


    const CONTEXT_BE = 'BE';
    const CONTEXT_FE = 'FE';

    const TITLES_CACHE_NAME = 'skvideo_titlescache';
    const CACHE_PREFIX = 'TITLES';
    private const CACHE_TAG = 'skvideo';

    private $MAX_WIDTH;
    private $MAX_HEIGHT;

    function __construct() {
        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $settings = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'skvideo'
        );
        $this->MAX_WIDTH = intval($settings['max_preview_width']??0);
        $this->MAX_HEIGHT = intval($settings['max_preview_height']??0);
//        $this->MAX_HEIGHT = intval($settings['disablerememberme']??0);
//        $this->MAX_HEIGHT = intval($settings['remembermedays']??0);
        if ($this->MAX_WIDTH < 100) {
            $this->MAX_WIDTH = 500;
        }
        if ($this->MAX_HEIGHT < 100) {
            $this->MAX_HEIGHT = 500;
        }
    }

    public function getMaxGeneratedWidth() {
        return $this->MAX_WIDTH;
    }
    public function getMaxGeneratedHeight() {
        return $this->MAX_HEIGHT;
    }
    private function getTitlesCacheKey($code, $type) {
        // the cache identifiers follow a specific pattern. As I use the user provided video code, the user
        // may type in something that is invalid for this pattern
        // TYPO3 9 uses this check: return preg_match(self::PATTERN_ENTRYIDENTIFIER, $identifier)===1
        // where PATTERN_ENTRYIDENTIFIER = '/^[a-zA-Z0-9_%\\-&]{1,250}$/'
        // so do here a similar check
        $identifier = self::CACHE_PREFIX.$code.'_'.$type;
        if (preg_match('/^[a-zA-Z0-9_%\\-&]{1,250}$/', $identifier)!==1) {
            return false;
        }
        return $identifier;
    }
    public function getTitles($code, $type) {
        $cacheIdentifier = $this->getTitlesCacheKey($code, $type);
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::TITLES_CACHE_NAME);
        $titles = FALSE;
        if ($cacheIdentifier) {
            $titles = $cache->get($cacheIdentifier);
        }
        if ($titles === FALSE) {
            if ($type == self::TYPE_YOUTUBE) {
                $titles = $this->getTitlesYoutube($code);
            }
            else if ($type == self::TYPE_YOUTUBE_LIST) {
                $titles = $this->getTitlesYoutubeList($code);
            }
            else if ($type == self::TYPE_VIMEO) {
                $titles = $this->getTitlesVimeo($code);
            }
            if ($titles && $cacheIdentifier) {
                $cache->set($cacheIdentifier, $titles, [self::CACHE_TAG], \Skaras\Skvideo\ExtensionConfiguration::getSetting('titleslifetime',1209600));
            }

        }
        return $titles;
    }

    private function getTitlesYoutube($code) {
        $oembedUrl = 'https://www.youtube.com/oembed?url=http%3A//youtube.com/watch%3Fv%3D'.$code.'&format=json';
        return $this->retrieveTitles($oembedUrl);
    }
    private function getYoutubeListApiUrl($code) {
        $apiKey = \Skaras\Skvideo\ExtensionConfiguration::getSetting('ytapikey',null);
        if (!$apiKey) { // youtube play list info can only be retrieved via API access, using a google API key for YouTube Data API v3
            $this->log("tx_skvideo youtube play list info can only be retrieved via API access, using a google API key for YouTube Data API v3. Set API key in extension settings");
            return null;
        }
        return 'https://www.googleapis.com/youtube/v3/playlists?part=snippet&id='.$code.'&key='.$apiKey;
    }
    private function getTitlesYoutubeList($code) {
        $apiUrl = $this->getYoutubeListApiUrl($code);
        if (!$apiUrl) {
            return null;
        }
        $decoded = $this->retrieveJsonUrl($apiUrl);
        if (!$decoded) {
            return null;
        }
        $title = $decoded['items'][0]['snippet']['title']??null;

        if ($title) {
            return ['title'=>$title, 'author'=>null];
        }
        return null;
    }

    private function getTitlesVimeo($code) {
        $oembedUrl = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/'.$code;
        return $this->retrieveTitles($oembedUrl);
    }
    private function retrieveTitles($oembedUrl) {
        $decoded = $this->retrieveJsonUrl($oembedUrl);
        if (!$decoded) {
            return null;
        }
        $title = $decoded['title']??null;
        $author = $decoded['author_name']??null;

        if ($title) {
            return ['title'=>$title, 'author'=>$author];
        }
        return null;
    }
    private function retrieveJsonUrl($url) {
        $json = @file_get_contents($url);
        $decoded = @json_decode($json,true);
        return $decoded;
    }

    public function getPreviewImagePath($code, $type, $context) {
        $imgPath = null;
        if ($type == self::TYPE_YOUTUBE) {
            $imgPath = $this->getPreviewImagePathYoutube($code, $context);
        }
        if ($type == self::TYPE_YOUTUBE_LIST) {
            $imgPath = $this->getPreviewImagePathYoutubeList($code, $context);
        }
        if ($type == self::TYPE_VIMEO) {
            $imgPath = $this->getPreviewImagePathVimeo($code, $context);
        }
        if (!$imgPath)
            return $this->getPreviewImagePathNoImage();

        return $imgPath;
    }

    public function getCustomPreviewImagePath($fileRef, $context) {
        return $fileRef->getForLocalProcessing(false);
    }

    private function getPreviewImagePathYoutube($code, $context) {
        $url = [
            "https://img.youtube.com/vi/$code/maxresdefault.jpg",
            "https://img.youtube.com/vi/$code/mqdefault.jpg",
            "https://img.youtube.com/vi/$code/default.jpg"
        ];
        return $this->retrieveImage($url, $code, $context, self::FILE_PREFIX_YOUTUBE);
    }
    private function getPreviewImagePathVimeo($code, $context) {
        // for vimeo we do not know the url for the thumbimage, so call it with null and true for the isVimeo parameter
        return $this->retrieveImage(null, $code, $context, self::FILE_PREFIX_VIMEO, true);
    }
    private function getPreviewImagePathYoutubeList($code, $context) {
        // for youtube list we do not know the url for the thumbimage, so call it with null and true for the isYoutubeList parameter
        return $this->retrieveImage(null, $code, $context, self::FILE_PREFIX_YOUTUBE_PL, false, true);
    }
    private function retrieveImage($url, $code, $context, $filePrefix, $isVimeo = false, $isYoutubeList = false) {
        $retrieveResult = $this->retrieveThumbImage($url, $code, $filePrefix, $isVimeo, $isYoutubeList);
        if ($retrieveResult === false) {
            return false;
        }
        return $this->getAbsoluteFilePath($code, $filePrefix);
//    if ($context === self::CONTEXT_FE) {
//      $maxWidth = $this->MAX_WIDTH;
//      $maxHeight = $this->MAX_HEIGHT;
//
//      return $this->getImageUrl($this->getAbsoluteFilePath($code, $filePrefix), $maxWidth, $maxHeight, 90);
//    }
//    else {
//      return $this->getAbsoluteFilePath($code, $filePrefix);
//    }
    }
    private function getPreviewImagePathNoImage() {
        return GeneralUtility::getFileAbsFileName( 'EXT:skvideo/Resources/Public/Images/nopreview.png');

    }
//    private function getPreviewImageUrlNoImage() {
//        return PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName( 'EXT:skvideo/Resources/Public/Images/nopreview.png'));
//
//    }
    private function getImageUrl($absoluteFilePath, $maxWidth, $maxHeight, $quality = 95) {
//    $img = array();
//    $img['image.']['file.']['maxH']   = $maxWidth;
//    $img['image.']['file.']['maxW']   = $maxHeight;
//    $img['image.']['file.']['params']  ='-quality '.$quality;
//    $img['image.']['file'] = $absoluteFilePath;
//    $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
//    $cObj = $configurationManager->getContentObject();
//    return $cObj->cObjGetSingle('IMG_RESOURCE', $img['image.']);

        $imageService = GeneralUtility::makeInstance(ImageService::class);
        $image = $imageService->getImage($absoluteFilePath, null, false);
        $processingInstructions = array(
            'maxWidth' => $maxWidth,
            'maxHeight' => $maxHeight
        );
        $processedImage = $imageService->applyProcessingInstructions($image, $processingInstructions);
        return $imageService->getImageUri($processedImage);
    }

    private function getAbsoluteFilePath($code, $filePrefix) {
        $uploadDir = $this->getAbsoluteUploadDir();
        return $uploadDir.$this->getFilename($code, $filePrefix);
    }
    private function retrieveThumbImage($url, $code, $filePrefix, $isVimeo = false, $isYoutubeList = false) {
        $uploadDir = $this->getAbsoluteUploadDir();

        $dst = $this->getAbsoluteFilePath($code, $filePrefix);
        if (!file_exists($uploadDir)) { // upload dir does not exist yet. Create it
            $mkdirResult = \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($uploadDir);
            if ($mkdirResult === false) {
                $this->log("uploads/tx_skvideo folder does not exist and could not be created");
                return false;
            }
        }
        $imagesLifeTime = \Skaras\Skvideo\ExtensionConfiguration::getSetting('imageslifetime',1209600);
        if (file_exists($dst) && (filemtime($dst) + $imagesLifeTime > time()) ) { // already downloaded and lifetime has not passed yet
            return true;
        }
        if ($isVimeo) {
            // for vimeo we did not know the url of the thumb image, so url is null. Get it here
            $apiUrl = "https://vimeo.com/api/v2/video/$code.json";
            $decoded = $this->retrieveJsonUrl($apiUrl);
            $url = $decoded[0]['thumbnail_large']??null;
        }
        if ($isYoutubeList) {
            // for youtube list we did not know the url of the thumb image, so url is null. Get it here
            $apiUrl = $this->getYoutubeListApiUrl($code);
            $decoded = $this->retrieveJsonUrl($apiUrl);
            $url = $decoded['items'][0]['snippet']['thumbnails']['maxres']['url']??null;
        }
        if (!is_array($url) && $url) {
            $url = [$url];
        }

        // at this point, $url should be an array of urls. Make sure
        if (!is_array($url)) {
            $this->log("tx_skvideo no url for retrieving thumbnail image, for prefix ".$filePrefix);
            return false;
        }

        $file = @file_get_contents($url[0]); // up to 3 urls
        if ($file === FALSE && count($url) > 1) {
            $file = @file_get_contents($url[1]);
        }
        if ($file === FALSE && count($url) > 2) {
            $file = @file_get_contents($url[2]);
        }
        if ($file === FALSE) {
            // $http_response_header will be of the last request (3rd url)
            $this->log("tx_skvideo could not retrieve video thumb from url(s) ".print_r($url,true).print_r($http_response_header,true));
            return false;
        }
        $saveResult = file_put_contents($dst, $file);
        if ($saveResult === FALSE) {
            $this->log("tx_skvideo could not store video thumb to ".$dst);
            return false;
        }
        return true;
    }
    private function getAbsoluteUploadDir() {
        return \TYPO3\CMS\Core\Core\Environment::getPublicPath().'/'.$this->getRelativeUploadFolder();
    }
    private function getRelativeFilePath($code, $filePrefix) {
        return $this->getRelativeUploadFolder().$this->getFilename($code, $filePrefix);
    }
    private function getRelativeUploadFolder() {
        return 'uploads/tx_skvideo/';
    }
    private function getFilename($code, $filePrefix) {
        return $filePrefix.$code.'.jpg';
    }
    private function log($msg) {
        $logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
        $logger->error(
            $msg
        );
    }
}