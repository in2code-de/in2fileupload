<?php
declare(strict_types=1);

namespace In2code\In2fileupload\Domain\Model;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class MetaFieldConfiguration
{
    protected string $fieldName = '';
    protected string $table = '';
    protected bool $required = false;
    protected string $title = '';
    protected string $placeholder = '';

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): MetaFieldConfiguration
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): MetaFieldConfiguration
    {
        $this->table = $table;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): MetaFieldConfiguration
    {
        $this->required = $required;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): MetaFieldConfiguration
    {
        if (str_starts_with($title, 'LLL:')) {
            $translationLabel = LocalizationUtility::translate(
                $title
            );

            if ($translationLabel) {
                $title = $translationLabel;
            }
        }

        $this->title = $title;

        return $this;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string $placeholder): MetaFieldConfiguration
    {
        if (str_starts_with($placeholder, 'LLL:')) {
            $placeholderLabel = LocalizationUtility::translate(
                $placeholder
            );

            if ($placeholderLabel) {
                $placeholder = $placeholderLabel;
            }

        }

        $this->placeholder = $placeholder;

        return $this;
    }

    public function getCombinedIdentifier(): string
    {
        return $this->getTable() . '__' . $this->getFieldName();
    }

}
