<?php
declare(strict_types=1);

namespace In2code\In2fileupload\Controller;

use In2code\In2fileupload\Domain\Factory\MetaFieldConfigurationFactory;
use In2code\In2fileupload\Domain\Model\MetaFieldConfiguration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFileNameException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class UploadController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ModuleTemplate $moduleTemplate;
    protected ResourceFactory $resourceFactory;
    protected PageRenderer $pageRenderer;
    protected Context $context;
    private LoggerInterface $logger;

    protected array $uploadErrors = [];

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        ResourceFactory $resourceFactory,
        PageRenderer $pageRenderer,
        Context $context,
        LoggerInterface $logger
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->resourceFactory = $resourceFactory;
        $this->pageRenderer = $pageRenderer;
        $this->context = $context;
        $this->logger = $logger;
    }

    public function indexAction(): ResponseInterface
    {
        if (array_key_exists('id', $this->request->getQueryParams())) {
            $folderIdentifier = $this->request->getQueryParams()['id'];
            $requiredMetaFields = [];
            $metaFields = $this->getMetaFields();

            foreach ($metaFields as $fieldConfiguration) {
                if (array_key_exists('required', $fieldConfiguration) && $fieldConfiguration['required']) {
                    $requiredMetaFields[] = $fieldConfiguration['id'];
                }
            }

            $configuration = [
                'targetFolder' => $folderIdentifier,
                'maxFileSize' => GeneralUtility::getMaxUploadFileSize() * 1024,
                'allowedFileTypes' => GeneralUtility::trimExplode(',', $this->settings['allowedFileTypes'] ?? '', true),
                'metaFields' => array_values($metaFields),
                'allowedMetaFields' => array_merge(array_keys($metaFields),
                    ['in2fileupload__folderIdentifier', 'in2fileupload__duplicationBehaviour']),
                'requiredMetaFields' => $requiredMetaFields,
                'backendLanguage' => $GLOBALS['BE_USER']->uc['lang'] ?? 'en'
            ];

            $configuration =
                $this->eventDispatcher->dispatch(new ModifyModuleConfigurationEvent($configuration))->getModuleConfiguration();

            $this->preparePageRenderer($configuration);

            $this->view->assignMultiple(
                [
                    'folder' => $this->resourceFactory->getFolderObjectFromCombinedIdentifier($folderIdentifier),
                    'configuration' => $configuration
                ]
            );
        }

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        return $this->htmlResponse($moduleTemplate->setContent($this->view->render())->renderContent());
    }

    public function uploadAction(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->settings)) {
            $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'in2fileupload');
        }

        $isValid = $this->validate($request);
        $response = [
            'success' => false
        ];

        if ($isValid) {
            $file = reset($_FILES);
            $targetFolder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($_POST['in2fileupload__folderIdentifier']);

            try {
                $sysFile = $targetFolder->addUploadedFile($file, $this->settings['duplicationBehaviour']);

                $fileMetaInformation = [];

                foreach ($this->getMetaFields() as $key => $configurationArray) {
                    if (array_key_exists($key, $_POST) && !empty($_POST[$key]) && $_POST[$key] !== 'undefined') {
                        $configuration = $this->buildConfiguration($configurationArray);
                        $fileMetaInformation[$configuration->getTable()][$configuration->getFieldName()] = $_POST[$key];
                    }
                }

                $fileMetaInformation = $this->eventDispatcher->dispatch(new ModifyFileMetaInformationEvent($fileMetaInformation,
                    $sysFile))->getFileInformation();

                if (array_key_exists('sys_file_metadata', $fileMetaInformation)) {
                    $metaData = $sysFile->getMetaData();
                    foreach ($fileMetaInformation['sys_file_metadata'] as $property => $value) {
                        $metaData->offsetSet($property, $value);
                    }

                    $metaData->save();
                }

                $response['success'] = true;

            } catch (ExistingTargetFileNameException $exception) {
                $this->uploadErrors['targetFileExist'] = [
                    'title' => LocalizationUtility::translate(
                        'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.targetFileExist.title'
                    ),
                    'message' => LocalizationUtility::translate(
                        'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.targetFileExist.message'
                    )
                ];
            }
        }

        if (!empty($this->uploadErrors)) {
            $response['errors'] = $this->uploadErrors;
        }

        return $this->jsonResponse(json_encode($response, JSON_THROW_ON_ERROR));
    }

    private function validate(ServerRequestInterface $request): bool
    {
        $isValid = true;

        if (empty($this->settings)) {
            $isValid = false;
            $this->uploadErrors['missingConfiguration'] = [
                'title' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.missingConfiguration.title'
                ),
                'message' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.missingConfiguration.message'
                )
            ];
        }

        if (!is_array($request->getUploadedFiles())) {
            $isValid = false;
            $this->uploadErrors['missingFile'] = [
                'title' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.missingFile.title'
                ),
                'message' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.missingFile.message'
                )
            ];
        }

        if (count($request->getUploadedFiles()) > 1) {
            $isValid = false;
            $this->uploadErrors['batchUpload'] = [
                'title' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.batchUpload.title'
                ),
                'message' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.batchUpload.message'
                )
            ];
        }

        if (!array_key_exists('in2fileupload__folderIdentifier', $_POST) ||
            empty($_POST['in2fileupload__folderIdentifier']) ||
            $_POST['in2fileupload__folderIdentifier'] === 'undefined') {
            $isValid = false;
            $this->uploadErrors['missingFolder'] = [
                'title' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.missingFolder.title'
                ),
                'message' => LocalizationUtility::translate(
                    'LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:uploadError.missingFolder.message'
                )
            ];
        }

        $isValid = $this->eventDispatcher->dispatch(new AfterUploadValidationEvent($isValid, $uploadErrors, $request,
            $this))->isValid();

        return $isValid;
    }


    private function preparePageRenderer(array $configuration): void
    {
        foreach ($this->settings['cssFiles'] as $cssFile) {
            $this->pageRenderer->addCssFile($cssFile);
        }

        foreach ($this->settings['jsFiles'] as $jsFile) {
            $this->pageRenderer->addJsFile($jsFile);
        }

        $this->pageRenderer->addJsInlineCode(
            'in2fileupload configuration',
            'var in2fileupload = ' . json_encode($configuration, JSON_THROW_ON_ERROR)
        );
    }

    private function buildConfiguration(array $properties): MetaFieldConfiguration
    {
        $metaFieldFactory = GeneralUtility::makeInstance(MetaFieldConfigurationFactory::class);
        return $metaFieldFactory->mapProperties($properties);
    }

    private function getMetaFields(): array
    {
        $metaFields = [];

        if (array_key_exists('fieldConfiguration', $this->settings) &&
            is_array($this->settings['fieldConfiguration'])) {
            foreach ($this->settings['fieldConfiguration'] as $table => $tableConfiguration) {
                foreach ($tableConfiguration as $fieldName => $configuration) {
                    if (!ArrayUtility::isValidPath($GLOBALS['TCA'], $table . '/columns/' . $fieldName)) {
                        $this->logger->error(
                            'No CTA configuration for the given field.',
                            ['field' => $fieldName, 'table' => $table]
                        );
                        continue;
                    }

                    $configuration = $this->buildConfiguration(
                        array_merge($configuration, ['table' => $table, 'fieldName' => $fieldName])
                    );
                    $combinedIdentifier = $configuration->getCombinedIdentifier();

                    $metaFields[$combinedIdentifier] = [
                        'table' => $configuration->getTable(),
                        'fieldName' => $configuration->getFieldName(),
                        'id' => $combinedIdentifier,
                        'name' => $configuration->getTitle(),
                        'placeholder' => $configuration->getPlaceholder(),
                        'required' => $configuration->isRequired(),
                    ];
                }
            }
        }

        return $metaFields;
    }

    protected function getUploadErrors(): array
    {

    }
}
