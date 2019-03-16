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
        $flexformService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\FlexFormService::class);
        $settings = $flexformService->convertFlexFormContentToArray($row['pi_flexform'])['settings']??NULL;

        if (!$settings) {
          $itemContent = 'Missing plugin settings';
          return;
        }

        $type = $settings['type'] ?? null;
        $code = trim($row['bodytext'] ?? '');
        if (!$type) {
          $itemContent = 'Missing video type';
          return;
        }
        if (!$code) {
          $itemContent = 'Missing video code';
          return;
        }

        $helper = new Helper();
        $imgPath = $helper->getPreviewImageUrl($code, $type, Helper::CONTEXT_BE); // for BE I can't get a url. I just get the absolute file path of the iage

        // Festlegen der Template-Datei
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $fluidTemplate */
        $fluidTmplFilePath = GeneralUtility::getFileAbsFileName('typo3conf/ext/skvideo/Resources/Private/Templates/BePreviewTemplate.html');
        $fluidTmpl = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\View\StandaloneView');
        $fluidTmpl->setTemplatePathAndFilename($fluidTmplFilePath);
        $fluidTmpl->assign('type', $type);
        $fluidTmpl->assign('code', $code);
        $fluidTmpl->assign('imgPath', $imgPath);

        $itemContent = $parentObject->linkEditContent($fluidTmpl->render(), $row);

/*
        $itemContent .= '<p>Video type:'.$type.'</p>';
        $itemContent .= '<p>Code:'.$code.'</p>';
        //$itemContent .= '<p>imgSrc:'.$imgSrc.'</p>';
        $itemContent .= '<p>Auto play:'.($autoplay?'Yes':'No').'</p>';*/
      }
   }
}