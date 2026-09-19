import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/widgets/role_menu_drawer.dart';
import 'package:mobile/screens/privacy_policy_screen.dart';
import 'package:mobile/screens/active_call_screen.dart';
import 'package:mobile/screens/map_screen.dart';
import 'package:mobile/screens/eatery_detail_screen.dart';
import 'package:mobile/screens/create_story_screen.dart';

void main() {
  group('Kiểm thử giao diện không chứa thuật ngữ kỹ thuật', () {
    testWidgets('RoleMenuDrawer không chứa (WebRTC), (EULA), Dashboard', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(
            drawer: RoleMenuDrawer(activeRole: 'user'),
          ),
        ),
      );

      final scaffoldState = tester.state<ScaffoldState>(find.byType(Scaffold));
      scaffoldState.openDrawer();
      await tester.pumpAndSettle();

      expect(find.text('Tin nhắn & Gọi điện', skipOffstage: false), findsOneWidget);
      expect(find.text('Chính sách bảo mật & Điều khoản', skipOffstage: false), findsOneWidget);

      expect(find.textContaining('(WebRTC)', skipOffstage: false), findsNothing);
      expect(find.textContaining('(EULA)', skipOffstage: false), findsNothing);
      expect(find.textContaining('Dashboard', skipOffstage: false), findsNothing);
    });

    testWidgets('PrivacyPolicyScreen không chứa (EULA), (UGC), (Zero Tolerance), (Block User)', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: PrivacyPolicyScreen(),
        ),
      );
      await tester.pumpAndSettle();

      expect(find.text('Điều khoản sử dụng', skipOffstage: false), findsOneWidget);
      expect(find.text('Chính sách bảo mật', skipOffstage: false), findsOneWidget);
      expect(find.text('Quy chuẩn nội dung người dùng', skipOffstage: false), findsOneWidget);
      expect(find.text('1. Chính sách không khoan nhượng', skipOffstage: false), findsOneWidget);
      expect(find.text('3. Quyền Chặn người dùng', skipOffstage: false), findsOneWidget);

      expect(find.textContaining('(EULA)', skipOffstage: false), findsNothing);
      expect(find.textContaining('(UGC)', skipOffstage: false), findsNothing);
      expect(find.textContaining('Zero Tolerance', skipOffstage: false), findsNothing);
      expect(find.textContaining('Block User', skipOffstage: false), findsNothing);
    });

    testWidgets('ActiveCallScreen hiển thị Đang đổ chuông không chứa số giây đếm ngược', (tester) async {
      int endedDuration = -1;
      await tester.pumpWidget(
        MaterialApp(
          home: ActiveCallScreen(
            friendName: 'Nguyễn Văn A',
            friendId: 101,
            isCaller: true,
            isVideo: false,
            onCallEnded: (sec) {
              endedDuration = sec;
            },
          ),
        ),
      );

      await tester.pump();

      // Hiển thị trạng thái đổ chuông tự nhiên
      expect(find.text('Đang đổ chuông...', skipOffstage: false), findsOneWidget);
      expect(find.text('Nguyễn Văn A', skipOffstage: false), findsOneWidget);

      // Không hiển thị số giây đếm ngược
      expect(find.textContaining('(90s)', skipOffstage: false), findsNothing);
      expect(find.textContaining('s)...', skipOffstage: false), findsNothing);

      // Sau 90 giây timeout
      await tester.pump(const Duration(seconds: 91));
      await tester.pump();
      expect(endedDuration, equals(0));
    });

    testWidgets('MapScreen khởi tạo mượt mà và hiển thị thanh tìm kiếm, danh mục', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: MapScreen(),
        ),
      );
      await tester.pump(const Duration(milliseconds: 500));

      expect(find.byType(MapScreen), findsOneWidget);
      expect(find.byType(TextField), findsOneWidget);
      expect(find.text('Tất cả'), findsOneWidget);
      expect(find.text('Ẩm thực'), findsOneWidget);
    });
    testWidgets('EateryDetailScreen hiển thị chi tiết cơ sở y tế / bệnh viện đầy đủ', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: EateryDetailScreen(
            categorySlug: 'wellness-care',
            eaterySlug: 'benh-vien-da-khoa-dong-anh',
            initialData: {
              'id': 100,
              'name': 'Bệnh viện Đa khoa Đông Anh',
              'address': 'Đường Cao Lỗ, Uy Nỗ, Đông Anh, Hà Nội',
              'phone': '02438832438',
              'opening_hours': 'Cấp cứu 24/24',
              'wellness_services': [
                {
                  'name': 'Khoa Cấp cứu & Hồi sức tích cực',
                  'description': 'Tiếp nhận cấp cứu 24/7 với trang thiết bị hiện đại',
                  'price': '0',
                  'duration': '24/7',
                },
                {
                  'name': 'Chụp cắt lớp vi tính CT Scanner',
                  'description': 'Chẩn đoán hình ảnh kỹ thuật cao',
                  'price': '1200000',
                  'duration': '30 phút',
                },
              ],
            },
          ),
        ),
      );
      await tester.pump();

      expect(find.text('Bệnh viện Đa khoa Đông Anh'), findsOneWidget);
      expect(find.text('Cấp cứu 24/24'), findsOneWidget);
      expect(find.text('02438832438'), findsOneWidget);
      expect(find.text('Gọi điện'), findsOneWidget);
      expect(find.text('Khoa Cấp cứu & Hồi sức tích cực'), findsOneWidget);
      expect(find.text('Chụp cắt lớp vi tính CT Scanner'), findsOneWidget);
      expect(find.textContaining('Bệnh viện Đa khoa Đông Anh là cơ sở y tế'), findsOneWidget);
    });

    testWidgets('EateryDetailScreen hiển thị chi tiết cơ sở kinh doanh không có mô tả với fallback thông minh', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: EateryDetailScreen(
            categorySlug: 'co-so-kinh-doanh',
            eaterySlug: 'cong-ty-tnhh-co-khi-dong-anh',
            initialData: {
              'id': 200,
              'name': 'Công ty TNHH Cơ khí Đông Anh',
              'address': 'Tổ 15, Thị trấn Đông Anh',
              'phone': '0912345678',
              'opening_hours': '08:00 - 17:30',
              'price_range': 'Liên hệ báo giá',
              'commune_name': 'Thị trấn Đông Anh',
            },
          ),
        ),
      );
      await tester.pump();

      expect(find.text('Công ty TNHH Cơ khí Đông Anh'), findsOneWidget);
      expect(find.text('08:00 - 17:30'), findsOneWidget);
      expect(find.text('Liên hệ báo giá'), findsOneWidget);
      expect(find.text('0912345678'), findsOneWidget);
      expect(find.text('Thị trấn Đông Anh'), findsOneWidget);
      expect(find.textContaining('là cơ sở sản xuất kinh doanh, thương mại dịch vụ uy tín'), findsOneWidget);
      expect(find.text('Chưa có đánh giá hoặc check-in nào'), findsOneWidget);
    });

    testWidgets('CreateStoryScreen hiển thị giao diện sáng tươi mới và mở được bộ chọn nhạc Facebook', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: CreateStoryScreen(),
        ),
      );
      await tester.pump();

      expect(find.text('Tạo Tin Mới'), findsOneWidget);
      expect(find.text('Văn bản'), findsOneWidget);
      expect(find.text('Máy ảnh'), findsOneWidget);
      expect(find.text('Âm nhạc'), findsOneWidget);
      expect(find.text('Cổ Loa'), findsOneWidget);
      expect(find.text('Thư viện thiết bị'), findsOneWidget);
      expect(find.text('Khám Phá & Check-in Đông Anh'), findsOneWidget);

      // Chạm vào nút Âm nhạc để mở bộ chọn nhạc chuẩn Facebook
      await tester.tap(find.text('Âm nhạc'));
      await tester.pump(const Duration(milliseconds: 500));

      expect(find.text('Âm nhạc cho Tin'), findsOneWidget);
      expect(find.text('Tìm kiếm bài hát, ca sĩ, giai điệu...'), findsOneWidget);
      expect(find.text('Thịnh hành 🔥'), findsOneWidget);
      expect(find.text('Đông Anh 🏛️'), findsOneWidget);

      // Đóng modal
      Navigator.of(tester.element(find.text('Âm nhạc cho Tin'))).pop();
      await tester.pump(const Duration(milliseconds: 500));

      // Chạm vào chế độ Văn bản
      await tester.tap(find.text('Văn bản'));
      await tester.pump(const Duration(milliseconds: 500));

      // Kiểm tra thanh công cụ biên tập và nút chia sẻ
      expect(find.text('Chia sẻ lên tin'), findsOneWidget);
      expect(find.byType(TextField), findsOneWidget);
    });
  });
}
