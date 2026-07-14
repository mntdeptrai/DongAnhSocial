import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';

class MapScreen extends StatefulWidget {
  const MapScreen({super.key});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _LoginCheckinModal extends StatefulWidget {
  final int eateryId;
  final String eateryName;
  final Function(int rating, String comment, String? guestName, String? imagePath) onSubmit;

  const _LoginCheckinModal({
    required this.eateryId,
    required this.eateryName,
    required this.onSubmit,
  });

  @override
  State<_LoginCheckinModal> createState() => _LoginCheckinModalState();
}

class _LoginCheckinModalState extends State<_LoginCheckinModal> {
  int _rating = 5;
  final _commentController = TextEditingController();
  final _guestNameController = TextEditingController();
  bool _isSending = false;
  String? _imagePath;

  @override
  void dispose() {
    _commentController.dispose();
    _guestNameController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final pickedFile = await picker.pickImage(
        source: source,
        maxWidth: 1000,
        maxHeight: 1000,
        imageQuality: 80,
      );
      if (pickedFile != null) {
        setState(() {
          _imagePath = pickedFile.path;
        });
      }
    } catch (e) {
      debugPrint('Lỗi chọn ảnh: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final isGuest = !ApiService.isAuthenticated;

    return Padding(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
        left: 20,
        right: 20,
        top: 20,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Check-in tại ${widget.eateryName}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          if (isGuest) ...[
            TextField(
              controller: _guestNameController,
              decoration: const InputDecoration(
                labelText: 'Tên của bạn (Khách vãng lai)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16),
          ],
          const Text('Đánh giá của bạn:'),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(5, (index) {
              return IconButton(
                icon: Icon(
                  index < _rating ? Icons.star : Icons.star_border,
                  color: Colors.amber,
                  size: 32,
                ),
                onPressed: () {
                  setState(() {
                    _rating = index + 1;
                  });
                },
              );
            }),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _commentController,
            maxLines: 3,
            decoration: const InputDecoration(
              labelText: 'Nhập cảm nhận của bạn...',
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 16),
          
          // Image picker layout
          if (_imagePath == null)
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickImage(ImageSource.camera),
                    icon: const Icon(Icons.camera_alt),
                    label: const Text('Chụp ảnh'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      side: const BorderSide(color: Color(0xFF0EA5E9)),
                      foregroundColor: const Color(0xFF0EA5E9),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickImage(ImageSource.gallery),
                    icon: const Icon(Icons.photo),
                    label: const Text('Thư viện'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      side: const BorderSide(color: Color(0xFF0EA5E9)),
                      foregroundColor: const Color(0xFF0EA5E9),
                    ),
                  ),
                ),
              ],
            )
          else
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.file(
                    File(_imagePath!),
                    height: 140,
                    width: double.infinity,
                    fit: BoxFit.cover,
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: GestureDetector(
                    onTap: () {
                      setState(() {
                        _imagePath = null;
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: Colors.black54,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.close, color: Colors.white, size: 20),
                    ),
                  ),
                ),
              ],
            ),

          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _isSending
                ? null
                : () async {
                    setState(() {
                      _isSending = true;
                    });
                    await widget.onSubmit(
                      _rating,
                      _commentController.text,
                      isGuest ? _guestNameController.text : null,
                      _imagePath,
                    );
                    if (mounted) {
                      setState(() {
                        _isSending = false;
                      });
                      Navigator.pop(context);
                    }
                  },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14),
            ),
            child: _isSending
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                  )
                : const Text('Gửi Check-in', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}

class _MapScreenState extends State<MapScreen> {
  final MapController _mapController = MapController();
  LatLng _center = const LatLng(21.1373, 105.8436); // Đông Anh center
  List<dynamic> _categories = [];
  String? _selectedCategory;
  List<dynamic> _eateries = [];
  bool _isLoading = false;
  LatLng? _userLocation;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    final categories = await ApiService.getCategories();
    if (categories.isNotEmpty && mounted) {
      setState(() {
        _categories = categories;
        _selectedCategory = categories[0]['slug'];
      });
      _loadEateries();
    }
  }

  Future<void> _loadEateries() async {
    if (_selectedCategory == null) return;
    setState(() {
      _isLoading = true;
    });
    final eateries = await ApiService.getEateries(_selectedCategory!);
    if (mounted) {
      setState(() {
        _eateries = eateries;
        _isLoading = false;
      });
    }
  }

  Future<void> _getLocation() async {
    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;
    }

    final position = await Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.high,
    );

    if (mounted) {
      setState(() {
        _userLocation = LatLng(position.latitude, position.longitude);
      });
      _mapController.move(_userLocation!, 15);
    }
  }

  void _showEateryDetails(dynamic eatery) {
    // Parse coordinates correctly
    double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
    double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          eatery['name'] ?? 'Địa điểm',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          eatery['address'] ?? 'Đông Anh, Hà Nội',
                           style: TextStyle(color: Colors.grey[600], fontSize: 13),
                        ),
                      ],
                    ),
                  ),
                  if (lat != null && lng != null)
                    IconButton(
                      icon: const Icon(Icons.directions, color: Color(0xFF0EA5E9)),
                      onPressed: () {
                        _mapController.move(LatLng(lat, lng), 16);
                        Navigator.pop(context);
                      },
                    ),
                ],
              ),
              const Divider(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.star, color: Colors.amber, size: 20),
                      const SizedBox(width: 4),
                      Text(
                        '${eatery['rating_avg'] ?? '5.0'} / 5.0',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(width: 4),
                       Text(
                        '(${eatery['reviews_count'] ?? '0'} đánh giá)',
                        style: TextStyle(color: Colors.grey[500], fontSize: 12),
                      ),
                    ],
                  ),
                  Text(
                    eatery['commune']?['name'] ?? 'Đông Anh',
                    style: TextStyle(color: Colors.grey[600], fontSize: 13),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(context);
                  _openCheckinDialog(eatery);
                },
                icon: const Icon(Icons.location_on),
                label: const Text('Check-in tại đây'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0EA5E9),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _openCheckinDialog(dynamic eatery) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return _LoginCheckinModal(
          eateryId: eatery['id'],
          eateryName: eatery['name'],
          onSubmit: (rating, comment, guestName, imagePath) async {
            final res = await ApiService.storeCheckin(
              eateryId: eatery['id'],
              rating: rating,
              comment: comment,
              guestName: guestName,
              imagePath: imagePath,
            );
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Thành công'),
                  backgroundColor: res['success'] == true ? Colors.green : Colors.red,
                ),
              );
            }
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF0EA5E9);

    // Filter valid markers
    final markers = <Marker>[];
    for (var eatery in _eateries) {
      double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
      double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');
      if (lat != null && lng != null) {
        markers.add(
          Marker(
            point: LatLng(lat, lng),
            width: 40,
            height: 40,
            alignment: Alignment.center,
            child: GestureDetector(
              onTap: () => _showEateryDetails(eatery),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Icon(Icons.location_on, color: primaryColor, size: 40),
                  const Positioned(
                    top: 6,
                    child: Icon(Icons.restaurant, color: Colors.white, size: 16),
                  ),
                ],
              ),
            ),
          ),
        );
      }
    }

    if (_userLocation != null) {
      markers.add(
        Marker(
          point: _userLocation!,
          width: 32,
          height: 32,
          child: Container(
            decoration: BoxDecoration(
              color: Colors.blue.withOpacity(0.3),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Container(
                width: 14,
                height: 14,
                decoration: BoxDecoration(
                  color: Colors.blue,
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2),
                ),
              ),
            ),
          ),
        ),
      );
    }

    return Scaffold(
      body: Stack(
        children: [
          // Leaflet Map
          FlutterMap(
            mapController: _mapController,
            options: MapOptions(
              initialCenter: _center,
              initialZoom: 13,
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.donganh.discovery',
              ),
              MarkerLayer(markers: markers),
            ],
          ),

          // Categories Selector (Top)
          Positioned(
            top: 50,
            left: 0,
            right: 0,
            child: SizedBox(
              height: 44,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: _categories.length,
                itemBuilder: (context, index) {
                  final cat = _categories[index];
                  final isSelected = _selectedCategory == cat['slug'];
                  return Padding(
                    padding: const EdgeInsets.only(right: 8.0),
                    child: ChoiceChip(
                      label: Text(
                        cat['name'] ?? 'Danh mục',
                        style: TextStyle(
                           color: isSelected ? Colors.white : Colors.grey[700],
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                        ),
                      ),
                      selected: isSelected,
                      selectedColor: primaryColor,
                      backgroundColor: Colors.white.withOpacity(0.95),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                        side: BorderSide(
                          color: isSelected ? primaryColor : Colors.grey[200]!,
                        ),
                      ),
                      onSelected: (selected) {
                        if (selected) {
                          setState(() {
                            _selectedCategory = cat['slug'];
                          });
                          _loadEateries();
                        }
                      },
                    ),
                  );
                },
              ),
            ),
          ),

          // Floating Action Buttons (GPS location & Loading indicator)
          Positioned(
            bottom: 24,
            right: 16,
            child: Column(
              children: [
                if (_isLoading)
                  Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(12),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 6)],
                    ),
                    child: SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(color: primaryColor, strokeWidth: 2),
                    ),
                  ),
                FloatingActionButton(
                  onPressed: _getLocation,
                  backgroundColor: Colors.white,
                  foregroundColor: primaryColor,
                  child: const Icon(Icons.my_location),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
