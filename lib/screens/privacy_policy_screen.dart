import 'package:flutter/material.dart';

class PrivacyPolicyScreen extends StatefulWidget {
  final int initialTabIndex;

  const PrivacyPolicyScreen({super.key, this.initialTabIndex = 0});

  @override
  State<PrivacyPolicyScreen> createState() => _PrivacyPolicyScreenState();
}

class _PrivacyPolicyScreenState extends State<PrivacyPolicyScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this, initialIndex: widget.initialTabIndex);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Color(0xFF0F172A), size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Chính sách & Điều khoản',
          style: TextStyle(
            color: Color(0xFF0F172A),
            fontSize: 17,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
        bottom: TabBar(
          controller: _tabController,
          labelColor: primaryColor,
          unselectedLabelColor: const Color(0xFF64748B),
          indicatorColor: primaryColor,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: const [
            Tab(text: 'Điều khoản sử dụng'),
            Tab(text: 'Chính sách bảo mật'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildEulaTab(),
          _buildPrivacyTab(),
        ],
      ),
    );
  }

  Widget _buildEulaTab() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildNoticeBanner(
          icon: Icons.gavel_rounded,
          color: const Color(0xFFDC2626),
          title: 'Quy chuẩn nội dung người dùng',
          subtitle: 'Không khoan nhượng đối với nội dung độc hại & vi phạm pháp luật',
        ),
        const SizedBox(height: 16),
        _buildSectionCard(
          title: '1. Chính sách không khoan nhượng',
          content:
              'Đông Anh Social nghiêm cấm tuyệt đối mọi hành vi đăng tải, phát tán các nội dung sau:\n'
              '• Nội dung phản động, chống phá pháp luật, kích động bạo lực hoặc thù hận sắc tộc/tôn giáo.\n'
              '• Hình ảnh khiêu dâm, dung tục, đồi trụy hoặc quấy rối tình dục.\n'
              '• Hành vi lăng mạ, bôi nhọ, xúc phạm danh dự nhân phẩm cá nhân, tổ chức.\n'
              '• Hành vi lừa đảo, phát tán mã độc, spam quảng cáo trái phép.\n'
              '• Vi phạm bản quyền sở hữu trí tuệ hoặc xâm phạm bí mật đời tư.',
        ),
        const SizedBox(height: 12),
        _buildSectionCard(
          title: '2. Cơ chế xử lý vi phạm trong 24 giờ',
          content:
              'Khi người dùng nhấn "Báo cáo vi phạm" trên bất kỳ bài viết, video hoặc bình luận nào:\n'
              '• Đội ngũ kiểm duyệt Đông Anh Social tiếp nhận và xử lý trong vòng 24 giờ.\n'
              '• Nội dung vi phạm sẽ bị gỡ bỏ ngay lập tức.\n'
              '• Tài khoản vi phạm có thể bị khóa tạm thời hoặc cấm vĩnh viễn (Ban) tùy mức độ vi phạm.',
        ),
        const SizedBox(height: 12),
        _buildSectionCard(
          title: '3. Quyền Chặn người dùng',
          content:
              'Bạn có toàn quyền chủ động bảo vệ trải nghiệm của mình:\n'
              '• Bạn có thể nhấn menu ba chấm (⋯) trên bài viết và chọn "Chặn người dùng này".\n'
              '• Ngay khi chặn, toàn bộ bài viết, bình luận và nội dung của người đó sẽ lập tức biến mất khỏi bảng tin của bạn.',
        ),
        const SizedBox(height: 12),
        _buildSectionCard(
          title: '4. Trách nhiệm người đăng tải',
          content:
              'Người dùng tự chịu trách nhiệm hoàn toàn trước pháp luật về tính chính xác, bản quyền hình ảnh và thông tin trong các bài viết, bài đánh giá địa điểm du lịch, ẩm thực và sản phẩm OCOP đăng trên nền tảng.',
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  Widget _buildPrivacyTab() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildNoticeBanner(
          icon: Icons.shield_rounded,
          color: const Color(0xFF0EA5E9),
          title: 'Bảo vệ dữ liệu cá nhân',
          subtitle: 'Cam kết minh bạch và tuân thủ các tiêu chuẩn bảo mật quốc tế',
        ),
        const SizedBox(height: 16),
        _buildSectionCard(
          title: '1. Dữ liệu chúng tôi thu thập',
          content:
              '• Thông tin tài khoản: Họ tên, số điện thoại, địa chỉ email khi bạn đăng ký hoặc đặt mua sản phẩm OCOP.\n'
              '• Quyền Vị trí (GPS): Chỉ được sử dụng khi bạn bật tính năng Check-in địa điểm du lịch, trải nghiệm Cổ Loa hoặc tìm kiếm quán ăn lân cận.\n'
              '• Quyền Camera & Thư viện ảnh: Chỉ kích hoạt khi bạn chọn tải ảnh bài viết, cập nhật ảnh đại diện hoặc quét mã QR.\n'
              '• Dữ liệu phân tích kỹ thuật: Thông tin thiết bị và phiên làm việc nhằm tối ưu hiệu năng và ngăn chặn spam.',
        ),
        const SizedBox(height: 12),
        _buildSectionCard(
          title: '2. Mục đích sử dụng dữ liệu',
          content:
              '• Xác thực danh tính và duy trì hoạt động tài khoản của bạn trên nền tảng.\n'
              '• Xử lý và giao nhận đơn hàng sản phẩm OCOP đặc sản Đông Anh.\n'
              '• Cải thiện chất lượng bản đồ số du lịch và đề xuất trải nghiệm văn hóa phù hợp.\n'
              '• Chúng tôi tuyệt đối KHÔNG bán, cho thuê hoặc chia sẻ dữ liệu cá nhân cho bên thứ ba vì mục đích quảng cáo thương mại ngoài ý muốn.',
        ),
        const SizedBox(height: 12),
        _buildSectionCard(
          title: '3. Quyền Xóa tài khoản & Dữ liệu (Account Deletion)',
          content:
              'Bạn có toàn quyền yêu cầu xóa vĩnh viễn tài khoản và dữ liệu cá nhân của mình bất kỳ lúc nào:\n'
              '• Bạn có thể thực hiện trực tiếp tại trang Cá nhân > Cài đặt tài khoản > "Xóa tài khoản vĩnh viễn".\n'
              '• Khi xác nhận xóa, toàn bộ thông tin tài khoản, lịch sử check-in, đơn hàng và các token phiên làm việc sẽ được xóa hoàn toàn khỏi máy chủ hệ thống.',
        ),
        const SizedBox(height: 12),
        _buildSectionCard(
          title: '4. Đầu mối hỗ trợ & Khiếu nại',
          content:
              'Mọi thắc mắc, khiếu nại về quyền riêng tư hoặc quyết định kiểm duyệt xin vui lòng liên hệ:\n'
              '• Đơn vị: Cổng Thông Tin Điện Tử Đông Anh Social\n'
              '• Email tiếp nhận: bantin@xadonganh.com\n'
              '• Tổng đài hỗ trợ: 1900 8686 (Giờ hành chính)\n'
              '• Địa chỉ: Huyện Đông Anh, Thành phố Hà Nội',
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  Widget _buildNoticeBanner({
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: color),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionCard({required String title, required String content}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            content,
            style: const TextStyle(
              fontSize: 13,
              color: Color(0xFF475569),
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }
}
