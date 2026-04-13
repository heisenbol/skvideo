<?php
declare(strict_types=1);
namespace Skaras\Skvideo\Backend\EventListener;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use Skaras\Skvideo\Helper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

final class SkvideoEventListener
{

    // https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Events/Events/Backend/PageContentPreviewRenderingEvent.html
    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getRecord()->getFullType() !== 'tt_content.skvideo_ce') {
            return;
        }

        $record = $event->getRecord();
        $rawRecord = $record->getRawRecord()->toArray();
        $flexformData = GeneralUtility::xml2array((string)$rawRecord['pi_flexform']);


        $helper = new Helper();
        $includeTitles = $this->getFieldFromFlexform($flexformData,'settings.includetitles');
        $type = $this->getFieldFromFlexform($flexformData,'settings.type');
        if (!$type) {
            $itemContent = LocalizationUtility::translate('LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:missingtype', 'skvideo', null, null);
            $event->setPreviewContent($itemContent);
            return;
        }
        $code = trim($record->get('bodytext'));
        $title = '';
        if ($includeTitles) {
            $title = $this->getFieldFromFlexform($flexformData,'settings.overridetitle');
            if (!$title) {
                $title = $helper->getTitles($code, $type)['title']??null;
            }
        }


        if (!$code) {
            $itemContent = LocalizationUtility::translate('LLL:EXT:skvideo/Resources/Private/Language/locallang_be.xlf:missingcode', 'skvideo', null, null);
            $event->setPreviewContent($itemContent);
            return;
        }

        $fileReferences = BackendUtility::resolveFileReferences('tt_content', 'image', $rawRecord);
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

        $platformLink = '';
        if ($type == Helper::TYPE_YOUTUBE) {
            $platformLink = "https://www.youtube.com/watch?v=".htmlspecialchars($code);
        }
        if ($type == Helper::TYPE_VIMEO) {
            $platformLink = "https://vimeo.com/".htmlspecialchars($code);
        }

        $viewFactoryData = new ViewFactoryData(
            templatePathAndFilename: 'EXT:skvideo/Resources/Private/Templates/BePreviewTemplate.html'
        );
        $view = GeneralUtility::makeInstance(ViewFactoryInterface::class)->create($viewFactoryData);
        $view->assign('title', $title);
        $view->assign('includeTitles', true);
        $view->assign('type', $type);
        $view->assign('platformLink', $platformLink);
        $view->assign('imgPath', $imgPath);
        $view->assign('videoCode', $code);
        $itemContent = $view->render();
        $event->setPreviewContent($itemContent);
    }
    public function getFieldFromFlexform(?array $flexform, string $key, string $sheet = 'sDEF'): ?string
    {
        /* method from news extension */
        if (!$flexform || !is_array($flexform)) {
            return null;
        }
        if (isset($flexform['data'])) {
            $flexform = $flexform['data'];
            if (isset($flexform[$sheet]['lDEF'][$key]['vDEF'])
            ) {
                return $flexform[$sheet]['lDEF'][$key]['vDEF'];
            }
        }

        return null;
    }

}

