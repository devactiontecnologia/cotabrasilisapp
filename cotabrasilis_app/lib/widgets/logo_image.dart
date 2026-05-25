import 'package:flutter/material.dart';

/// Logo Cota Brasilis - asset assets/images/logo.png
class LogoImage extends StatelessWidget {
  final double? height;
  final double? width;
  final BoxFit fit;

  const LogoImage({
    super.key,
    this.height,
    this.width,
    this.fit = BoxFit.contain,
  });

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/logo.png',
      height: height,
      width: width,
      fit: fit,
      errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported, size: 48),
    );
  }
}
