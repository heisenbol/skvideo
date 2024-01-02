<?php
declare(strict_types=1);
namespace Skaras\Skvideo\Backend\EventListener;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Backend\View\PageLayoutView;
use Skaras\Skvideo\Helper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
final class SkvideoEventListener
{
    // https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Events/Events/Backend/PageContentPreviewRenderingEvent.html
    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content') {
            return;
        }

        if ($event->getRecord()['CType'] === 'skvideo_ce') {
            $row = $event->getRecord();
            $flexFormService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\FlexFormService::class);
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
                $itemContent = LocalizationUtility::translate('LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:missingtype', 'skvideo', null, null);
                return;
            }
            if (!$code) {
                $itemContent = LocalizationUtility::translate('LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:missingcode', 'skvideo', null, null);
                return;
            }

            $fileReferences = \TYPO3\CMS\Backend\Utility\BackendUtility::resolveFileReferences('tt_content', 'image', $row);
            $customPreviewImage = null;
            foreach($fileReferences as $fileReference) {
                if (!$fileReference->getProperty('hidden') && !$fileReference->getProperty('deleted')) {
                    $customPreviewImage = $fileReference;
                    break;
                }
            }
            if ($customPreviewImage) {
                $imgPath = $helper->getCustomPreviewImagePath($customPreviewImage, Helper::CONTEXT_BE);
            }
            else {
                $imgPath = $helper->getPreviewImagePath($code, $type, Helper::CONTEXT_BE);
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

            $fluidTmplFilePath = GeneralUtility::getFileAbsFileName('EXT:skvideo/Resources/Private/Templates/BePreviewTemplate.html');
            $fluidTmpl = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\View\StandaloneView');
            $fluidTmpl->setTemplatePathAndFilename($fluidTmplFilePath);
            $fluidTmpl->assign('title', $title);
            $fluidTmpl->assign('includeTitles', $includeTitles);
            $fluidTmpl->assign('type', $type);
            $fluidTmpl->assign('platformLink', $platformLink);
            $fluidTmpl->assign('imgPath', $imgPath);
            $fluidTmpl->assign('videoCode', $code);
            $itemContent = $fluidTmpl->render();//$parentObject->linkEditContent($fluidTmpl->render(), $row);
            $event->setPreviewContent($itemContent);
        }
    }

}

