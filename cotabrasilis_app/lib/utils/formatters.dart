String formatPrice(num? value) {
  if (value == null) return '-';
  return 'R\$ ${value.toStringAsFixed(0)}';
}

String formatDate(String? dateStr) {
  if (dateStr == null) return '-';
  try {
    final dt = DateTime.parse(dateStr);
    final day = dt.day.toString().padLeft(2, '0');
    final month = dt.month.toString().padLeft(2, '0');
    return '$day/$month/${dt.year}';
  } catch (_) {
    return dateStr;
  }
}
