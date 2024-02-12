<?php

declare(strict_types=1);

namespace In2code\In2fileupload\Event;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;

class AfterUploadValidationEvent
{
    private bool $isValid;
    private array $uploadErrors;
    private ServerRequestInterface $request;
    private ControllerInterface $controller;

    public function __construct(bool $isValid, array $uploadErrors, ServerRequestInterface $request, ControllerInterface $controller)
    {
        $this->isValid = $isValid;
        $this->uploadErrors = $uploadErrors;
        $this->request = $request;
        $this->controller = $controller;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    public function getUploadErrors(): array
    {
        return $this->uploadErrors;
    }

    public function setUploadErrors(array $uploadErrors): void
    {
        $this->uploadErrors = $uploadErrors;
    }
}
