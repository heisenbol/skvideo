<?php
namespace Skar\Skvideo;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Cache\CacheManager;
use \TYPO3\CMS\Extbase\Service\ImageService;
use \TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Contains a preview rendering for the page module of CType="skvideo_skvideo_ce"
 */
class Helper
{
  const TYPE_YOUTUBE = 'YOUTUBE';
  const TYPE_VIMEO = 'VIMEO';
  const FILE_PREFIX_YOUTUBE = 'yt_';
  const FILE_PREFIX_VIMEO = 'vi_';

  const CONTEXT_BE = 'BE';
  const CONTEXT_FE = 'FE';

  const TITLES_CACHE_NAME = 'skvideo_titlescache';
  const CACHE_PREFIX = 'TITLES';
  private const CACHE_TAG = 'skvideo';

  const MAX_WIDTH = 1280   ;
  const MAX_HEIGHT = 1280;

  private function getTitlesCacheKey($code, $type) {
    return self::CACHE_PREFIX.$code.'_'.$type;
  }
  public function getTitles($code, $type) {
    $cacheIdentifier = $this->getTitlesCacheKey($code, $type);
    $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::TITLES_CACHE_NAME);
    if (($titles = $cache->get($cacheIdentifier)) === FALSE) {
      if ($type == self::TYPE_YOUTUBE) {
        $titles = $this->getTitlesYoutube($code);
      }
      else if ($type == self::TYPE_VIMEO) {
        $titles = $this->getTitlesVimeo($code);
      }
      if ($titles) {
        $cache->set($cacheIdentifier, $titles, [self::CACHE_TAG], \Skar\Skvideo\ExtensionConfiguration::getSetting('titleslifetime',1209600));
      }

    }
    return $titles;
  }

  private function getTitlesYoutube($code) {
    $oembedUrl = 'https://www.youtube.com/oembed?url=http%3A//youtube.com/watch%3Fv%3D'.$code.'&format=json';
    return $this->retrieveTitles($oembedUrl);
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

  public function getPreviewImageUrl($code, $type, $context) {
    if ($type == self::TYPE_YOUTUBE) {
      return $this->getPreviewImageUrlYoutube($code, $context);
    }
    if ($type == self::TYPE_VIMEO) {
      return $this->getPreviewImageUrlVimeo($code, $context);
    }
    return $this->getPreviewImageUrlNoImage();
  }

  public function getCustomPreviewImageUrl($fileRef, $context) {
    $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    $imageService= $objectManager->get(ImageService::class);
    $cropString = $fileRef->getProperty('crop');
    $cropVariantCollection = \TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection::create($cropString);
    $cropArea = $cropVariantCollection->getCropArea('default');
    if ($context === self::CONTEXT_FE) {
      // https://rolf-thomas.de/how-to-generate-images-in-a-typo3-extbase-controller
      //$imagePath = $fileRef->getOriginalFile()->getPublicUrl();
      $processedImage = $imageService->applyProcessingInstructions(
          $fileRef, 
            [
              'maxWidth' => self::MAX_WIDTH,
              'maxHeight' => self::MAX_HEIGHT,
              'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($fileRef)
            ]
          );
      return $imageService->getImageUri($processedImage);
    }
    else {
      return $fileRef->getOriginalFile()->getPublicUrl();
    }
  }

  private function getPreviewImageUrlYoutube($code, $context) {

    $url = [
      "https://img.youtube.com/vi/$code/maxresdefault.jpg",
      "https://img.youtube.com/vi/$code/mqdefault.jpg",
      "https://img.youtube.com/vi/$code/default.jpg"
    ];
    return $this->retrieveImage($url, $code, $context, self::FILE_PREFIX_YOUTUBE);
  }

  private function getPreviewImageUrlVimeo($code, $context) {
    $apiUrl = "https://vimeo.com/api/v2/video/$code.json";
    $json = @file_get_contents($apiUrl);
    $decoded = json_decode($json,true);
    $url = $decoded[0]['thumbnail_large']??null;
    return $this->retrieveImage($url, $code, $context, self::FILE_PREFIX_VIMEO);
  }

  private function retrieveImage($url, $code, $context, $filePrefix) {
    $retrieveResult = $this->retrieveThumbImage($url, $code, $filePrefix);
    if ($retrieveResult === false) {
      return false;
    }
    if ($context === self::CONTEXT_FE) {
      $maxWidth = self::MAX_WIDTH;
      $maxHeight = self::MAX_HEIGHT;
      return $this->getImageUrl($this->getAbsoluteFilePath($code, $filePrefix), $maxWidth, $maxHeight, 90);
    }
    else {
      return $this->getAbsoluteFilePath($code, $filePrefix);
    }
  }
  private function getPreviewImageUrlNoImage() {
    return 'noimage src';
  }

  private function getImageUrl($absoluteFilePath, $maxWidth, $maxHeight, $quality = 95) {
    $img = array();
    $img['image.']['file.']['maxH']   = $maxWidth;
    $img['image.']['file.']['maxW']   = $maxHeight;
    $img['image.']['file.']['params']  ='-quality '.$quality;
    $img['image.']['file'] = $absoluteFilePath;  
    $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
  //  $cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
    $cObj = $configurationManager->getContentObject();
//    $cObj = GeneralUtility::makeInstance(TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
    return $cObj->cObjGetSingle('IMG_RESOURCE', $img['image.']);
  }

  private function getAbsoluteFilePath($code, $filePrefix) {
    $uploadDir = $this->getAbsoluteUploadDir();
    return $uploadDir.$this->getFilename($code, $filePrefix);
  }
  private function retrieveThumbImage($url, $code, $filePrefix) {
    $uploadDir = $this->getAbsoluteUploadDir();

    $dst = $this->getAbsoluteFilePath($code, $filePrefix);
    if (!file_exists($uploadDir)) { // upload dir does not exist yet. Create it
      $mkdirResult = mkdir($uploadDir);
      if ($mkdirResult === false) {
        $this->log("uploads/tx_skvideo folder does not exist and could not be created");
        return false;
      }
    }
    $imagesLifeTime = \Skar\Skvideo\ExtensionConfiguration::getSetting('imageslifetime',1209600);
    if (file_exists($dst) && (filemtime($dst) + $imagesLifeTime > time()) ) { // already downloaded and lifetime has not passed yet
//      $this->log("tx_skvideo $dst already exists ");
      return true; 
    }
    if (!is_array($url)) {
      $url = [$url];
    }
    $file = @file_get_contents($url[0]); // up to 3 urls
    if ($file === FALSE && count($url) > 1) {
      $file = @file_get_contents($url[1]);
    }
    if ($file === FALSE && count($url) > 2) {
      $file = @file_get_contents($url[2]);
    }
    if ($file === FALSE) {
      $this->log("tx_skvideo could not retrieve video thumb from url(s) ".print_r($url,true));
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