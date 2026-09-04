# Image processing

- `Base\Image::setBody()` derives MIME type and dimensions from binary content; optional EXIF metadata must never prevent a valid image from loading.
- Prefer the MIME reported by `getimagesizefromstring()` for image bodies. The system `fileinfo` database can classify valid GD-generated WebP bodies as `application/octet-stream`, which would select the wrong encoder and fail validation on a subsequent resize.
- Call `exif_read_data()` only for JPEG and TIFF bodies. PHP emits warnings or returns failure for valid formats such as WebP and PNG, so those formats use an empty metadata array.
- Laminas' file validator can reject a valid generated WebP stored in an extensionless temporary stream. For WebP only, fall back to `getimagesizefromstring()` and require non-zero dimensions plus the exact `image/webp` MIME type.
- GD resizing preserves the source MIME type. Runtime images that consume this library must compile GD with the codecs they serve, especially JPEG and WebP.
- Validate WebP changes with a generated WebP body passed through `setBody()` and `resizeImage()`, asserting both the resulting MIME type and dimensions.
