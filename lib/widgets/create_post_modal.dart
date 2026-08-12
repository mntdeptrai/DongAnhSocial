import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';

void showCreatePostModal(BuildContext context, {VoidCallback? onPostSuccess}) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (ctx) => CreatePostModal(onPostSuccess: onPostSuccess),
  );
}

class CreatePostModal extends StatefulWidget {
  final VoidCallback? onPostSuccess;

  const CreatePostModal({super.key, this.onPostSuccess});

  @override
  State<CreatePostModal> createState() => _CreatePostModalState();
}

class _CreatePostModalState extends State<CreatePostModal> {
  final TextEditingController _textController = TextEditingController();
  final ImagePicker _picker = ImagePicker();

  final List<XFile> _selectedFiles = [];
  bool _isPublishing = false;
  String _uploadStatus = '';
  String _selectedPrivacy = 'Công khai';
  String? _selectedMusic;
  String? _selectedAlbum;
  List<dynamic> _taggedFriends = [];
  String? _locationTag;

  @override
  void dispose() {
    _textController.dispose();
    super.dispose();
  }

  void _openTagFriendsModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        String searchQuery = '';
        List<dynamic> friendsList = [];
        bool isLoadingFriends = true;
        List<dynamic> tempSelected = List.from(_taggedFriends);

        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            void loadFriends([String? query]) async {
              setModalState(() => isLoadingFriends = true);
              final res = await ApiService.getFriends(search: query);
              if (modalCtx.mounted) {
                setModalState(() {
                  friendsList = res;
                  isLoadingFriends = false;
                });
              }
            }

            if (isLoadingFriends && friendsList.isEmpty && searchQuery.isEmpty) {
              loadFriends();
            }

            return Container(
              height: MediaQuery.of(context).size.height * 0.75,
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(modalCtx).viewInsets.bottom + 16,
                top: 16,
                left: 16,
                right: 16,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                children: [
                  Center(
                    child: Container(
                      width: 38,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        '🏷️ Gắn thẻ bạn bè',
                        style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                      ),
                      ElevatedButton(
                        onPressed: () {
                          setState(() {
                            _taggedFriends = tempSelected;
                          });
                          Navigator.pop(modalCtx);
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0EA5E9),
                          foregroundColor: Colors.white,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        ),
                        child: Text(
                          'Xong (${tempSelected.length})',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  TextField(
                    decoration: InputDecoration(
                      hintText: '🔍 Tìm tên bạn bè, người dùng...',
                      hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
                      filled: true,
                      fillColor: const Color(0xFFF1F5F9),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                    ),
                    onChanged: (val) {
                      searchQuery = val;
                      loadFriends(val);
                    },
                  ),
                  const SizedBox(height: 12),

                  Expanded(
                    child: isLoadingFriends
                        ? const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)))
                        : friendsList.isEmpty
                            ? const Center(
                                child: Text('Không tìm thấy bạn bè nào', style: TextStyle(color: Color(0xFF64748B))),
                              )
                            : ListView.separated(
                                itemCount: friendsList.length,
                                separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
                                itemBuilder: (ctx, idx) {
                                  final friend = friendsList[idx];
                                  final friendId = friend['id'];
                                  final friendName = friend['name'] ?? 'Bạn bè';
                                  final friendAvatar = ApiService.getAvatarUrl(friend, friendName);
                                  final isChecked = tempSelected.any((f) => f['id'] == friendId);

                                  return ListTile(
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                                    leading: CircleAvatar(
                                      backgroundImage: NetworkImage(friendAvatar),
                                    ),
                                    title: Text(friendName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                    subtitle: Text(friend['email'] ?? friend['role'] ?? 'Thành viên', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                    trailing: Checkbox(
                                      value: isChecked,
                                      activeColor: const Color(0xFF0EA5E9),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                      onChanged: (val) {
                                        setModalState(() {
                                          if (val == true) {
                                            tempSelected.add(friend);
                                          } else {
                                            tempSelected.removeWhere((f) => f['id'] == friendId);
                                          }
                                        });
                                      },
                                    ),
                                    onTap: () {
                                      setModalState(() {
                                        if (isChecked) {
                                          tempSelected.removeWhere((f) => f['id'] == friendId);
                                        } else {
                                          tempSelected.add(friend);
                                        }
                                      });
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

  Future<void> _pickMultiImages() async {
    try {
      final List<XFile> picked = await _picker.pickMultiImage(
        imageQuality: 85,
        maxWidth: 2048,
        maxHeight: 2048,
      );
      if (picked.isNotEmpty) {
        setState(() {
          _selectedFiles.addAll(picked);
        });
      }
    } catch (e) {
      debugPrint('Lỗi chọn nhiều ảnh: $e');
    }
  }

  Future<void> _pickVideo() async {
    try {
      final XFile? video = await _picker.pickVideo(
        source: ImageSource.gallery,
        maxDuration: const Duration(minutes: 10),
      );
      if (video != null) {
        setState(() {
          _selectedFiles.add(video);
        });
      }
    } catch (e) {
      debugPrint('Lỗi chọn video: $e');
    }
  }

  Future<void> _takeCameraPhoto() async {
    try {
      final XFile? photo = await _picker.pickImage(
        source: ImageSource.camera,
        imageQuality: 85,
        maxWidth: 2048,
        maxHeight: 2048,
      );
      if (photo != null) {
        setState(() {
          _selectedFiles.add(photo);
        });
      }
    } catch (e) {
      debugPrint('Lỗi chụp ảnh: $e');
    }
  }

  void _removeFile(int index) {
    setState(() {
      _selectedFiles.removeAt(index);
    });
  }

  Future<void> _handlePublish() async {
    final text = _textController.text.trim();
    if (text.isEmpty && _selectedFiles.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vui lòng nhập nội dung hoặc đính kèm ảnh/video!'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() {
      _isPublishing = true;
      _uploadStatus = 'Đang chuẩn bị...';
    });

    final List<String> imageUrls = [];
    final List<String> videoUrls = [];

    if (_selectedFiles.isNotEmpty) {
      setState(() {
        _uploadStatus = 'Đang tải ${_selectedFiles.length} tệp lên Cloudflare R2...';
      });

      final localPaths = _selectedFiles.map((f) => f.path).toList();
      final uploadedItems = await ApiService.uploadFilesToR2(localPaths, folder: 'posts');

      for (var item in uploadedItems) {
        final url = item['url'];
        final type = item['type'];
        if (url != null) {
          if (type == 'video' || url.endsWith('.mp4') || url.endsWith('.mov') || url.endsWith('.avi') || url.endsWith('.mkv')) {
            videoUrls.add(url);
          } else {
            imageUrls.add(url);
          }
        }
      }
    }

    setState(() {
      _uploadStatus = 'Đang xuất bản bài viết...';
    });

    String fullDescription = text;
    if (_taggedFriends.isNotEmpty) {
      final names = _taggedFriends.map((f) => (f['name'] ?? 'Bạn bè').toString()).join(', ');
      fullDescription += '\n🏷️ Cùng với $names';
    }
    if (_locationTag != null) {
      fullDescription += '\n📍 tại $_locationTag';
    }
    if (_selectedMusic != null) {
      fullDescription += '\n🎵 Nhạc: $_selectedMusic';
    }

    final res = await ApiService.createPost(
      description: fullDescription,
      name: text.length > 50 ? '${text.substring(0, 50)}...' : text,
      images: imageUrls.isNotEmpty ? imageUrls : null,
      videos: videoUrls.isNotEmpty ? videoUrls : null,
      imagePath: imageUrls.isNotEmpty ? imageUrls.first : null,
    );

    if (mounted) {
      setState(() {
        _isPublishing = false;
      });

      if (res['success'] == true) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Đã đăng bài viết thành công lên Cloudflare R2!'),
            backgroundColor: const Color(0xFF059669),
          ),
        );
        widget.onPostSuccess?.call();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Đăng bài viết thất bại!'),
            backgroundColor: const Color(0xFFEF4444),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser;
    final userName = user?['name'] ?? user?['username'] ?? 'Thành viên Đông Anh';
    final avatarUrl = ApiService.getAvatarUrl(user, userName);

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.92,
      ),
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag Handle
          const SizedBox(height: 10),
          Container(
            width: 38,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey.shade300,
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          // Header Bar
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: const BoxDecoration(
                      color: Color(0xFFF1F5F9),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.close_rounded, size: 20, color: Color(0xFF64748B)),
                  ),
                ),
                const Text(
                  'Tạo bài viết',
                  style: TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF0F172A),
                  ),
                ),
                ElevatedButton(
                  onPressed: _isPublishing ? null : _handlePublish,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0EA5E9),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  ),
                  child: _isPublishing
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Đăng', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: Color(0xFFE2E8F0)),

          // Modal Scroll Content
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // User Info & Privacy Pill Row
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 22,
                        backgroundColor: const Color(0xFF0EA5E9).withValues(alpha: 0.1),
                        backgroundImage: NetworkImage(avatarUrl),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            userName,
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                          const SizedBox(height: 4),
                          GestureDetector(
                            onTap: () {
                              setState(() {
                                _selectedPrivacy = _selectedPrivacy == 'Công khai' ? 'Bạn bè' : 'Công khai';
                              });
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    _selectedPrivacy == 'Công khai' ? Icons.public_rounded : Icons.people_rounded,
                                    size: 13,
                                    color: const Color(0xFF475569),
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    _selectedPrivacy,
                                    style: const TextStyle(
                                      color: Color(0xFF475569),
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  const SizedBox(width: 2),
                                  const Icon(Icons.arrow_drop_down_rounded, size: 16, color: Color(0xFF475569)),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  if (_taggedFriends.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFBAE6FD)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.local_offer_rounded, size: 14, color: Color(0xFF0EA5E9)),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              'Cùng với ${_taggedFriends.map((f) => f['name'] ?? 'Bạn bè').join(', ')}',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0284C7)),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          GestureDetector(
                            onTap: () => setState(() => _taggedFriends.clear()),
                            child: const Icon(Icons.close_rounded, size: 16, color: Color(0xFF0284C7)),
                          ),
                        ],
                      ),
                    ),
                  ],
                  const SizedBox(height: 14),

                  // Text Input ("Bạn đang nghĩ gì?")
                  TextField(
                    controller: _textController,
                    maxLines: null,
                    minLines: 4,
                    style: const TextStyle(fontSize: 16, color: Color(0xFF0F172A), height: 1.4),
                    decoration: const InputDecoration(
                      hintText: 'Bạn đang nghĩ gì?',
                      hintStyle: TextStyle(color: Color(0xFF94A3B8), fontSize: 18, fontWeight: FontWeight.normal),
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.zero,
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Progress & Status Text (if uploading)
                  if (_isPublishing) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0F9FF),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFBAE6FD)),
                      ),
                      child: Row(
                        children: [
                          const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0EA5E9)),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              _uploadStatus,
                              style: const TextStyle(color: Color(0xFF0284C7), fontSize: 13, fontWeight: FontWeight.w600),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Selected Media Previews
                  if (_selectedFiles.isNotEmpty) ...[
                    Container(
                      height: 120,
                      margin: const EdgeInsets.only(bottom: 14),
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: _selectedFiles.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 10),
                        itemBuilder: (context, index) {
                          final file = _selectedFiles[index];
                          final isVideo = file.path.endsWith('.mp4') || file.path.endsWith('.mov') || file.path.endsWith('.avi');

                          return Stack(
                            children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  width: 110,
                                  height: 120,
                                  color: const Color(0xFF1E293B),
                                  child: isVideo
                                      ? const Center(
                                          child: Icon(Icons.play_circle_fill_rounded, color: Colors.white, size: 36),
                                        )
                                      : Image.file(
                                          File(file.path),
                                          fit: BoxFit.cover,
                                          width: 110,
                                          height: 120,
                                        ),
                                ),
                              ),

                              // Selection Badge Index (1, 2, 3...)
                              Positioned(
                                top: 6,
                                left: 6,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF0EA5E9),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    '${index + 1}',
                                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ),

                              // Remove button
                              Positioned(
                                top: 6,
                                right: 6,
                                child: GestureDetector(
                                  onTap: () => _removeFile(index),
                                  child: Container(
                                    padding: const EdgeInsets.all(4),
                                    decoration: const BoxDecoration(
                                      color: Colors.black54,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.close_rounded, size: 14, color: Colors.white),
                                  ),
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                    ),
                  ],

                  // Aa Style Button & Quick Action Chips Row
                  Row(
                    children: [
                      // Rainbow Ring "Aa" Style Picker Button
                      Container(
                        width: 38,
                        height: 38,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: SweepGradient(
                            colors: [
                              Colors.red,
                              Colors.orange,
                              Colors.yellow,
                              Colors.green,
                              Colors.blue,
                              Colors.purple,
                              Colors.red,
                            ],
                          ),
                        ),
                        padding: const EdgeInsets.all(2.5),
                        child: Container(
                          decoration: const BoxDecoration(
                            color: Colors.white,
                            shape: BoxShape.circle,
                          ),
                          child: const Center(
                            child: Text(
                              'Aa',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),

                      // Horizontal Scrollable Action Chips (Nhạc, Album, Với bạn bè)
                      Expanded(
                        child: SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: Row(
                            children: [
                              _buildActionChip(
                                icon: Icons.music_note_rounded,
                                label: _selectedMusic ?? 'Nhạc',
                                isSelected: _selectedMusic != null,
                                onTap: () {
                                  setState(() {
                                    _selectedMusic = _selectedMusic == null ? 'BGM Đông Anh' : null;
                                  });
                                },
                              ),
                              const SizedBox(width: 8),
                              _buildActionChip(
                                icon: Icons.photo_album_rounded,
                                label: _selectedAlbum ?? 'Album',
                                isSelected: _selectedAlbum != null,
                                onTap: () {
                                  setState(() {
                                    _selectedAlbum = _selectedAlbum == null ? 'Kỷ niệm Đông Anh' : null;
                                  });
                                },
                              ),
                              const SizedBox(width: 8),
                              Builder(
                                builder: (context) {
                                  String tagLabel = 'Với bạn bè';
                                  if (_taggedFriends.length == 1) {
                                    tagLabel = 'Cùng với ${_taggedFriends[0]['name']}';
                                  } else if (_taggedFriends.length > 1) {
                                    tagLabel = 'Cùng với ${_taggedFriends[0]['name']} +${_taggedFriends.length - 1}';
                                  }

                                  return _buildActionChip(
                                    icon: Icons.local_offer_rounded,
                                    label: tagLabel,
                                    isSelected: _taggedFriends.isNotEmpty,
                                    onTap: _openTagFriendsModal,
                                  );
                                },
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Divider(height: 1, color: Color(0xFFE2E8F0)),
                  const SizedBox(height: 12),

                  // Toolbar Row with 5 Action Icons (Emoji, Gallery, Video, Link, Location)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.sentiment_satisfied_alt_rounded, color: Color(0xFFF59E0B), size: 26),
                        onPressed: () {
                          _textController.text = '${_textController.text} 😊';
                        },
                      ),
                      IconButton(
                        icon: const Icon(Icons.photo_library_rounded, color: Color(0xFF10B981), size: 26),
                        onPressed: _pickMultiImages,
                        tooltip: 'Chọn nhiều ảnh',
                      ),
                      IconButton(
                        icon: const Icon(Icons.play_circle_fill_rounded, color: Color(0xFFEF4444), size: 26),
                        onPressed: _pickVideo,
                        tooltip: 'Chọn video',
                      ),
                      IconButton(
                        icon: const Icon(Icons.link_rounded, color: Color(0xFF3B82F6), size: 26),
                        onPressed: () {
                          _textController.text = '${_textController.text} https://';
                        },
                      ),
                      IconButton(
                        icon: const Icon(Icons.location_on_rounded, color: Color(0xFFEC4899), size: 26),
                        onPressed: () {
                          setState(() {
                            _locationTag = _locationTag == null ? 'Đông Anh, Hà Nội' : null;
                          });
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Integrated Bottom Media Grid (Camera Tile + Quick Photo Pickers)
                  GridView.count(
                    crossAxisCount: 3,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisSpacing: 8,
                    mainAxisSpacing: 8,
                    children: [
                      // Tile 1: Camera Tile ("Chụp ảnh")
                      InkWell(
                        onTap: _takeCameraPhoto,
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: const Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.camera_alt_rounded, size: 30, color: Color(0xFF64748B)),
                              SizedBox(height: 6),
                              Text(
                                'Chụp ảnh',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF475569),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                      // Tile 2: Multi-Image Picker Button Tile
                      InkWell(
                        onTap: _pickMultiImages,
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFA7F3D0)),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.collections_rounded, size: 28, color: Color(0xFF10B981)),
                              const SizedBox(height: 4),
                              Text(
                                _selectedFiles.isEmpty ? 'Chọn nhiều ảnh' : 'Thêm tệp (${_selectedFiles.length})',
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF047857),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                      // Tile 3: Video Picker Tile
                      InkWell(
                        onTap: _pickVideo,
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF2F2),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFFECACA)),
                          ),
                          child: const Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.video_library_rounded, size: 28, color: Color(0xFFEF4444)),
                              SizedBox(height: 4),
                              Text(
                                'Tải lên Video',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFFB91C1C),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionChip({
    required IconData icon,
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF0EA5E9).withValues(alpha: 0.12) : const Color(0xFFFFFFFF),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFFE2E8F0),
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 15,
              color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF475569),
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 13,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF334155),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
