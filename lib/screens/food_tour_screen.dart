import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/squircle_helper.dart';
import '../widgets/road_trip_loader.dart';

class FoodTourScreen extends StatefulWidget {
  const FoodTourScreen({super.key});

  @override
  State<FoodTourScreen> createState() => _FoodTourScreenState();
}

class _FoodTourScreenState extends State<FoodTourScreen> {
  List<dynamic> _foodTours = [];
  bool _isLoading = true;

  // AI Tour Generator State
  int _budget = 300000;
  String _mood = 'chill';
  bool _isGeneratingAi = false;
  final List<String> _aiStreamLogs = [];

  @override
  void initState() {
    super.initState();
    _fetchFoodTours();
  }

  Future<void> _fetchFoodTours() async {
    setState(() => _isLoading = true);
    try {
      final tours = await ApiService.getFoodTours();
      if (mounted) {
        setState(() {
          _foodTours = tours;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _startAiTourGeneration() {
    setState(() {
      _isGeneratingAi = true;
      _aiStreamLogs.clear();
    });

    ApiService.streamAiTour(_budget, _mood).listen(
      (dataStr) {
        if (mounted) {
          try {
            final json = jsonDecode(dataStr);
            setState(() {
              if (json['message'] != null) {
                _aiStreamLogs.add('✨ ${json['message']}');
              } else if (json['recommendation'] != null) {
                _aiStreamLogs.add('📍 Điểm ${json['index']}: ${json['name']}\n💡 ${json['recommendation']}');
              } else if (json['story'] != null) {
                _aiStreamLogs.add('📖 Story: ${json['story']}');
              }
            });
          } catch (_) {
            setState(() {
              _aiStreamLogs.add(dataStr);
            });
          }
        }
      },
      onError: (err) {
        if (mounted) {
          setState(() {
            _isGeneratingAi = false;
            _aiStreamLogs.add('⚠️ Có lỗi xảy ra: $err');
          });
        }
      },
      onDone: () {
        if (mounted) {
          setState(() {
            _isGeneratingAi = false;
          });
        }
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: const Row(
          children: [
            Icon(Icons.directions_bike_rounded, color: Color(0xFF0284C7)),
            SizedBox(width: 8),
            Text('Hành Trình Ẩm Thực & Food Tour AI', style: TextStyle(color: Color(0xFF0F172A), fontSize: 17, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // AI Gemini Tour Generator Banner Card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: SquircleHelper.decoration(
                radius: 24,
                color: const Color(0xFF0F172A),
                borderSide: const BorderSide(color: Color(0xFF38BDF8), width: 1.5),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.auto_awesome_rounded, color: Color(0xFF38BDF8), size: 24),
                      SizedBox(width: 8),
                      Text('TRỢ LÝ AI TẠO HÀNH TRÌNH TỰ ĐỘNG', style: TextStyle(color: Color(0xFF38BDF8), fontSize: 12, fontWeight: FontWeight.bold, letterSpacing: 0.8)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  const Text('Bạn có bao nhiêu ngân sách & tâm trạng hôm nay?', style: TextStyle(color: Colors.white, fontSize: 14)),
                  const SizedBox(height: 12),

                  // Budget Slider
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Ngân sách:', style: TextStyle(color: Colors.white70, fontSize: 12)),
                      Text('${(_budget / 1000).toStringAsFixed(0)}k VNĐ', style: const TextStyle(color: Color(0xFF38BDF8), fontSize: 15, fontWeight: FontWeight.bold)),
                    ],
                  ),
                  Slider(
                    value: _budget.toDouble(),
                    min: 100000,
                    max: 1000000,
                    divisions: 18,
                    activeColor: const Color(0xFF38BDF8),
                    onChanged: (val) => setState(() => _budget = val.round()),
                  ),

                  // Mood Selection Chips
                  const Text('Tâm trạng:', style: TextStyle(color: Colors.white70, fontSize: 12)),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: ['chill', 'sáng sớm', 'ăn đêm', 'văn hóa'].map((m) {
                      final isSelected = _mood == m;
                      return ChoiceChip(
                        label: Text(m.toUpperCase()),
                        selected: isSelected,
                        selectedColor: const Color(0xFF0284C7),
                        labelStyle: TextStyle(color: isSelected ? Colors.white : Colors.white70, fontSize: 11, fontWeight: FontWeight.bold),
                        onSelected: (selected) {
                          if (selected) setState(() => _mood = m);
                        },
                      );
                    }).toList(),
                  ),

                  const SizedBox(height: 14),

                  // Generate Button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _isGeneratingAi ? null : _startAiTourGeneration,
                      icon: _isGeneratingAi
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : const Icon(Icons.bolt_rounded, size: 18),
                      label: Text(_isGeneratingAi ? 'Đang Stream AI...' : 'Tạo Lộ Trình AI Nối Đuôi Stream'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0284C7),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),

                  // Stream Output Logs
                  if (_aiStreamLogs.isNotEmpty) ...[
                    const Divider(color: Colors.white24, height: 24),
                    ..._aiStreamLogs.map((log) {
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(color: Colors.white10, borderRadius: BorderRadius.circular(12)),
                        child: Text(log, style: const TextStyle(color: Colors.white, fontSize: 12)),
                      );
                    }),
                  ]
                ],
              ),
            ),

            const SizedBox(height: 24),

            // Pre-made Food Tours Section
            const Text('GỢI Ý HÀNH TRÌNH CÓ SẴN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8)),
            const SizedBox(height: 12),

            _isLoading
                ? const Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: RoadTripLoader(message: '🛵 Đang lấy gợi ý Food Tour phượt Cổ Loa...'),
                  )
                : Column(
                    children: _foodTours.map((tour) {
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: SquircleHelper.decoration(radius: 18, color: Colors.white, borderSide: BorderSide(color: Colors.grey.shade200)),
                        child: ListTile(
                          contentPadding: const EdgeInsets.all(12),
                          leading: const CircleAvatar(
                            backgroundColor: Color(0xFFE0F2FE),
                            radius: 24,
                            child: Icon(Icons.explore_rounded, color: Color(0xFF0284C7)),
                          ),
                          title: Text(tour['title'] ?? 'Tour Ẩm Thực Đông Anh', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                          subtitle: Text(tour['description'] ?? 'Khám phá các quán ăn ngon chuẩn vị', style: const TextStyle(fontSize: 12, color: Colors.grey), maxLines: 2),
                          trailing: const Icon(Icons.chevron_right_rounded, color: Colors.grey),
                        ),
                      );
                    }).toList(),
                  ),
          ],
        ),
      ),
    );
  }
}
