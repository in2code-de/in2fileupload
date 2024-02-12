<?php

declare(strict_types=1);

namespace In2code\In2fileupload\Event;

class ModifyModuleConfigurationEvent
{
    private mixed $moduleConfiguration;

    public function __construct(array $moduleConfiguration)
    {
        $this->moduleConfiguration = $moduleConfiguration;
    }

    public function getModuleConfiguration(): mixed
    {
        return $this->moduleConfiguration;
    }

    public function setModuleConfiguration(mixed $moduleConfiguration): void
    {
        $this->moduleConfiguration = $moduleConfiguration;
    }
}
