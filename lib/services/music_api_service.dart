import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class StoryMusicTrack {
  final String id;
  final String name;
  final String artist;
  final String category;
  final String audioUrl;
  final String? artworkUrl;
  final String duration;
  final bool isLocal;

  const StoryMusicTrack({
    required this.id,
    required this.name,
    required this.artist,
    required this.category,
    required this.audioUrl,
    this.artworkUrl,
    this.duration = '0:30',
    this.isLocal = false,
  });

  int get totalSeconds {
    try {
      final parts = duration.split(':');
      if (parts.length == 2) {
        final sec = (int.parse(parts[0]) * 60) + int.parse(parts[1]);
        if (sec > 0) return sec;
      }
    } catch (_) {}
    return 210;
  }

  factory StoryMusicTrack.fromJson(Map<String, dynamic> json, {String category = 'all'}) {
    final trackId = (json['trackId'] ?? json['collectionId'] ?? DateTime.now().millisecondsSinceEpoch).toString();
    final trackName = (json['trackName'] ?? json['collectionName'] ?? 'Không tên').toString();
    final artistName = (json['artistName'] ?? 'Nghệ sĩ').toString();
    final previewUrl = (json['previewUrl'] ?? '').toString();
    final rawArtwork = (json['artworkUrl100'] ?? json['artworkUrl60'] ?? '').toString();
    final artworkUrl = rawArtwork.isNotEmpty
        ? rawArtwork.replaceAll('100x100bb', '300x300bb')
        : null;

    final millis = json['trackTimeMillis'] as int?;
    final durationStr = millis != null && millis > 0
        ? '${(millis ~/ 60000)}:${((millis % 60000) ~/ 1000).toString().padLeft(2, '0')}'
        : '0:30';

    return StoryMusicTrack(
      id: trackId,
      name: trackName,
      artist: artistName,
      category: category,
      audioUrl: previewUrl,
      artworkUrl: artworkUrl,
      duration: durationStr,
      isLocal: false,
    );
  }
}

class MusicApiService {
  static const String _baseUrl = 'https://itunes.apple.com/search';

  // Cache in-memory for instant response
  static final Map<String, List<StoryMusicTrack>> _cache = {};

  /// Search tracks live from Apple Music / iTunes
  static Future<List<StoryMusicTrack>> search(String query) async {
    final cleanQuery = query.trim();
    if (cleanQuery.isEmpty) return fetchTrending();

    final cacheKey = 'search_${cleanQuery.toLowerCase()}';
    if (_cache.containsKey(cacheKey)) {
      return _cache[cacheKey]!;
    }

    try {
      final uri = Uri.parse('$_baseUrl?term=${Uri.encodeComponent(cleanQuery)}&country=VN&media=music&entity=song&limit=30');
      final response = await http.get(uri).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final results = (data['results'] as List? ?? []);
        final tracks = results
            .map((item) => StoryMusicTrack.fromJson(item as Map<String, dynamic>, category: 'search'))
            .where((track) => track.audioUrl.isNotEmpty)
            .toList();

        _cache[cacheKey] = tracks;
        return tracks;
      }
    } catch (e) {
      debugPrint('[MusicApiService] Search error: $e');
    }

    return defaultCuratedTracks.where((t) {
      final q = cleanQuery.toLowerCase();
      return t.name.toLowerCase().contains(q) || t.artist.toLowerCase().contains(q);
    }).toList();
  }

  /// Fetch tracks based on selected category
  static Future<List<StoryMusicTrack>> fetchByCategory(String category) async {
    if (category == 'all' || category == 'trending') {
      return fetchTrending();
    }

    final cacheKey = 'cat_$category';
    if (_cache.containsKey(cacheKey)) {
      return _cache[cacheKey]!;
    }

    String searchTerm = 'vpop';
    switch (category) {
      case 'donganh':
        searchTerm = 'que huong viet nam';
        break;
      case 'chill':
        searchTerm = 'chill acoustic viet';
        break;
      case 'remix':
        searchTerm = 'remix viet nam';
        break;
      case 'love':
        searchTerm = 'tinh ca viet nam';
        break;
    }

    try {
      final uri = Uri.parse('$_baseUrl?term=${Uri.encodeComponent(searchTerm)}&country=VN&media=music&entity=song&limit=25');
      final response = await http.get(uri).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final results = (data['results'] as List? ?? []);
        final tracks = results
            .map((item) => StoryMusicTrack.fromJson(item as Map<String, dynamic>, category: category))
            .where((track) => track.audioUrl.isNotEmpty)
            .toList();

        if (tracks.isNotEmpty) {
          _cache[cacheKey] = tracks;
          return tracks;
        }
      }
    } catch (e) {
      debugPrint('[MusicApiService] Fetch category error: $e');
    }

    return defaultCuratedTracks.where((t) => t.category == category || category == 'all').toList();
  }

  /// Top V-Pop Trending tracks
  static Future<List<StoryMusicTrack>> fetchTrending() async {
    const cacheKey = 'trending';
    if (_cache.containsKey(cacheKey) && _cache[cacheKey]!.isNotEmpty) {
      return _cache[cacheKey]!;
    }

    try {
      final uri = Uri.parse('$_baseUrl?term=Son+Tung+M-TP&country=VN&media=music&entity=song&limit=30');
      final response = await http.get(uri).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final results = (data['results'] as List? ?? []);
        final tracks = results
            .map((item) => StoryMusicTrack.fromJson(item as Map<String, dynamic>, category: 'trending'))
            .where((track) => track.audioUrl.isNotEmpty)
            .toList();

        if (tracks.isNotEmpty) {
          _cache[cacheKey] = tracks;
          return tracks;
        }
      }
    } catch (e) {
      debugPrint('[MusicApiService] Fetch trending error: $e');
    }

    return defaultCuratedTracks;
  }

  static const List<StoryMusicTrack> defaultCuratedTracks = [
    StoryMusicTrack(
      id: '1749963740',
      name: 'Đừng Làm Trái Tim Anh Đau',
      artist: 'Sơn Tùng M-TP',
      category: 'trending',
      audioUrl: 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview211/v4/b0/13/7c/b0137c01-aa71-4764-11a0-8930dbd3afe1/mzaf_9928953135043326013.plus.aac.p.m4a',
      artworkUrl: 'https://is1-ssl.mzstatic.com/image/thumb/Music211/v4/e3/0b/38/e30b383e-5818-321a-7626-557b7b0f8ba3/24UMGIM61359.rgb.jpg/300x300bb.jpg',
      duration: '4:39',
    ),
    StoryMusicTrack(
      id: '1710095788',
      name: 'Bạn Đời (feat. GDucky)',
      artist: 'Karik',
      category: 'trending',
      audioUrl: 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview221/v4/e9/dd/3a/e9dd3a9c-ff14-6b7c-f993-bd595fabdef7/mzaf_12599409504931212297.plus.aac.p.m4a',
      artworkUrl: 'https://is1-ssl.mzstatic.com/image/thumb/Music116/v4/3a/48/93/3a489327-9939-cd14-13e6-d32b991bed74/5054197800085.jpg/300x300bb.jpg',
      duration: '5:00',
    ),
    StoryMusicTrack(
      id: '1708740818',
      name: 'Nơi Này Có Anh',
      artist: 'Sơn Tùng M-TP',
      category: 'love',
      audioUrl: 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview211/v4/39/0f/d4/390fd42f-dcff-6569-1055-cfb9df07c71a/mzaf_16192551629341831457.plus.aac.p.m4a',
      artworkUrl: 'https://is1-ssl.mzstatic.com/image/thumb/Music116/v4/13/5c/57/135c57dd-5297-81f3-0f56-1ac5dad47f8c/23UM1IM10890.rgb.jpg/300x300bb.jpg',
      duration: '4:20',
    ),
    StoryMusicTrack(
      id: '1710356278',
      name: 'Âm Thầm Bên Em',
      artist: 'Sơn Tùng M-TP',
      category: 'chill',
      audioUrl: 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview211/v4/2a/1d/be/2a1dbea4-6b4d-4fe5-0bfe-8847558c4261/mzaf_6610910292119959219.plus.aac.p.m4a',
      artworkUrl: 'https://is1-ssl.mzstatic.com/image/thumb/Music116/v4/63/1b/1a/631b1a1d-f87c-2864-1b9f-47ffd625e33f/23UM1IM10806.rgb.jpg/300x300bb.jpg',
      duration: '4:53',
    ),
    StoryMusicTrack(
      id: '6770896822',
      name: 'Come My Way',
      artist: 'Sơn Tùng M-TP & Tyga',
      category: 'remix',
      audioUrl: 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview211/v4/f9/c9/76/f9c97670-bc06-2904-0e30-11ac6639a817/mzaf_17462857484679588249.plus.aac.p.m4a',
      artworkUrl: 'https://is1-ssl.mzstatic.com/image/thumb/Music211/v4/fb/8d/17/fb8d17ce-e565-7106-db5f-f1f50eccc347/823375188872_Cover.jpg/300x300bb.jpg',
      duration: '3:12',
    ),
    StoryMusicTrack(
      id: '1567406117',
      name: 'The Playah (Special Performance)',
      artist: 'SOOBIN',
      category: 'remix',
      audioUrl: 'https://audio-ssl.itunes.apple.com/itunes-assets/AudioPreview125/v4/54/28/79/542879e5-1313-52e9-2ad0-2b5d25dc82e7/mzaf_17677093022491967865.plus.aac.p.m4a',
      artworkUrl: 'https://is1-ssl.mzstatic.com/image/thumb/Music115/v4/a4/96/2e/a4962e07-f86a-1079-f534-0a29255eb246/190296665738.jpg/300x300bb.jpg',
      duration: '7:29',
    ),
  ];
}
