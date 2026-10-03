import 'package:flutter/material.dart';
import '../services/api_service.dart';

class PersonalInfoScreen extends StatefulWidget {
  final bool isModal;
  final ValueChanged<Map<String, dynamic>>? onSaved;

  const PersonalInfoScreen({
    super.key,
    this.isModal = false,
    this.onSaved,
  });

  /// Helper to open as a modern Apple-style Modal Bottom Sheet
  static Future<Map<String, dynamic>?> showModal(BuildContext context, {ValueChanged<Map<String, dynamic>>? onSaved}) {
    return showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => FractionallySizedBox(
        heightFactor: 0.92,
        child: ClipRRect(
          borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          child: PersonalInfoScreen(isModal: true, onSaved: onSaved),
        ),
      ),
    );
  }

  @override
  State<PersonalInfoScreen> createState() => _PersonalInfoScreenState();
}

class _PersonalInfoScreenState extends State<PersonalInfoScreen> {
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  late TextEditingController _addressController;
  late TextEditingController _bioController;

  String? _selectedCommune;
  String _selectedGender = 'male'; // 'male', 'female', 'other'
  DateTime? _selectedBirthday;
  bool _isSaving = false;

  // 24 Đơn vị hành chính cấp xã/thị trấn tại Huyện Đông Anh
  static const List<String> dongAnhCommunes = [
    'Thị trấn Đông Anh',
    'Xã Cổ Loa',
    'Xã Uy Nỗ',
    'Xã Tiên Dương',
    'Xã Đông Hội',
    'Xã Hải Bối',
    'Xã Kim Nỗ',
    'Xã Vĩnh Ngọc',
    'Xã Xuân Canh',
    'Xã Việt Hùng',
    'Xã Mai Lâm',
    'Xã Dục Tú',
    'Xã Vân Nội',
    'Xã Nam Hồng',
    'Xã Bắc Hồng',
    'Xã Nguyên Khê',
    'Xã Thụy Lâm',
    'Xã Liên Hà',
    'Xã Vân Hà',
    'Xã Kim Chung',
    'Xã Đại Mạch',
    'Xã Võng La',
    'Xã Tàm Xá',
    'Xã Xuân Nộn',
  ];

  @override
  void initState() {
    super.initState();
    final user = ApiService.currentUser ?? {};
    _nameController = TextEditingController(text: (user['name'] ?? '').toString());
    _phoneController = TextEditingController(text: (user['phone'] ?? '').toString());
    _addressController = TextEditingController(text: (user['address'] ?? '').toString());
    _bioController = TextEditingController(text: (user['bio'] ?? '').toString());

    _selectedCommune = user['commune']?.toString();
    if (_selectedCommune != null && !dongAnhCommunes.contains(_selectedCommune)) {
      // Nếu địa chỉ lưu trước đó thiếu tiền tố 'Xã'
      final match = dongAnhCommunes.firstWhere(
        (c) => c.toLowerCase().contains(_selectedCommune!.toLowerCase()),
        orElse: () => _selectedCommune!,
      );
      _selectedCommune = match;
    }

    _selectedGender = (user['gender'] ?? 'male').toString();
    if (!['male', 'female', 'other'].contains(_selectedGender)) {
      _selectedGender = 'male';
    }

    if (user['birthday'] != null) {
      try {
        _selectedBirthday = DateTime.tryParse(user['birthday'].toString());
      } catch (_) {}
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _bioController.dispose();
    super.dispose();
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    final res = await ApiService.updateProfile(
      name: _nameController.text.trim(),
      phone: _phoneController.text.trim(),
      address: _addressController.text.trim(),
      commune: _selectedCommune?.trim(),
      bio: _bioController.text.trim(),
      gender: _selectedGender,
      birthday: _selectedBirthday != null
          ? "${_selectedBirthday!.year}-${_selectedBirthday!.month.toString().padLeft(2, '0')}-${_selectedBirthday!.day.toString().padLeft(2, '0')}"
          : null,
    );

    if (!mounted) return;
    setState(() => _isSaving = false);

    if (res['success'] == true) {
      final updatedUser = res['user'] ?? ApiService.currentUser ?? {};
      widget.onSaved?.call(updatedUser);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              const SizedBox(width: 10),
              Expanded(child: Text(res['message'] ?? 'Đã cập nhật thông tin thành công!')),
            ],
          ),
          backgroundColor: const Color(0xFF059669),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );

      if (widget.isModal) {
        Navigator.pop(context, updatedUser);
      } else {
        Navigator.pop(context, updatedUser);
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Lỗi khi lưu thông tin! Vui lòng thử lại.'),
          backgroundColor: const Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _showCommunePicker() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        String searchQuery = '';
        return StatefulBuilder(
          builder: (context, setPickerState) {
            final filtered = dongAnhCommunes
                .where((c) => c.toLowerCase().contains(searchQuery.toLowerCase()))
                .toList();

            return Container(
              height: MediaQuery.of(context).size.height * 0.7,
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                children: [
                  // Drag Handle
                  Container(
                    width: 40,
                    height: 4,
                    margin: const EdgeInsets.only(top: 12, bottom: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFCBD5E1),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),

                  // Header
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Chọn Xã / Thị Trấn (Đông Anh)',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                        ),
                        IconButton(
                          onPressed: () => Navigator.pop(ctx),
                          icon: const Icon(Icons.close_rounded, size: 22, color: Color(0xFF64748B)),
                        ),
                      ],
                    ),
                  ),

                  // Search Field
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
                    child: TextField(
                      onChanged: (val) => setPickerState(() => searchQuery = val),
                      decoration: InputDecoration(
                        hintText: 'Tìm kiếm xã / thị trấn...',
                        prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF0EA5E9), size: 20),
                        filled: true,
                        fillColor: const Color(0xFFF1F5F9),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: BorderSide.none,
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 6),

                  // List
                  Expanded(
                    child: ListView.separated(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      itemCount: filtered.length,
                      separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
                      itemBuilder: (context, idx) {
                        final commune = filtered[idx];
                        final isSelected = commune == _selectedCommune;
                        return ListTile(
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          tileColor: isSelected ? const Color(0xFFE0F2FE) : null,
                          leading: Icon(
                            Icons.location_on_rounded,
                            color: isSelected ? const Color(0xFF0284C7) : const Color(0xFF94A3B8),
                            size: 20,
                          ),
                          title: Text(
                            commune,
                            style: TextStyle(
                              fontSize: 14.5,
                              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                              color: isSelected ? const Color(0xFF0369A1) : const Color(0xFF1E293B),
                            ),
                          ),
                          trailing: isSelected
                              ? const Icon(Icons.check_circle_rounded, color: Color(0xFF0284C7), size: 20)
                              : null,
                          onTap: () {
                            setState(() => _selectedCommune = commune);
                            Navigator.pop(ctx);
                          },
                        );
                      },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _pickBirthday() async {
    final now = DateTime.now();
    final initialDate = _selectedBirthday ?? DateTime(2000, 1, 1);
    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: DateTime(1930),
      lastDate: now,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF0EA5E9),
              onPrimary: Colors.white,
              onSurface: Color(0xFF0F172A),
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() => _selectedBirthday = picked);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser ?? {};
    final avatarUrl = ApiService.getAvatarUrl(user, user['name']);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: Colors.black.withValues(alpha: 0.05),
        leading: IconButton(
          icon: Icon(
            widget.isModal ? Icons.expand_more_rounded : Icons.arrow_back_ios_new_rounded,
            color: const Color(0xFF0F172A),
            size: widget.isModal ? 30 : 20,
          ),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Hồ Sơ & Thông Tin Cá Nhân',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w800,
            color: Color(0xFF0F172A),
            letterSpacing: -0.3,
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 14),
            child: Center(
              child: SizedBox(
                height: 36,
                child: ElevatedButton(
                  onPressed: _isSaving ? null : _handleSave,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0EA5E9),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: _isSaving
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text(
                          'Lưu',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
                        ),
                ),
              ),
            ),
          ),
        ],
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
            children: [
              // Top Profile Card (Double Bezel Aesthetic)
              _buildTopIdentityCard(user, avatarUrl),

              const SizedBox(height: 18),

              // Section 1: Thông tin liên hệ & Giao nhận tại Đông Anh
              _buildSectionCard(
                title: 'ĐỊA CHỈ & LIÊN HỆ GIAO NHẬN',
                icon: Icons.local_shipping_outlined,
                iconColor: const Color(0xFF0284C7),
                iconBg: const Color(0xFFE0F2FE),
                subtitle: 'Sử dụng để giao nhận hàng OCOP & liên hệ chính chủ',
                children: [
                  _buildInputField(
                    controller: _phoneController,
                    label: 'Số điện thoại liên hệ *',
                    hint: 'Nhập số điện thoại (VD: 0912 345 678)',
                    icon: Icons.phone_android_rounded,
                    keyboardType: TextInputType.phone,
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) {
                        return 'Vui lòng nhập số điện thoại';
                      }
                      if (val.trim().length < 9) {
                        return 'Số điện thoại không hợp lệ';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 14),

                  // Bộ chọn Xã / Thị trấn Đông Anh
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Khu vực hành chính tại Đông Anh *',
                        style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                      ),
                      const SizedBox(height: 6),
                      InkWell(
                        onTap: _showCommunePicker,
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(
                              color: _selectedCommune != null ? const Color(0xFF0EA5E9) : const Color(0xFFCBD5E1),
                              width: _selectedCommune != null ? 1.5 : 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                Icons.location_city_rounded,
                                color: _selectedCommune != null ? const Color(0xFF0284C7) : const Color(0xFF94A3B8),
                                size: 20,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  _selectedCommune ?? 'Chọn xã hoặc thị trấn tại Huyện Đông Anh',
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: _selectedCommune != null ? FontWeight.w600 : FontWeight.w400,
                                    color: _selectedCommune != null ? const Color(0xFF0F172A) : const Color(0xFF94A3B8),
                                  ),
                                ),
                              ),
                              const Icon(Icons.arrow_drop_down_rounded, color: Color(0xFF64748B), size: 26),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Địa chỉ cụ thể
                  _buildInputField(
                    controller: _addressController,
                    label: 'Địa chỉ nhận hàng chi tiết *',
                    hint: 'Số nhà, xóm, ngõ, thôn (VD: Thôn Mạch Tràng, gần Chợ Cổ Loa)',
                    icon: Icons.place_outlined,
                    maxLines: 2,
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) {
                        return 'Vui lòng nhập địa chỉ nhận hàng chi tiết';
                      }
                      return null;
                    },
                  ),
                ],
              ),

              const SizedBox(height: 18),

              // Section 2: Thông tin định danh cá nhân
              _buildSectionCard(
                title: 'THÔNG TIN ĐỊNH DANH',
                icon: Icons.badge_outlined,
                iconColor: const Color(0xFF7C3AED),
                iconBg: const Color(0xFFEDE9FE),
                children: [
                  _buildInputField(
                    controller: _nameController,
                    label: 'Họ và tên hiển thị *',
                    hint: 'Nhập họ và tên đầy đủ',
                    icon: Icons.person_outline_rounded,
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) {
                        return 'Họ và tên không được để trống';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 14),

                  // Email (Readonly)
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Địa chỉ Email',
                        style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.email_outlined, color: Color(0xFF94A3B8), size: 20),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                (user['email'] ?? 'Chưa liên kết email').toString(),
                                style: const TextStyle(fontSize: 14, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xFFE2E8F0),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text('Cố định', style: TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),

              const SizedBox(height: 18),

              // Section 3: Cá nhân hóa & Thông tin thêm
              _buildSectionCard(
                title: 'CÁ NHÂN HÓA & TIỂU SỬ',
                icon: Icons.auto_awesome_rounded,
                iconColor: const Color(0xFFD97706),
                iconBg: const Color(0xFFFEF3C7),
                children: [
                  // Giới tính
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Giới tính',
                        style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          _buildGenderChip('male', 'Nam 👨', const Color(0xFF0EA5E9)),
                          const SizedBox(width: 10),
                          _buildGenderChip('female', 'Nữ 👩', const Color(0xFFEC4899)),
                          const SizedBox(width: 10),
                          _buildGenderChip('other', 'Khác ✨', const Color(0xFF8B5CF6)),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Ngày sinh
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Ngày sinh',
                        style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                      ),
                      const SizedBox(height: 6),
                      InkWell(
                        onTap: _pickBirthday,
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFCBD5E1)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.cake_outlined, color: Color(0xFFD97706), size: 20),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  _selectedBirthday != null
                                      ? "${_selectedBirthday!.day.toString().padLeft(2, '0')}/${_selectedBirthday!.month.toString().padLeft(2, '0')}/${_selectedBirthday!.year}"
                                      : 'Chọn ngày sinh của bạn',
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: _selectedBirthday != null ? FontWeight.w600 : FontWeight.w400,
                                    color: _selectedBirthday != null ? const Color(0xFF0F172A) : const Color(0xFF94A3B8),
                                  ),
                                ),
                              ),
                              const Icon(Icons.calendar_month_rounded, color: Color(0xFF64748B), size: 20),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Tiểu sử / Giới thiệu
                  _buildInputField(
                    controller: _bioController,
                    label: 'Tiểu sử / Lời giới thiệu',
                    hint: 'Chia sẻ một chút về bản thân, sở thích ẩm thực hoặc quê quán Đông Anh...',
                    icon: Icons.edit_note_rounded,
                    maxLines: 3,
                  ),
                ],
              ),

              const SizedBox(height: 28),

              // Bottom Save Action Button
              SizedBox(
                height: 52,
                child: ElevatedButton(
                  onPressed: _isSaving ? null : _handleSave,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0EA5E9),
                    foregroundColor: Colors.white,
                    elevation: 2,
                    shadowColor: const Color(0xFF0EA5E9).withValues(alpha: 0.35),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  ),
                  child: _isSaving
                      ? const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)),
                            SizedBox(width: 12),
                            Text('Đang lưu thông tin...', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                          ],
                        )
                      : const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.save_rounded, size: 20),
                            SizedBox(width: 8),
                            Text('Cập Nhật Thông Tin Cá Nhân', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                          ],
                        ),
                ),
              ),

              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTopIdentityCard(Map<String, dynamic> user, String avatarUrl) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFF0EA5E9), width: 2),
            ),
            child: CircleAvatar(
              radius: 30,
              backgroundColor: const Color(0xFFE0F2FE),
              backgroundImage: NetworkImage(avatarUrl),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        (user['name'] ?? 'Thành viên Đông Anh').toString(),
                        style: const TextStyle(
                          fontSize: 16.5,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 6),
                    const Icon(Icons.verified_rounded, color: Color(0xFF0EA5E9), size: 18),
                  ],
                ),
                const SizedBox(height: 3),
                Text(
                  _selectedCommune != null
                      ? '📍 $_selectedCommune, Đông Anh'
                      : 'Huyện Đông Anh, Thành phố Hà Nội',
                  style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'ID: #${user['id'] ?? 'user'} • Quyền: ${(user['role'] ?? 'user').toString().toUpperCase()}',
                    style: const TextStyle(fontSize: 11, color: Color(0xFF475569), fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionCard({
    required String title,
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    String? subtitle,
    required List<Widget> children,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  color: iconBg,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: iconColor, size: 18),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF0F172A),
                        letterSpacing: 0.4,
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _buildInputField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
    int maxLines = 1,
    String? Function(String?)? validator,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
        ),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          maxLines: maxLines,
          validator: validator,
          style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
            prefixIcon: maxLines == 1 ? Icon(icon, color: const Color(0xFF64748B), size: 20) : null,
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFF0EA5E9), width: 1.8),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFFEF4444)),
            ),
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.8),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildGenderChip(String value, String label, Color activeColor) {
    final isSelected = _selectedGender == value;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _selectedGender = value),
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? activeColor.withValues(alpha: 0.12) : const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? activeColor : const Color(0xFFE2E8F0),
              width: isSelected ? 1.5 : 1,
            ),
          ),
          alignment: Alignment.center,
          child: Text(
            label,
            style: TextStyle(
              fontSize: 13.5,
              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
              color: isSelected ? activeColor : const Color(0xFF64748B),
            ),
          ),
        ),
      ),
    );
  }
}
