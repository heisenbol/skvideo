<?php
defined('TYPO3_MODE') || die('Access denied.');




		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('skvideo', 'Configuration/TypoScript', 'SK Video');
		// \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile($extKey,'Configuration/PageTs/pageTSconfig.txt','SK Carousel');


		$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

		$iconRegistry->registerIcon(
			'skvideo-icon',
			\TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
			['source' => 'EXT:skvideo/Resources/Public/Icons/Backend/ContentElement/Video.png']
		);

		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
			mod.wizards.newContentElement.wizardItems.common {
			   elements {
			      skvideo_ce {
			         iconIdentifier = skvideo-icon
			         title = SK Youtube/Vimeo  Video
			         description = Embed a Youtube/Vimeo Video
			         tt_content_defValues {
			            CType = skvideo_ce
			         }
			      }
			   }
			   show := addToList(skvideo_ce)
			}
		');


