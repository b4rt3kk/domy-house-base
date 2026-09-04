# Image processing

- `Base\Image::setBody()` derives MIME type and dimensions from binary content; optional EXIF metadata must never prevent a valid image from loading.
- Call `exif_read_data()` only for JPEG and TIFF bodies. PHP emits warnings or returns failure for valid formats such as WebP and PNG, so those formats use an empty metadata array.
- GD resizing preserves the source MIME type. Runtime images that consume this library must compile GD with the codecs they serve, especially JPEG and WebP.
- Validate WebP changes with a generated WebP body passed through `setBody()` and `resizeImage()`, asserting both the resulting MIME type and dimensions.
