import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/road_trip_loader.dart';
import 'map_screen.dart';

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

  // Structured AI Output State
  String? _generatedTourTitle;
  String? _generatedStory;
  final List<Map<String, dynamic>> _generatedStops = [];
  String _aiStatusMessage = '';

  final List<Map<String, String>> _moodOptions = [
    {'key': 'chill', 'label': 'Chill & Thư Giãn', 'emoji': '🍹'},
    {'key': 'sáng sớm', 'label': 'Sáng Sớm', 'emoji': '🌅'},
    {'key': 'ăn đêm', 'label': 'Ăn Đêm Phố Cổ', 'emoji': '🌙'},
    {'key': 'văn hóa', 'label': 'Văn Hóa Cổ Loa', 'emoji': '🏛️'},
  ];

  final List<int> _budgetPresets = [100000, 300000, 500000, 1000000];

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
      _generatedTourTitle = null;
      _generatedStory = null;
      _generatedStops.clear();
      _aiStatusMessage = 'Đang khởi tạo Gemini AI...';
    });

    ApiService.streamAiTour(_budget, _mood).listen(
      (dataStr) {
        if (mounted) {
          try {
            final json = jsonDecode(dataStr);
            setState(() {
              final type = json['type'];
              if (type == 'status' || json['message'] != null) {
                _aiStatusMessage = json['message'] ?? '';
                _aiStreamLogs.add('${json['message']}');
              }
              if (type == 'meta' && json['tour_name'] != null) {
                _generatedTourTitle = json['tour_name'];
              }
              if (type == 'stop' || json['recommendation'] != null) {
                _generatedStops.add({
                  'index': json['index'] ?? (_generatedStops.length + 1),
                  'name': json['name'] ?? 'Địa điểm phượt',
                  'recommendation': json['recommendation'] ?? '',
                });
                _aiStreamLogs.add('📍 Điểm ${json['index'] ?? ''}: ${json['name'] ?? ''}\n💡 ${json['recommendation']}');
              }
              if (type == 'story' || json['story'] != null) {
                _generatedStory = json['story'];
                _aiStreamLogs.add('📖 Câu chuyện: ${json['story']}');
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
            _aiStatusMessage = 'Lỗi kết nối AI';
            _aiStreamLogs.add('⚠️ Có lỗi xảy ra: $err');
          });
        }
      },
      onDone: () {
        if (mounted) {
          setState(() {
            _isGeneratingAi = false;
            _aiStatusMessage = 'Đã tạo xong lộ trình AI!';
          });
        }
      },
    );
  }

  String _formatCurrency(int amount) {
    if (amount >= 1000000) {
      return '${(amount / 1000000).toStringAsFixed(amount % 1000000 == 0 ? 0 : 1)} Triệu VNĐ';
    }
    return '${(amount / 1000).toStringAsFixed(0)}.000 VNĐ';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Color(0xFF1E293B), size: 20),
          onPressed: () => Navigator.of(context).maybePop(),
        ),
        titleSpacing: 0,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                gradient: const LinearGradient(colors: [Color(0xFF0284C7), Color(0xFF06B6D4)]),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.directions_bike_rounded, color: Colors.white, size: 20),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Hành Trình Ẩm Thực',
                    style: TextStyle(color: Color(0xFF0F172A), fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    'Food Tour AI & Gợi Ý Phượt Cổ Loa',
                    style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.w500),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFE0F2FE),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: const Color(0xFFBAE6FD)),
            ),
            child: const Row(
              children: [
                Icon(Icons.explore, color: Color(0xFF0284C7), size: 14),
                SizedBox(width: 4),
                Text('Lộ Trình v2.5', style: TextStyle(color: Color(0xFF0369A1), fontSize: 11, fontWeight: FontWeight.w800)),
              ],
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // AI Gemini Tour Generator Banner Card
            Container(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.4), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withValues(alpha: 0.3),
                    blurRadius: 20,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(24),
                child: Stack(
                  children: [
                    // Glow background decoration
                    Positioned(
                      top: -30,
                      right: -30,
                      child: Container(
                        width: 140,
                        height: 140,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: const Color(0xFF0284C7).withValues(alpha: 0.25),
                        ),
                      ),
                    ),

                    Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Header Badge & Title
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF38BDF8).withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.3)),
                                ),
                                child: const Icon(Icons.alt_route_rounded, color: Color(0xFF38BDF8), size: 20),
                              ),
                              const SizedBox(width: 10),
                              const Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'TRỢ LÝ AI TẠO HÀNH TRÌNH TỰ ĐỘNG',
                                    style: TextStyle(
                                      color: Color(0xFF38BDF8),
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: 1.0,
                                    ),
                                  ),
                                  SizedBox(height: 2),
                                  Text(
                                    'Thiết kế lộ trình phượt ẩm thực theo ý bạn',
                                    style: TextStyle(color: Colors.white70, fontSize: 12),
                                  ),
                                ],
                              ),
                            ],
                          ),

                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 16),
                            child: Divider(color: Colors.white12, height: 1),
                          ),

                          // Budget Selector Section
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Row(
                                children: [
                                  Icon(Icons.account_balance_wallet_outlined, color: Colors.white70, size: 16),
                                  SizedBox(width: 6),
                                  Text('Ngân sách dự kiến:', style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w500)),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF0284C7).withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.5)),
                                ),
                                child: Text(
                                  _formatCurrency(_budget),
                                  style: const TextStyle(color: Color(0xFF38BDF8), fontSize: 14, fontWeight: FontWeight.bold),
                                ),
                              ),
                            ],
                          ),

                          const SizedBox(height: 8),

                          // Slider Theme
                          SliderTheme(
                            data: SliderTheme.of(context).copyWith(
                              activeTrackColor: const Color(0xFF38BDF8),
                              inactiveTrackColor: Colors.white24,
                              thumbColor: const Color(0xFF38BDF8),
                              overlayColor: const Color(0xFF38BDF8).withValues(alpha: 0.2),
                              valueIndicatorColor: const Color(0xFF0284C7),
                              trackHeight: 6,
                              thumbShape: const RoundSliderThumbShape(enabledThumbRadius: 10),
                            ),
                            child: Slider(
                              value: _budget.toDouble(),
                              min: 100000,
                              max: 1000000,
                              divisions: 18,
                              onChanged: (val) => setState(() => _budget = val.round()),
                            ),
                          ),

                          // Quick Budget Presets
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: _budgetPresets.map((b) {
                              final isSelected = _budget == b;
                              return InkWell(
                                onTap: () => setState(() => _budget = b),
                                borderRadius: BorderRadius.circular(8),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                  decoration: BoxDecoration(
                                    color: isSelected ? const Color(0xFF0284C7) : Colors.white.withValues(alpha: 0.08),
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: isSelected ? const Color(0xFF38BDF8) : Colors.transparent,
                                    ),
                                  ),
                                  child: Text(
                                    b >= 1000000 ? '1 Triệu' : '${(b / 1000).toStringAsFixed(0)}k',
                                    style: TextStyle(
                                      color: isSelected ? Colors.white : Colors.white60,
                                      fontSize: 11,
                                      fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                    ),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),

                          const SizedBox(height: 20),

                          // Mood Selection Section
                          const Row(
                            children: [
                              Icon(Icons.psychology_outlined, color: Colors.white70, size: 16),
                              SizedBox(width: 6),
                              Text('Tâm trạng & Phong cách:', style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w500)),
                            ],
                          ),
                          const SizedBox(height: 10),

                          // Custom Grid Mood Cards
                          GridView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              childAspectRatio: 3.2,
                              crossAxisSpacing: 10,
                              mainAxisSpacing: 10,
                            ),
                            itemCount: _moodOptions.length,
                            itemBuilder: (context, index) {
                              final item = _moodOptions[index];
                              final isSelected = _mood == item['key'];
                              return InkWell(
                                onTap: () => setState(() => _mood = item['key']!),
                                borderRadius: BorderRadius.circular(14),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 200),
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                  decoration: BoxDecoration(
                                    gradient: isSelected
                                        ? const LinearGradient(colors: [Color(0xFF0284C7), Color(0xFF06B6D4)])
                                        : null,
                                    color: isSelected ? null : Colors.white.withValues(alpha: 0.08),
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(
                                      color: isSelected ? const Color(0xFF38BDF8) : Colors.white12,
                                      width: isSelected ? 1.5 : 1.0,
                                    ),
                                    boxShadow: isSelected
                                        ? [
                                            BoxShadow(
                                              color: const Color(0xFF0284C7).withValues(alpha: 0.4),
                                              blurRadius: 8,
                                              offset: const Offset(0, 4),
                                            )
                                          ]
                                        : null,
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(item['emoji']!, style: const TextStyle(fontSize: 16)),
                                      const SizedBox(width: 8),
                                      Flexible(
                                        child: Text(
                                          item['label']!,
                                          style: TextStyle(
                                            color: isSelected ? Colors.white : Colors.white70,
                                            fontSize: 12,
                                            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                                          ),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),

                          const SizedBox(height: 20),

                          // Generate Button
                          SizedBox(
                            width: double.infinity,
                            height: 50,
                            child: ElevatedButton(
                              onPressed: _isGeneratingAi ? null : _startAiTourGeneration,
                              style: ElevatedButton.styleFrom(
                                padding: EdgeInsets.zero,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                elevation: 4,
                                shadowColor: const Color(0xFF0284C7).withValues(alpha: 0.5),
                              ),
                              child: Ink(
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFF0284C7), Color(0xFF06B6D4)],
                                  ),
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                child: Container(
                                  alignment: Alignment.center,
                                  child: _isGeneratingAi
                                      ? const Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            SizedBox(
                                              width: 20,
                                              height: 20,
                                              child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                                            ),
                                            SizedBox(width: 12),
                                            Text(
                                              'Đang Lập Lộ Trình AI...',
                                              style: TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold),
                                            ),
                                          ],
                                        )
                                      : const Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(Icons.bolt_rounded, color: Colors.white, size: 22),
                                            SizedBox(width: 8),
                                            Text(
                                              'Tạo Lộ Trình AI Nối Đuôi Stream',
                                              style: TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold),
                                            ),
                                          ],
                                        ),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // =========================================================================
            // DISPLAY RESULT: STRUCTURED AI GENERATED TOUR ITINERARY TIMELINE VIEW
            // =========================================================================
            if (_isGeneratingAi || _generatedStops.isNotEmpty || _generatedTourTitle != null) ...[
              const SizedBox(height: 24),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: const Color(0xFFBAE6FD), width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0284C7).withValues(alpha: 0.08),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Badge & Title
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFE0F2FE),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.map_outlined, color: Color(0xFF0284C7), size: 14),
                              SizedBox(width: 4),
                              Text(
                                'LỘ TRÌNH DÀNH RIÊNG CHO BẠN',
                                style: TextStyle(color: Color(0xFF0369A1), fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 0.8),
                              ),
                            ],
                          ),
                        ),
                        if (_isGeneratingAi)
                          const SizedBox(
                            width: 14,
                            height: 14,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0284C7)),
                          ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    Text(
                      _generatedTourTitle ?? 'Hành trình Ẩm thực & Di sản Đông Anh',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A), height: 1.3),
                    ),

                    const SizedBox(height: 8),

                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(8)),
                          child: Text('💰 ${_formatCurrency(_budget)}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF475569))),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(8)),
                          child: Text('🎭 ${_mood.toUpperCase()}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF475569))),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(color: const Color(0xFFE0F2FE), borderRadius: BorderRadius.circular(8)),
                          child: Text('📍 ${_generatedStops.length} điểm dừng', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF0369A1))),
                        ),
                      ],
                    ),

                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      child: Divider(color: Color(0xFFE2E8F0), height: 1),
                    ),

                    // Visual Vertical Timeline of Stops
                    if (_generatedStops.isEmpty && _isGeneratingAi)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 20),
                        child: Row(
                          children: [
                            const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0284C7))),
                            const SizedBox(width: 12),
                            Text(_aiStatusMessage.isNotEmpty ? _aiStatusMessage : 'AI đang phân tích các địa điểm ngon nhất...', style: const TextStyle(fontSize: 13, color: Color(0xFF64748B))),
                          ],
                        ),
                      )
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _generatedStops.length,
                        itemBuilder: (context, index) {
                          final stop = _generatedStops[index];
                          final isLast = index == _generatedStops.length - 1;

                          return IntrinsicHeight(
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Left Timeline Column (Badge & Line)
                                Column(
                                  children: [
                                    Container(
                                      width: 28,
                                      height: 28,
                                      decoration: BoxDecoration(
                                        gradient: const LinearGradient(colors: [Color(0xFF0284C7), Color(0xFF06B6D4)]),
                                        shape: BoxShape.circle,
                                        boxShadow: [
                                          BoxShadow(color: const Color(0xFF0284C7).withValues(alpha: 0.3), blurRadius: 6, offset: const Offset(0, 2)),
                                        ],
                                      ),
                                      alignment: Alignment.center,
                                      child: Text(
                                        '${stop['index']}',
                                        style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                    if (!isLast)
                                      Expanded(
                                        child: Container(
                                          width: 2,
                                          margin: const EdgeInsets.symmetric(vertical: 4),
                                          color: const Color(0xFFBAE6FD),
                                        ),
                                      ),
                                  ],
                                ),

                                const SizedBox(width: 12),

                                // Right Card Column
                                Expanded(
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 16),
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          stop['name'] ?? 'Địa điểm',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          stop['recommendation'] ?? '',
                                          style: const TextStyle(fontSize: 12, color: Color(0xFF475569), height: 1.4),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),

                    // Story / Context Section
                    if (_generatedStory != null && _generatedStory!.isNotEmpty) ...[
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFFFDE68A)),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('📖 ', style: TextStyle(fontSize: 16)),
                            Expanded(
                              child: Text(
                                _generatedStory!,
                                style: const TextStyle(fontSize: 12, color: Color(0xFF78350F), height: 1.4, fontWeight: FontWeight.w500),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Footer Action Buttons
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: () {
                              Navigator.push(context, MaterialPageRoute(builder: (_) => const MapScreen()));
                            },
                            icon: const Icon(Icons.map_outlined, size: 16),
                            label: const Text('Mở Bản Đồ Map'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF0284C7),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        OutlinedButton.icon(
                          onPressed: () {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('🎉 Đã lưu lộ trình AI vào danh sách cá nhân thành công!'),
                                backgroundColor: Color(0xFF059669),
                              ),
                            );
                          },
                          icon: const Icon(Icons.bookmark_border_rounded, size: 16, color: Color(0xFF0284C7)),
                          label: const Text('Lưu Tour', style: TextStyle(color: Color(0xFF0284C7))),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            side: const BorderSide(color: Color(0xFF0284C7)),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 28),

            // Pre-made Food Tours Section Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.map_rounded, color: Color(0xFF0284C7), size: 18),
                    SizedBox(width: 8),
                    Text(
                      'GỢI Ý HÀNH TRÌNH CÓ SẴN',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF334155), letterSpacing: 0.8),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE2E8F0),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${_foodTours.length} Tour',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 12),

            _isLoading
                ? const Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: RoadTripLoader(message: '🛵 Đang lấy gợi ý Food Tour phượt Cổ Loa...'),
                  )
                : _foodTours.isEmpty
                    ? Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: const Column(
                          children: [
                            Icon(Icons.no_meals_rounded, color: Color(0xFF94A3B8), size: 40),
                            SizedBox(height: 10),
                            Text('Chưa có gợi ý tour có sẵn nào', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                          ],
                        ),
                      )
                    : Column(
                        children: _foodTours.map((tour) {
                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF64748B).withValues(alpha: 0.06),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: ListTile(
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                              leading: Container(
                                width: 48,
                                height: 48,
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(colors: [Color(0xFFE0F2FE), Color(0xFFBAE6FD)]),
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                child: const Icon(Icons.explore_rounded, color: Color(0xFF0284C7), size: 24),
                              ),
                              title: Text(
                                tour['title'] ?? 'Tour Ẩm Thực Đông Anh',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                              ),
                              subtitle: Padding(
                                padding: const EdgeInsets.only(top: 4),
                                child: Text(
                                  tour['description'] ?? 'Khám phá các quán ăn ngon chuẩn vị Cổ Loa',
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), height: 1.3),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              trailing: Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Icon(Icons.chevron_right_rounded, color: Color(0xFF0284C7)),
                              ),
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
