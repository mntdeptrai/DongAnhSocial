import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile/services/moderation_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    await ModerationService.init();
  });

  test('ModerationService blocks and unblocks user correctly', () async {
    const testUserId = 'test_user_99';
    expect(ModerationService.isUserBlocked(testUserId), isFalse);

    await ModerationService.blockUser(testUserId);
    expect(ModerationService.isUserBlocked(testUserId), isTrue);

    await ModerationService.unblockUser(testUserId);
    expect(ModerationService.isUserBlocked(testUserId), isFalse);
  });

  test('ModerationService hides and unhides post correctly', () async {
    const testPostId = 'post_123';
    expect(ModerationService.isPostHidden(testPostId), isFalse);

    await ModerationService.hidePost(testPostId);
    expect(ModerationService.isPostHidden(testPostId), isTrue);

    await ModerationService.unhidePost(testPostId);
    expect(ModerationService.isPostHidden(testPostId), isFalse);
  });

  test('ModerationService creates and resolves report tickets', () async {
    final initialCount = ModerationService.getReportTickets().length;
    expect(initialCount, greaterThanOrEqualTo(1));

    await ModerationService.reportContent(
      contentId: 'post_bad_99',
      contentType: 'post',
      reason: 'Spam',
      title: 'Bài viết spam',
      authorName: 'Spammer',
      authorId: 'spammer_1',
    );

    final updatedTickets = ModerationService.getReportTickets();
    expect(updatedTickets.length, equals(initialCount + 1));
    expect(updatedTickets.first['content_id'], equals('post_bad_99'));
    expect(updatedTickets.first['status'], equals('pending'));

    final ticketId = updatedTickets.first['id'];
    await ModerationService.resolveReportTicket(ticketId: ticketId, action: 'remove_content');

    final afterResolve = ModerationService.getReportTickets();
    expect(afterResolve.first['status'], equals('resolved_removed'));
    expect(ModerationService.isPostHidden('post_bad_99'), isTrue);
  });

  test('ModerationService supports temporary ban durations and metadata', () async {
    const testUserId = 'temp_banned_user_1';
    expect(ModerationService.isUserBlocked(testUserId), isFalse);

    await ModerationService.banUserWithDuration(
      userId: testUserId,
      duration: const Duration(hours: 24),
      reason: 'Spam bình luận',
    );

    expect(ModerationService.isUserBlocked(testUserId), isTrue);
    final banInfo = ModerationService.getUserBanInfo(testUserId);
    expect(banInfo, isNotNull);
    expect(banInfo!['is_banned'], isTrue);
    expect(banInfo['is_permanent'], isFalse);
    expect(banInfo['reason'], equals('Spam bình luận'));

    await ModerationService.unblockUser(testUserId);
    expect(ModerationService.isUserBlocked(testUserId), isFalse);
  });
}
