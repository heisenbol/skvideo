<?php
namespace Skar\Skvideo\Hooks\PageLayoutView;

use \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use \TYPO3\CMS\Backend\View\PageLayoutView;
use Skar\Skvideo\Helper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Contains a preview rendering for the page module of CType="skvideo_skvideo_ce"
 */
class SkvideoPreviewRenderer implements PageLayoutViewDrawItemHookInterface
{

   /**
    * Preprocesses the preview rendering of a content element of type "My new content element"
    *
    * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
    * @param bool $drawItem Whether to draw the item using the default functionality
    * @param string $headerContent Header content
    * @param string $itemContent Item content
    * @param array $row Record row of tt_content
    *
    * @return void
    */
   public function preProcess(
      PageLayoutView &$parentObject,
      &$drawItem,
      &$headerContent,
      &$itemContent,
      array &$row
   )
   {
      if ($row['CType'] === 'skvideo_ce') {
        $drawItem = false;
//        $flexformService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\FlexFormService::class);
        $flexFormService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\FlexFormService::class);
        $settings = $flexFormService->convertFlexFormContentToArray($row['pi_flexform'])['settings']??NULL;

        if (!$settings) {
          $itemContent = 'Missing plugin settings';
          return;
        }
        $helper = new Helper();
        $includeTitles = $settings['includetitles'] ?? null;
        $type = $settings['type'] ?? null;
        $code = trim($row['bodytext'] ?? '');
        $title = '';
        if ($includeTitles) {
          $title = trim($settings['overridetitle'] ?? '');
          if (!$title) {
            $title = $helper->getTitles($code, $type)['title']??null; 
          }
        }

        if (!$type) {
          $itemContent = 'Missing video type';
          return;
        }
        if (!$code) {
          $itemContent = 'Missing video code';
          return;
        }
        
        $fileReferences = \TYPO3\CMS\Backend\Utility\BackendUtility::resolveFileReferences('tt_content', 'image', $row);
        $customPreviewImage = null;
        foreach($fileReferences as $fileReference) {
          $customPreviewImage = $fileReference;
          break;
        }
        if ($customPreviewImage) {

            $imgPath = $helper->getCustomPreviewImageUrl($customPreviewImage, Helper::CONTEXT_BE); 
        }
        else {
            $imgPath = $helper->getPreviewImageUrl($code, $type, Helper::CONTEXT_BE);
        }

        

        // Set template file
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $fluidTemplate */
        $platformLink = '';
        if ($type == Helper::TYPE_YOUTUBE) {
          $platformLink = "https://www.youtube.com/watch?v=".htmlspecialchars($code);
        }
        if ($type == Helper::TYPE_VIMEO) {
          $platformLink = "https://vimeo.com/".htmlspecialchars($code);
        }

        $fluidTmplFilePath = GeneralUtility::getFileAbsFileName('typo3conf/ext/skvideo/Resources/Private/Templates/BePreviewTemplate.html');
        $fluidTmpl = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\View\StandaloneView');
        $fluidTmpl->setTemplatePathAndFilename($fluidTmplFilePath);
        $fluidTmpl->assign('title', $title);
        $fluidTmpl->assign('includeTitles', $includeTitles);
        $fluidTmpl->assign('type', $type);
        $fluidTmpl->assign('platformLink', $platformLink);
        $fluidTmpl->assign('imgPath', $imgPath);

        $itemContent = $parentObject->linkEditContent($fluidTmpl->render(), $row);
      }
   }
}