<?php
defined('TYPO3') or die();

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



