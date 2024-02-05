<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'in2fileupload',
    'setup',
    "@import 'EXT:in2fileupload/Configuration/TypoScript/setup.typoscript'"
);
