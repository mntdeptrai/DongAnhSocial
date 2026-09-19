import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    // Build our DongAnhSocial app and trigger a frame.
    await tester.pumpWidget(const MyApp());
    expect(find.byType(MyApp), findsOneWidget);
    await tester.pump(const Duration(milliseconds: 1500));
    await tester.pumpWidget(const SizedBox());
    await tester.pump();
  });
}
