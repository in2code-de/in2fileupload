<?php

declare(strict_types=1);

namespace In2code\in2fileupload\Event;

use TYPO3\CMS\Core\Resource\FileInterface;

class ModifyFileMetaInformationEvent
{
    private mixed $fileInformation;
    private FileInterface $sysFile;

    public function __construct(array $fileInformation, FileInterface $sysFile)
    {
        $this->fileInformation = $fileInformation;
        $this->sysFile = $sysFile;
    }

    public function getSysFile(): FileInterface
    {
        return $this->sysFile;
    }

    public function getFileInformation(): mixed
    {
        return $this->fileInformation;
    }

    public function setFileInformation(mixed $fileInformation): void
    {
        $this->fileInformation = $fileInformation;
    }
}
