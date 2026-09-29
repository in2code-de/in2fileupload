<?php
declare(strict_types=1);

return [
    'in2fileupload' => [
        'parent' => 'media',
        'access' => 'user',
        'path' => '/module/file/in2fileupload',
        'labels' => 'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang_in2fileupload.xlf',
        'extensionName' => 'in2fileupload',
        'iconIdentifier' => 'module-file',
        'controllerActions' => [
            In2code\In2fileupload\Controller\UploadController::class => 'index, upload',
        ],
    ],
];
