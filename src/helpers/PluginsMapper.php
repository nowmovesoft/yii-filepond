<?php

namespace nms\filepond\helpers;

/**
 * Contains information about FilePond plugins options names.
 * Automatic registers assets, if FilepondWidget contains these options.
 * @author Michael Naumov <vommuan@gmail.com>
 */
class PluginsMapper
{
    /**
     * Known FilePond options names in plugins.
     */
    const OPTIONS_MAP = [
        'nms\filepond\FileEncodeAsset' => [
            'allowFileEncode',
        ],
        'nms\filepond\FileMetadataAsset' => [
            'allowFileMetadata',
            'fileMetadataObject',
        ],
        'nms\filepond\FilePosterAsset' => [
            'allowFilePoster',
        ],
        'nms\filepond\FileRenameAsset' => [
            'allowFileRename',
            'fileRenameFunction',
        ],
        'nms\filepond\FileSizeValidationAsset' => [
            'allowFileSizeValidation',
            'maxFileSize',
            'maxTotalFileSize',
            'labelMaxFileSizeExceeded',
            'labelMaxFileSize',
            'labelMaxTotalFileSizeExceeded',
            'labelMaxTotalFileSize',
        ],
        'nms\filepond\FileTypeValidationAsset' => [
            'allowFileTypeValidation',
            'acceptedFileTypes',
            'labelFileTypeNotAllowed',
            'fileValidateTypeLabelExpectedTypes',
            'fileValidateTypeLabelExpectedTypesMap',
            'fileValidateTypeDetectType',
        ],
        'nms\filepond\ImageExifOrientationAsset' => [
            'allowImageExifOrientation',
        ],
        'nms\filepond\ImageCropAsset' => [
            'allowImageCrop',
            'imageCropAspectRatio',
        ],
        'nms\filepond\ImageEditAsset' => [
            'allowImageEdit',
            'styleImageEditButtonEditItemPosition',
            'imageEditInstantEdit',
            'imageEditAllowEdit',
            'imageEditIconEdit',
            'imageEditEditor',
        ],
        'nms\filepond\ImageFilterAsset' => [
            'allowImageFilter',
            'imageFilterColorMatrix',
        ],
        'nms\filepond\ImagePreviewAsset' => [
            'allowImagePreview',
            'imagePreviewMinHeight',
            'imagePreviewMaxHeight',
            'imagePreviewHeight',
            'imagePreviewMaxFileSize',
            'imagePreviewTransparencyIndicator',
            'imagePreviewMaxInstantPreviewFileSize',
            'imagePreviewMarkupShow',
            'imagePreviewMarkupFilter',
        ],
        'nms\filepond\ImageResizeAsset' => [
            'allowImageResize',
            'imageResizeTargetWidth',
            'imageResizeTargetHeight',
            'imageResizeMode',
            'imageResizeUpscale',
        ],
        'nms\filepond\ImageSizeValidationAsset' => [
            'allowImageValidateSize',
            'imageValidateSizeMinWidth',
            'imageValidateSizeMaxWidth',
            'imageValidateSizeMinHeight',
            'imageValidateSizeMaxHeight',
            'imageValidateSizeLabelFormatError',
            'imageValidateSizeLabelImageSizeTooSmall',
            'imageValidateSizeLabelImageSizeTooBig',
            'imageValidateSizeLabelExpectedMinSize',
            'imageValidateSizeLabelExpectedMaxSize',
            'imageValidateSizeMinResolution',
            'imageValidateSizeMaxResolution',
            'imageValidateSizeLabelImageResolutionTooLow',
            'imageValidateSizeLabelImageResolutionTooHigh',
            'imageValidateSizeLabelExpectedMinResolution',
            'imageValidateSizeLabelExpectedMaxResolution',
            'imageValidateSizeMeasure',
        ],
        'nms\filepond\ImageTransformAsset' => [
            'allowImageTransform',
            'imageTransformOutputMimeType',
            'imageTransformOutputQuality',
            'imageTransformOutputQualityMode',
            'imageTransformOutputStripImageHead',
            'imageTransformClientTransforms',
            'imageTransformVariants',
            'imageTransformVariantsIncludeDefault',
            'imageTransformVariantsDefaultName',
            'imageTransformVariantsIncludeOriginal',
            'imageTransformVariantsOriginalName',
            'imageTransformBeforeCreateBlob',
            'imageTransformAfterCreateBlob',
            'imageTransformCanvasMemoryLimit',
        ],
    ];

    /**
     * Registers all needed assets for view
     * @param array $filePond FilePond options from widget
     * @param View $view
     */
    public static function register($filePond, $view)
    {
        foreach (self::OPTIONS_MAP as $assetName => $options) {
            foreach ($options as $option) {
                if (array_key_exists($option, $filePond)) {
                    $assetName::register($view);
                    break;
                }
            }
        }
    }
}
