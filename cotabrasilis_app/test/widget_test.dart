import 'package:flutter_test/flutter_test.dart';
import 'package:cotabrasilis_app/main.dart';

void main() {
  testWidgets('App loads', (WidgetTester tester) async {
    await tester.pumpWidget(const CotaBrasilisApp());
    await tester.pumpAndSettle();
    expect(find.text('Cota Brasilis'), findsOneWidget);
  });
}
