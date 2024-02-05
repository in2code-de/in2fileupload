<?php

declare(strict_types=1);

namespace In2code\In2fileupload\Domain\Factory;

use In2code\In2fileupload\Domain\Model\MetaFieldConfiguration;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MetaFieldConfigurationFactory
{
    public function mapProperties(array $properties): MetaFieldConfiguration
    {
        $metaFieldConfiguration = GeneralUtility::makeInstance(MetaFieldConfiguration::class);

        $metaFieldConfiguration
            ->setTable($properties['table'])
            ->setFieldName($properties['fieldName']);

        if (array_key_exists('required', $properties) && (int)$properties['required'] === 1) {
            $metaFieldConfiguration->setRequired(true);
        }

        if (!empty(array_key_exists('placeholder', $properties) && $properties['placeholder'] !== '')) {
            $metaFieldConfiguration->setPlaceholder($properties['placeholder']);
        }

        if (!empty(array_key_exists('title', $properties) && $properties['title'] !== '')) {
            $metaFieldConfiguration->setTitle($properties['title']);
        }

        return $metaFieldConfiguration;
    }
}
