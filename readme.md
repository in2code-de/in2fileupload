# In2fileupload

Is a TYPO3 extension that makes it possible to force the user to set metadata such as copyright when uploading.

## Requirements

* TYPO3 ^11.5 or TYPO3 ^12.4
* PHP ^8.0

## TypoScript configuration

### Allowed file types (`settings.allowedFileTypes`)

allows you to restrict the files that are uploaded to certain file formats (comma-separated list).
Possible are wildcards `image/*`, or exact mime types `image/jpeg`, or file extensions `.jpg`

see: https://uppy.io/docs/uppy/#restrictions

```typo3_typoscript
module.tx_in2fileupload.settings.allowedFileTypes = image/*, .pdf
```

____

### Meta field configuration (`settings.fieldConfiguration`)

The metadata fields that are displayed during the upload are configured here.
In addition, fields can also be defined here as mandatory fields which must then be filled in before the upload.
It is also possible to use your own defined fields in "sys_file_metadata" here. As long as these are defined in the TCA.

```typo3_typoscript
fieldConfiguration {
    sys_file_metadata {
        title {
            required = 0
            title = LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:title
        }

        description {
            required = 0
            title = LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:description
            placeholder = LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:description.placeholder
        }

        copyright {
            required = 1
            title = LLL:EXT:in2fileupload/Resources/Private/Language/Backend/locallang.xlf:copyright
        }
    }
}
```
**Options:**

* required (0 or 1): defines if the configured meta field is required to be filled before upload
* title (string or language key): the displayed title of the input field 
* placeholder (string or language key): the displayed placeholder of the input field


### Duplication behaviour (`settings.duplicationBehaviour`)

TYPO3 provides three options for the duplication behaviour.
This extension supports all three options.

#### Options:

##### rename [default]:

appends a number to the end of the file name. Thus, for example, "myFile.jpg" becomes "myFile_01.jpg"
This is the current default bahaviour.

##### replace:

replaces the already existing file and overrides the potentially already set metadata

##### cancel:

the upload for this file is canceled

The duplication behaviour can be set via:

```typo3_typoscript
module.tx_in2fileupload.settings.duplicationBehaviour = rename
```

____

## Events

* ModifyModuleConfigurationEvent
* ModifyFileMetaInformationEvent
* AfterUploadValidationEvent

## External libraries

### Uppy

Uppy is a sleek, modular open source JavaScript file uploader.

- Website: https://uppy.io/
- Documentation: https://uppy.io/docs/quick-start/

## Known issues

- there is currently no simple possible way to theme / style the uppy dashboard.

### possible features for the future:

- categorisation
- fill fields of other tables
- more events
- localization of different backend language as "de" and "en"
- code cleanup
- option to set meta information for all uploaded files
- prefill of meta fields
