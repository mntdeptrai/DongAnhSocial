import 'package:flutter/foundation.dart';
import 'api_service.dart';

class CartService {
  static final ValueNotifier<int> cartCountNotifier = ValueNotifier<int>(0);

  static int get cartCount => cartCountNotifier.value;

  /// Tải lại số lượng sản phẩm thực tế trong giỏ hàng từ Backend API thời gian thực
  static Future<int> refreshCartCount() async {
    try {
      final cartRes = await ApiService.getCart();
      int totalQty = 0;
      if (cartRes is Map && cartRes['success'] == true && cartRes['data'] is List) {
        final List items = cartRes['data'];
        for (var item in items) {
          final int q = item['quantity'] is int
              ? item['quantity']
              : (int.tryParse(item['quantity']?.toString() ?? '1') ?? 1);
          totalQty += q;
        }
      }
      cartCountNotifier.value = totalQty;
      return totalQty;
    } catch (_) {
      return cartCountNotifier.value;
    }
  }

  /// Cập nhật số lượng giỏ hàng trực tiếp tức thì
  static void updateCountLocally(int count) {
    cartCountNotifier.value = count < 0 ? 0 : count;
  }
}
