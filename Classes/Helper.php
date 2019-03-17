<?php
namespace Skar\Skvideo;

use TYPO3\CMS\Core\Utility\GeneralUtility;
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
  public function getPreviewImageUrl($code, $type, $context) {
    if ($type == 'YOUTUBE') {
      return $this->getPreviewImageUrlYoutube($code, $context);
    }
    if ($type == 'VIMEO') {
      return $this->getPreviewImageUrlVimeo($code, $context);
    }
    return $this->getPreviewImageUrlNoImage();
  }

  private function getPreviewImageUrlYoutube($code, $context) {

    $url = "https://img.youtube.com/vi/$code/maxresdefault.jpg";
    return $this->retrieveImage($url, $code, $context, self::FILE_PREFIX_YOUTUBE);
  }
  private function getPreviewImageUrlVimeo($code, $context) {
    $apiUrl = "https://vimeo.com/api/v2/video/$code.json";
    $json = file_get_contents($apiUrl);
    $decoded = json_decode($json,true);
    $url = $decoded[0]['thumbnail_large']??null;
    return $this->retrieveImage($url, $code, $context, self::FILE_PREFIX_YOUTUBE);

  }
  private function retrieveImage($url, $code, $context, $filePrefix) {
    $retrieveResult = $this->retrieveThumbImage($url, $code, $filePrefix);
    if ($retrieveResult === false) {
      return false;
    }
    $maxWidth = 500;
    $maxHeight = 500;
    if ($context === self::CONTEXT_FE) {
      $maxWidth = 500;
      $maxHeight = 500;
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
    if (file_exists($dst)) { // already downloaded
      $this->log("tx_skvideo $dst already exists ");
      return true; 
    }

    $file = file_get_contents($url);
    if ($file === FALSE) {
      $this->log("tx_skvideo could not retrieve video thumb from url ".$url);
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
    return PATH_site.'/'.$this->getRelativeUploadFolder();
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