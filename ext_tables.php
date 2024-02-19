<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

if (GeneralUtility::makeInstance(Typo3Version::class)?->getMajorVersion() < 12) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'in2fileupload',
        'file',
        'upload',
        '',
        [
            In2code\In2fileupload\Controller\UploadController::class => 'index, upload',
        ],
        [
            'access' => 'user,group',
            'iconIdentifier' => 'module-filelist',
            'labels' => 'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang_in2fileupload.xlf',
        ]
    );
}
