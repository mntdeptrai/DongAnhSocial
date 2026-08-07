import 'package:flutter/material.dart';

/// A memory-optimized Image.network wrapper for low-RAM devices (e.g. 2GB RAM).
/// Automatically calculates [cacheWidth] and [cacheHeight] to downsample 
/// high-resolution network images in memory instead of decoding full 4K/HD bitmaps into RAM.
class OptimizedNetworkImage extends StatelessWidget {
  final String imageUrl;
  final double? width;
  final double? height;
  final BoxFit fit;
  final int? cacheWidth;
  final int? cacheHeight;
  final WidgetBuilder? placeholder;
  final WidgetBuilder? errorWidget;
  final FilterQuality filterQuality;
  final Alignment alignment;

  const OptimizedNetworkImage({
    super.key,
    required this.imageUrl,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.cacheWidth,
    this.cacheHeight,
    this.placeholder,
    this.errorWidget,
    this.filterQuality = FilterQuality.low,
    this.alignment = Alignment.center,
  });

  @override
  Widget build(BuildContext context) {
    if (imageUrl.isEmpty) {
      return errorWidget != null ? errorWidget!(context) : _defaultErrorWidget();
    }

    // Auto-calculate cache dimensions to avoid loading oversized uncompressed Bitmaps into RAM.
    final double devicePixelRatio = MediaQuery.maybeOf(context)?.devicePixelRatio ?? 2.0;
    
    final int? calculatedCacheWidth = cacheWidth ?? 
        (width != null && width!.isFinite && width! > 0 ? (width! * devicePixelRatio).round() : null);

    final int? calculatedCacheHeight = cacheHeight ?? 
        (height != null && height!.isFinite && height! > 0 ? (height! * devicePixelRatio).round() : null);

    return Image.network(
      imageUrl,
      width: width,
      height: height,
      fit: fit,
      alignment: alignment,
      cacheWidth: calculatedCacheWidth,
      cacheHeight: calculatedCacheHeight,
      filterQuality: filterQuality,
      errorBuilder: (context, error, stackTrace) {
        return errorWidget != null ? errorWidget!(context) : _defaultErrorWidget();
      },
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) return child;
        return placeholder != null 
            ? placeholder!(context) 
            : _defaultPlaceholder(loadingProgress);
      },
    );
  }

  Widget _defaultPlaceholder(ImageChunkEvent loadingProgress) {
    return Container(
      width: width,
      height: height,
      color: const Color(0xFFF1F5F9),
      child: Center(
        child: SizedBox(
          width: 20,
          height: 20,
          child: CircularProgressIndicator(
            strokeWidth: 2,
            value: loadingProgress.expectedTotalBytes != null
                ? loadingProgress.cumulativeBytesLoaded / loadingProgress.expectedTotalBytes!
                : null,
            color: const Color(0xFF0EA5E9),
          ),
        ),
      ),
    );
  }

  Widget _defaultErrorWidget() {
    return Container(
      width: width,
      height: height,
      color: const Color(0xFFF8FAFC),
      child: const Center(
        child: Icon(
          Icons.image_not_supported_outlined,
          color: Color(0xFF94A3B8),
          size: 24,
        ),
      ),
    );
  }
}

/// Helper function to create an avatar ImageProvider with memory bounding (ResizeImage)
ImageProvider optimizedAvatarProvider(String url, {int targetWidth = 120}) {
  return ResizeImage(
    NetworkImage(url),
    width: targetWidth,
  );
}
