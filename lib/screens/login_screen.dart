import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';

class LoginScreen extends StatefulWidget {
  final VoidCallback onLoginSuccess;
  final VoidCallback onSkip;

  const LoginScreen({
    super.key,
    required this.onLoginSuccess,
    required this.onSkip,
  });

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _usernameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  String _selectedRole = 'user';
  bool _agreeTerms = true;
  bool _isRegister = false;
  bool _isLoading = false;
  bool _obscurePassword = true;
  String? _errorMessage;

  @override
  void dispose() {
    _nameController.dispose();
    _usernameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _showTermsDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Text('📜 ', style: TextStyle(fontSize: 22)),
            Expanded(
              child: Text(
                'Điều Khoản & Bảo Mật Thông Tin',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: const [
              Text('1. Mục Đích Thu Thập Thông Tin Cá Nhân', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              SizedBox(height: 4),
              Text(
                '• Thu thập Họ tên, Email, Số điện thoại, Vai trò tài khoản để xác thực tài khoản qua OTP.\n'
                '• Cho phép đăng bài check-in, bình luận quán ăn & tham gia Food Tour.\n'
                '• Hỗ trợ chủ gian hàng số quản lý sản phẩm OCOP và mã VietQR.',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569), height: 1.5),
              ),
              SizedBox(height: 12),
              Text('2. Phạm Vi Thu Thập & Lưu Trữ Dữ Liệu', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              SizedBox(height: 4),
              Text(
                '• Thông tin định danh: Họ tên, Email, SĐT, Username, Mật khẩu mã hóa Bcrypt.\n'
                '• Vị trí GPS (khi cho phép) để hiển thị địa điểm xung quanh.',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569), height: 1.5),
              ),
              SizedBox(height: 12),
              Text('3. Cam Kết Bảo Mật Thông Tin', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              SizedBox(height: 4),
              Text(
                'Cam kết không chia sẻ hoặc bán dữ liệu cá nhân cho bên thứ ba vì mục đích thương mại.',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569), height: 1.5),
              ),
              SizedBox(height: 12),
              Text('4. Quy Định Sử Dụng & Quyền Hạn', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              SizedBox(height: 4),
              Text(
                '• Không đăng tải nội dung độc hại, sai sự thật.\n'
                '• Bạn có quyền cập nhật thông tin hoặc xóa tài khoản bất kỳ lúc nào.',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569), height: 1.5),
              ),
            ],
          ),
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Đã hiểu & Đóng', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_isRegister && !_agreeTerms) {
      setState(() {
        _errorMessage = 'Bạn cần đồng ý với Điều khoản dịch vụ & Thu thập thông tin để tiếp tục.';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    Map<String, dynamic> result;
    if (_isRegister) {
      result = await ApiService.register(
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        password: _passwordController.text,
        username: _usernameController.text.trim(),
        phone: _phoneController.text.trim(),
        role: _selectedRole,
        agreeTerms: _agreeTerms,
      );
      if (result['success'] == true) {
        // Auto login after registration
        result = await ApiService.login(
          _emailController.text.trim(),
          _passwordController.text,
        );
      }
    } else {
      result = await ApiService.login(
        _emailController.text.trim(),
        _passwordController.text,
      );
    }

    if (mounted) {
      setState(() {
        _isLoading = false;
      });

      if (result['success'] == true) {
        widget.onLoginSuccess();
      } else {
        setState(() {
          _errorMessage = result['message'] ?? 'Đã xảy ra lỗi.';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);
    const accentColor = Color(0xFF06B6D4);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Stack(
        children: [
          // Background Gradient Decorative Bubbles
          Positioned(
            top: -60,
            right: -60,
            child: Container(
              width: 240,
              height: 240,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: primaryColor.withValues(alpha: 0.15),
              ),
            ),
          ),
          Positioned(
            bottom: -80,
            left: -80,
            child: Container(
              width: 280,
              height: 280,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: accentColor.withValues(alpha: 0.12),
              ),
            ),
          ),

          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
                child: Form(
                  key: _formKey,
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Modern Glassmorphic App Branding Card
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(28),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF0EA5E9).withValues(alpha: 0.35),
                              blurRadius: 20,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: const BoxDecoration(
                                color: Colors.white24,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.map_rounded, color: Colors.white, size: 40),
                            ),
                            const SizedBox(height: 12),
                            const Text(
                              'Đông Anh Social',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 26,
                                fontWeight: FontWeight.w900,
                                color: Colors.white,
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Bản đồ Ẩm thực • Chợ số OCOP • Check-in Cổ Loa',
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 12, color: Colors.white70, fontWeight: FontWeight.w500),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 28),

                      // Feature Tags Row
                      Wrap(
                        alignment: WrapAlignment.center,
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _buildFeatureBadge('🍜 Ẩm Thực', Colors.orange),
                          _buildFeatureBadge('🏆 OCOP 5★', Colors.green),
                          _buildFeatureBadge('📸 Check-in', Colors.lightBlue),
                        ],
                      ),
                      const SizedBox(height: 24),

                      Text(
                        _isRegister ? 'Tạo Tài Khoản Mới' : 'Đăng Nhập Hệ Thống',
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        _isRegister ? 'Nhập thông tin cá nhân để tham gia cộng đồng' : 'Vui lòng nhập Email và Mật khẩu của bạn',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                      ),
                      const SizedBox(height: 20),

                      // Error Message Banner
                      if (_errorMessage != null) ...[
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF2F2),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFFCA5A5)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.error_outline_rounded, color: Color(0xFFEF4444), size: 20),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  _errorMessage!,
                                  style: const TextStyle(color: Color(0xFF991B1B), fontSize: 13, fontWeight: FontWeight.w600),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),
                      ],

                      // Form Inputs
                      if (_isRegister) ...[
                        TextFormField(
                          controller: _nameController,
                          decoration: InputDecoration(
                            labelText: 'Họ và tên *',
                            hintText: 'Nhập họ và tên đầy đủ',
                            prefixIcon: const Icon(Icons.person_outline_rounded, color: primaryColor),
                            filled: true,
                            fillColor: Colors.white,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade300)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: primaryColor, width: 2)),
                          ),
                          validator: (v) => v == null || v.isEmpty ? 'Vui lòng nhập họ và tên' : null,
                        ),
                        const SizedBox(height: 14),

                        TextFormField(
                          controller: _usernameController,
                          decoration: InputDecoration(
                            labelText: 'Tên đăng nhập (Username) *',
                            hintText: 'Ví dụ: nguyenvana',
                            prefixIcon: const Icon(Icons.alternate_email_rounded, color: primaryColor),
                            filled: true,
                            fillColor: Colors.white,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade300)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: primaryColor, width: 2)),
                          ),
                          validator: (v) {
                            if (v == null || v.isEmpty) return 'Vui lòng nhập tên đăng nhập';
                            if (!RegExp(r'^[a-zA-Z0-9_.-]+$').hasMatch(v)) {
                              return 'Tên đăng nhập không được chứa khoảng trắng hoặc dấu tiếng Việt';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                      ],

                      TextFormField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: InputDecoration(
                          labelText: 'Địa chỉ Email *',
                          hintText: 'vi_du@gmail.com',
                          prefixIcon: const Icon(Icons.email_outlined, color: primaryColor),
                          filled: true,
                          fillColor: Colors.white,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade300)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: primaryColor, width: 2)),
                        ),
                        validator: (v) => v == null || !v.contains('@') ? 'Vui lòng nhập Email hợp lệ' : null,
                      ),
                      const SizedBox(height: 14),

                      if (_isRegister) ...[
                        TextFormField(
                          controller: _phoneController,
                          keyboardType: TextInputType.phone,
                          decoration: InputDecoration(
                            labelText: 'Số điện thoại liên hệ *',
                            hintText: 'Ví dụ: 0901234567',
                            prefixIcon: const Icon(Icons.phone_outlined, color: primaryColor),
                            filled: true,
                            fillColor: Colors.white,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade300)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: primaryColor, width: 2)),
                          ),
                          validator: (v) {
                            if (v == null || v.isEmpty) return 'Vui lòng nhập số điện thoại';
                            if (!RegExp(r'^0[0-9]{9}$').hasMatch(v)) return 'SĐT phải gồm 10 chữ số và bắt đầu bằng số 0';
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),

                        DropdownButtonFormField<String>(
                          value: _selectedRole,
                          decoration: InputDecoration(
                            labelText: 'Vai trò tài khoản *',
                            prefixIcon: const Icon(Icons.badge_outlined, color: primaryColor),
                            filled: true,
                            fillColor: Colors.white,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade300)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: primaryColor, width: 2)),
                          ),
                          items: const [
                            DropdownMenuItem(value: 'user', child: Text('👤 Người dùng trải nghiệm')),
                            DropdownMenuItem(value: 'seller', child: Text('🏪 Chủ cơ sở, gian hàng số')),
                          ],
                          onChanged: (val) {
                            if (val != null) setState(() => _selectedRole = val);
                          },
                        ),
                        const SizedBox(height: 14),
                      ],

                      TextFormField(
                        controller: _passwordController,
                        obscureText: _obscurePassword,
                        decoration: InputDecoration(
                          labelText: 'Mật khẩu *',
                          hintText: '••••••••',
                          prefixIcon: const Icon(Icons.lock_outline_rounded, color: primaryColor),
                          suffixIcon: IconButton(
                            icon: Icon(_obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined, color: Colors.grey),
                            onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                          ),
                          filled: true,
                          fillColor: Colors.white,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade300)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: primaryColor, width: 2)),
                        ),
                        validator: (v) => v == null || v.length < 6 ? 'Mật khẩu phải có ít nhất 6 ký tự' : null,
                      ),
                      const SizedBox(height: 14),

                      // Checkbox đồng ý Điều khoản & Thu thập thông tin khi Đăng ký
                      if (_isRegister) ...[
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            SizedBox(
                              width: 24,
                              height: 24,
                              child: Checkbox(
                                value: _agreeTerms,
                                activeColor: primaryColor,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                onChanged: (val) {
                                  setState(() => _agreeTerms = val ?? false);
                                },
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Wrap(
                                crossAxisAlignment: WrapCrossAlignment.center,
                                children: [
                                  const Text(
                                    'Tôi đã đọc, hiểu rõ và đồng ý với ',
                                    style: TextStyle(fontSize: 12, color: Color(0xFF475569)),
                                  ),
                                  GestureDetector(
                                    onTap: _showTermsDialog,
                                    child: const Text(
                                      'Điều khoản dịch vụ & Chính sách bảo mật',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: primaryColor,
                                        fontWeight: FontWeight.bold,
                                        decoration: TextDecoration.underline,
                                      ),
                                    ),
                                  ),
                                  const Text(
                                    ' của Đông Anh Social.',
                                    style: TextStyle(fontSize: 12, color: Color(0xFF475569)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                      ],
                      const SizedBox(height: 24),

                      // Golden Amber CTA Submit Button
                      Container(
                        height: 52,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
                          ),
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFFF59E0B).withValues(alpha: 0.35),
                              blurRadius: 12,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: ElevatedButton(
                          onPressed: _isLoading ? null : _submit,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          ),
                          child: _isLoading
                              ? const ButtonDotsLoader(color: Colors.white, size: 7.0)
                              : Text(
                                  _isRegister ? 'ĐĂNG KÝ TÀI KHOẢN ✨' : 'ĐĂNG NHẬP NGAY ✨',
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Colors.white),
                                ),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Switch Register / Login
                      TextButton(
                        onPressed: () {
                          setState(() {
                            _isRegister = !_isRegister;
                            _errorMessage = null;
                          });
                        },
                        child: Text(
                          _isRegister ? 'Đã có tài khoản? Đăng nhập ngay' : 'Chưa có tài khoản? Đăng ký ngay',
                          style: const TextStyle(color: primaryColor, fontWeight: FontWeight.bold, fontSize: 14),
                        ),
                      ),

                      const Divider(height: 28),

                      // Guest Mode Button
                      OutlinedButton.icon(
                        onPressed: widget.onSkip,
                        icon: const Icon(Icons.arrow_forward_rounded, size: 18),
                        label: const Text('Bỏ qua (Trải nghiệm Khách vãng lai)'),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          side: BorderSide(color: Colors.grey.shade300),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          foregroundColor: const Color(0xFF475569),
                          backgroundColor: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFeatureBadge(String label, MaterialColor color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 4, offset: const Offset(0, 2)),
        ],
      ),
      child: Text(
        label,
        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
      ),
    );
  }
}
