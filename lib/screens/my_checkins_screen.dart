import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';

class MyCheckinsScreen extends StatefulWidget {
  const MyCheckinsScreen({super.key});

  @override
  State<MyCheckinsScreen> createState() => _MyCheckinsScreenState();
}

class _MyCheckinsScreenState extends State<MyCheckinsScreen> {
  List<dynamic> _myCheckins = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadMyCheckins();
  }

  Future<void> _loadMyCheckins() async {
    setState(() {
      _isLoading = true;
    });
    final checkins = await ApiService.getMyCheckins();
    if (mounted) {
      setState(() {
        _myCheckins = checkins;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF0EA5E9);

    return Scaffold(
      appBar: AppBar(
        title: const Text.rich(
          TextSpan(
            children: [
              TextSpan(
                text: 'Góc Trải Nghiệm ',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 18),
              ),
              TextSpan(
                text: 'Thực Tế',
                style: TextStyle(color: Color(0xFFFFB800), fontWeight: FontWeight.w900, fontSize: 18),
              ),
            ],
          ),
        ),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF38BDF8), Color(0xFF00A8EE), Color(0xFF0284C7)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: _isLoading
          ? const CustomPulseLoader(
              message: 'Đang kết nối nhật ký check-in...',
              icon: Icons.add_location_alt_rounded,
              primaryColor: Color(0xFF0EA5E9),
            )
          : _myCheckins.isEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.history_toggle_off, size: 64, color: Colors.grey[300]),
                        const SizedBox(height: 16),
                        const Text(
                          'Bạn chưa đăng bài check-in nào.',
                          style: TextStyle(fontSize: 16, color: Colors.grey, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Hãy vào tab Bản đồ, chọn một quán ăn và bấm "Check-in tại đây" để lưu lại hành trình của bạn!',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 13, color: Colors.grey[500]),
                        ),
                      ],
                    ),
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _myCheckins.length,
                  itemBuilder: (context, index) {
                    final item = _myCheckins[index];
                    return _buildCheckinCard(item, primaryColor);
                  },
                ),
    );
  }

  Widget _buildCheckinCard(dynamic item, Color primaryColor) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.grey[200]!.withOpacity(0.4),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header: Eatery Name & Rating
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item['eatery']?['name'] ?? 'Địa điểm',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${item['eatery']?['category'] ?? ''} • ${item['eatery']?['commune'] ?? ''}',
                        style: TextStyle(color: Colors.grey[500], fontSize: 11),
                      ),
                    ],
                  ),
                ),
                // Rating stars
                Row(
                  children: List.generate(5, (index) {
                    return Icon(
                      index < (item['rating'] ?? 5) ? Icons.star : Icons.star_border,
                      color: Colors.amber,
                      size: 16,
                    );
                  }),
                ),
              ],
            ),
          ),

          // User's Comment
          if (item['comment'] != null && item['comment'].toString().trim().isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(left: 16, right: 16, bottom: 12),
              child: Text(
                item['comment'],
                style: const TextStyle(fontSize: 13, height: 1.4, color: Colors.black87),
              ),
            ),

          // Attached Image
          if (item['image_path'] != null)
            Padding(
              padding: const EdgeInsets.only(left: 16, right: 16, bottom: 12),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.network(
                  item['image_path'].toString().startsWith('http')
                      ? item['image_path']
                      : 'https://donganhdiscovery.xadonganh.com/' + (item['image_path'].toString().startsWith('/') ? item['image_path'].toString().substring(1) : item['image_path'].toString()),
                  height: 160,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  filterQuality: FilterQuality.high,
                  errorBuilder: (_, __, ___) => Container(
                    height: 100,
                    color: Colors.grey[100],
                    child: const Icon(Icons.image_not_supported, color: Colors.grey),
                  ),
                ),
              ),
            ),

          // Footer: Timestamp
          Padding(
            padding: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
            child: Text(
              item['created_at_format'] ?? '',
              style: TextStyle(fontSize: 10, color: Colors.grey[400]),
            ),
          ),
        ],
      ),
    );
  }
}
