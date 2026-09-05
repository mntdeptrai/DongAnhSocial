import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:permission_handler/permission_handler.dart';
import '../services/api_service.dart';

class ActiveCallScreen extends StatefulWidget {
  final String friendName;
  final int friendId;
  final int? callId; // null nếu là caller (sẽ tạo mới từ API)
  final bool isCaller;
  final bool isVideo;
  final Function(int durationSeconds) onCallEnded;

  const ActiveCallScreen({
    super.key,
    required this.friendName,
    required this.friendId,
    this.callId,
    required this.isCaller,
    required this.isVideo,
    required this.onCallEnded,
  });

  @override
  State<ActiveCallScreen> createState() => _ActiveCallScreenState();
}

class _ActiveCallScreenState extends State<ActiveCallScreen> {
  bool _isMuted = false;
  bool _isVideoOn = true;
  bool _isSpeakerOn = true;
  bool _isConnected = false;
  int _secondsElapsed = 0;
  Timer? _callTimer;
  Timer? _statusPollTimer;
  int? _activeCallId;
  bool _callEnded = false;

  final _localRenderer = RTCVideoRenderer();
  final _remoteRenderer = RTCVideoRenderer();
  RTCPeerConnection? _peerConnection;
  MediaStream? _localStream;
  MediaStream? _remoteStream;

  @override
  void initState() {
    super.initState();
    _isVideoOn = widget.isVideo;
    _activeCallId = widget.callId;

    _initWebRTC();
  }

  Future<void> _initWebRTC() async {
    // Xin quyền Micro & Camera hiển thị Dialog của iOS/Android
    try {
      await [
        Permission.microphone,
        if (widget.isVideo) Permission.camera,
      ].request();
    } catch (e) {
      debugPrint('[Permissions] Error requesting permissions: $e');
    }

    await _localRenderer.initialize();
    await _remoteRenderer.initialize();

    final configuration = <String, dynamic>{
      'iceServers': [
        {'urls': 'stun:stun.l.google.com:19302'},
        {'urls': 'stun:openrelay.metered.ca:80'},
        {'urls': 'turn:openrelay.metered.ca:80', 'username': 'openrelayproject', 'credential': 'openrelayproject'},
        {'urls': 'turn:openrelay.metered.ca:443', 'username': 'openrelayproject', 'credential': 'openrelayproject'},
        {'urls': 'turn:openrelay.metered.ca:443?transport=tcp', 'username': 'openrelayproject', 'credential': 'openrelayproject'},
      ],
      'iceCandidatePoolSize': 10,
    };

    try {
      _peerConnection = await createPeerConnection(configuration);

      _peerConnection?.onIceCandidate = (candidate) {
        if (_activeCallId != null && candidate.candidate != null && candidate.candidate!.isNotEmpty) {
          final candMap = {
            'candidate': {
              'candidate': candidate.candidate,
              'sdpMid': candidate.sdpMid,
              'sdpMLineIndex': candidate.sdpMLineIndex,
            }
          };
          ApiService.sendSignal(_activeCallId!, widget.friendId, jsonEncode(candMap));
        }
      };

      _peerConnection?.onTrack = (event) {
        if (event.streams.isNotEmpty) {
          setState(() {
            _remoteStream = event.streams[0];
            _remoteRenderer.srcObject = _remoteStream;
          });
        }
      };

      _peerConnection?.onAddStream = (stream) {
        setState(() {
          _remoteStream = stream;
          _remoteRenderer.srcObject = stream;
        });
      };

      _peerConnection?.onIceConnectionState = (state) {
        debugPrint('[WebRTC] ICE Connection State: $state');
        if (state == RTCIceConnectionState.RTCIceConnectionStateConnected ||
            state == RTCIceConnectionState.RTCIceConnectionStateCompleted) {
          if (mounted && !_isConnected) {
            setState(() => _isConnected = true);
            _startDurationTimer();
          }
        } else if (state == RTCIceConnectionState.RTCIceConnectionStateFailed) {
          debugPrint('[WebRTC] ICE Connection FAILED - P2P không thể kết nối!');
        }
      };

      _peerConnection?.onConnectionState = (state) {
        debugPrint('[WebRTC] Peer Connection State: $state');
      };

      final mediaConstraints = <String, dynamic>{
        'audio': true,
        'video': widget.isVideo ? {'facingMode': 'user'} : false,
      };

      _localStream = await navigator.mediaDevices.getUserMedia(mediaConstraints);
      _localRenderer.srcObject = _localStream;

      _localStream?.getTracks().forEach((track) {
        _peerConnection?.addTrack(track, _localStream!);
      });
    } catch (e) {
      debugPrint('[WebRTC] Native setup warning: $e');
    }

    if (widget.isCaller) {
      _initiateCallOnServer();
    } else {
      _answerCallOnServer();
    }
  }

  /// Caller: Tạo SDP Offer → Gọi API khởi tạo cuộc gọi → Polling chờ Receiver
  Future<void> _initiateCallOnServer() async {
    String offerSdpStr = '';
    if (_peerConnection != null) {
      try {
        final offer = await _peerConnection!.createOffer({
          'offerToReceiveAudio': 1,
          'offerToReceiveVideo': widget.isVideo ? 1 : 0,
        });
        await _peerConnection!.setLocalDescription(offer);
        offerSdpStr = jsonEncode({'type': offer.type, 'sdp': offer.sdp});
      } catch (e) {
        debugPrint('[WebRTC] Create offer error: $e');
      }
    }

    final res = await ApiService.initiateCall(
      widget.friendId,
      widget.isVideo ? 'video' : 'audio',
      signalData: offerSdpStr.isNotEmpty ? offerSdpStr : null,
    );
    if (!mounted) return;

    if (res['status'] == 'success' && res['call_id'] != null) {
      final cid = res['call_id'] is int ? res['call_id'] as int : int.tryParse(res['call_id'].toString()) ?? 0;
      setState(() => _activeCallId = cid);
      _startStatusPolling();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Lỗi khởi tạo cuộc gọi')),
      );
      Navigator.of(context).pop();
    }
  }

  /// Receiver: Lấy SDP Offer từ Caller → Tạo SDP Answer → Gửi Answer lên server
  Future<void> _answerCallOnServer() async {
    if (_activeCallId != null && _peerConnection != null) {
      try {
        final status = await ApiService.getCallStatus(_activeCallId!);
        if (status['signal_data'] != null) {
          final offerMap = jsonDecode(status['signal_data']);
          if (offerMap['sdp'] != null && offerMap['sdp'].toString().isNotEmpty) {
            await _peerConnection!.setRemoteDescription(
              RTCSessionDescription(offerMap['sdp'], offerMap['type'] ?? 'offer'),
            );

            final answer = await _peerConnection!.createAnswer({
              'offerToReceiveAudio': 1,
              'offerToReceiveVideo': widget.isVideo ? 1 : 0,
            });
            await _peerConnection!.setLocalDescription(answer);

            final answerMap = {'type': answer.type, 'sdp': answer.sdp};
            await ApiService.answerCall(_activeCallId!, widget.friendId, signalData: jsonEncode(answerMap));
          }
        }
      } catch (e) {
        debugPrint('[WebRTC] Answer error: $e');
      }
    } else if (_activeCallId != null) {
      await ApiService.answerCall(_activeCallId!, widget.friendId);
    }

    if (!mounted) return;
    // Don't set _isConnected here — let onIceConnectionState handle it
    _startStatusPolling();
  }

  /// Polling trạng thái cuộc gọi + Trao đổi SDP Answer & ICE candidates mỗi 2 giây
  void _startStatusPolling() {
    _statusPollTimer?.cancel();
    _statusPollTimer = Timer.periodic(const Duration(seconds: 2), (timer) async {
      if (!mounted || _activeCallId == null || _callEnded) {
        timer.cancel();
        return;
      }
      final status = await ApiService.getCallStatus(_activeCallId!);
      if (!mounted || _callEnded) return;

      final callStatus = status['status']?.toString() ?? '';

      // Caller: Phát hiện Receiver đã chấp nhận -> nạp SDP Answer từ Receiver
      if (widget.isCaller && !_isConnected && callStatus == 'answered') {
        if (status['signal_data'] != null && _peerConnection != null) {
          try {
            final ansMap = jsonDecode(status['signal_data']);
            if (ansMap['sdp'] != null && ansMap['sdp'].toString().isNotEmpty) {
              await _peerConnection!.setRemoteDescription(
                RTCSessionDescription(ansMap['sdp'], ansMap['type'] ?? 'answer'),
              );
            }
          } catch (e) {
            debugPrint('[WebRTC] Set remote answer error: $e');
          }
        }
        // Don't set _isConnected here — let onIceConnectionState handle it
      }

      // Receiver: Nếu chưa có Remote Description -> Thử nạp SDP Offer từ Caller nếu có sẵn
      if (!widget.isCaller && _peerConnection != null) {
        final remoteDesc = await _peerConnection!.getRemoteDescription();
        if (remoteDesc == null && status['signal_data'] != null) {
          try {
            final offerMap = jsonDecode(status['signal_data']);
            if (offerMap['sdp'] != null && offerMap['sdp'].toString().isNotEmpty) {
              await _peerConnection!.setRemoteDescription(
                RTCSessionDescription(offerMap['sdp'], offerMap['type'] ?? 'offer'),
              );

              final answer = await _peerConnection!.createAnswer({
                'offerToReceiveAudio': 1,
                'offerToReceiveVideo': widget.isVideo ? 1 : 0,
              });
              await _peerConnection!.setLocalDescription(answer);

              final answerMap = {'type': answer.type, 'sdp': answer.sdp};
              await ApiService.answerCall(_activeCallId!, widget.friendId, signalData: jsonEncode(answerMap));
            }
          } catch (e) {
            debugPrint('[WebRTC] Receiver polling offer setup error: $e');
          }
        }
      }

      // Cả 2 bên: Nạp ICE candidates nhận được từ đối phương
      if (status['ice_candidates'] != null && status['ice_candidates'] is List) {
        for (var iceItem in status['ice_candidates']) {
          _applyIceCandidate(iceItem);
        }
      }

      // Phát hiện cúp máy từ đối phương
      if (callStatus == 'ended' || callStatus == 'rejected' || callStatus == 'missed') {
        timer.cancel();
        _callEnded = true;
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(callStatus == 'rejected' ? 'Cuộc gọi bị từ chối' : 'Cuộc gọi đã kết thúc')),
          );
          widget.onCallEnded(_secondsElapsed);
          Navigator.of(context).pop();
        }
      }
    });
  }

  void _applyIceCandidate(dynamic iceData) {
    try {
      Map<String, dynamic> map;
      if (iceData is String) {
        map = jsonDecode(iceData);
      } else {
        map = Map<String, dynamic>.from(iceData);
      }

      Map<String, dynamic> candObj = map;
      if (map.containsKey('candidate') && map['candidate'] is Map) {
        candObj = Map<String, dynamic>.from(map['candidate']);
      }

      final candidateStr = candObj['candidate']?.toString();
      if (candidateStr != null && candidateStr.isNotEmpty) {
        final sdpMid = candObj['sdpMid']?.toString();
        final sdpMLineIndex = candObj['sdpMLineIndex'] is int
            ? candObj['sdpMLineIndex'] as int
            : int.tryParse(candObj['sdpMLineIndex']?.toString() ?? '0') ?? 0;

        _peerConnection?.addCandidate(
          RTCIceCandidate(candidateStr, sdpMid, sdpMLineIndex),
        );
      }
    } catch (e) {
      debugPrint('[WebRTC] Candidate parse error: $e');
    }
  }

  void _startDurationTimer() {
    _callTimer?.cancel();
    _callTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() => _secondsElapsed++);
      }
    });
  }

  @override
  void dispose() {
    _statusPollTimer?.cancel();
    _callTimer?.cancel();
    try {
      _localStream?.getTracks().forEach((track) => track.stop());
      _remoteStream?.getTracks().forEach((track) => track.stop());
      _localRenderer.dispose();
      _remoteRenderer.dispose();
      _peerConnection?.close();
    } catch (_) {}
    super.dispose();
  }

  String _formatDuration(int totalSec) {
    final min = (totalSec ~/ 60).toString().padLeft(2, '0');
    final sec = (totalSec % 60).toString().padLeft(2, '0');
    return '$min:$sec';
  }

  void _endCall() async {
    if (!_callEnded && _activeCallId != null) {
      _callEnded = true;
      await ApiService.hangupCall(_activeCallId!, widget.friendId, 'ended');
    }
    if (mounted) {
      widget.onCallEnded(_secondsElapsed);
      Navigator.of(context).pop();
    }
  }

  void _toggleMute() {
    setState(() => _isMuted = !_isMuted);
    _localStream?.getAudioTracks().forEach((track) {
      track.enabled = !_isMuted;
    });
  }

  void _toggleVideo() {
    setState(() => _isVideoOn = !_isVideoOn);
    _localStream?.getVideoTracks().forEach((track) {
      track.enabled = _isVideoOn;
    });
  }

  void _toggleSpeaker() {
    setState(() => _isSpeakerOn = !_isSpeakerOn);
    _localStream?.getAudioTracks().forEach((track) {
      track.enableSpeakerphone(_isSpeakerOn);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      body: SafeArea(
        child: Stack(
          children: [
            // Remote Video Background Stream
            if (widget.isVideo && _remoteStream != null)
              Positioned.fill(
                child: RTCVideoView(
                  _remoteRenderer,
                  objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                ),
              )
            else if (widget.isVideo && _isVideoOn)
              Container(
                width: double.infinity,
                height: double.infinity,
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                ),
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 140,
                        height: 140,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: const Color(0xFF38BDF8), width: 3),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF38BDF8).withValues(alpha: 0.3),
                              blurRadius: 30,
                              spreadRadius: 5,
                            ),
                          ],
                        ),
                        child: CircleAvatar(
                          backgroundColor: const Color(0xFF0284C7),
                          child: Text(
                            widget.friendName.isNotEmpty ? widget.friendName[0].toUpperCase() : '👤',
                            style: const TextStyle(color: Colors.white, fontSize: 50, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

            // Local Camera Stream Floating Preview
            if (widget.isVideo && _isVideoOn && _localStream != null)
              Positioned(
                top: 40,
                right: 16,
                width: 110,
                height: 160,
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.white24, width: 2),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: RTCVideoView(
                      _localRenderer,
                      mirror: true,
                      objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                    ),
                  ),
                ),
              ),

            // Top Header & Controls
            Column(
              children: [
                const SizedBox(height: 30),
                Text(
                  widget.friendName,
                  style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: _isConnected ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _isConnected
                          ? '${widget.isVideo ? "Cuộc gọi video HD" : "Cuộc gọi thoại"} • ${_formatDuration(_secondsElapsed)}'
                          : 'Đang kết nối tín hiệu...',
                      style: const TextStyle(color: Colors.white70, fontSize: 14, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),

                const Spacer(),

                // Control Action Bar
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                  margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B).withValues(alpha: 0.9),
                    borderRadius: BorderRadius.circular(30),
                    border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      // Mute Mic
                      IconButton(
                        style: IconButton.styleFrom(
                          backgroundColor: _isMuted ? Colors.white : Colors.white24,
                          padding: const EdgeInsets.all(12),
                        ),
                        icon: Icon(
                          _isMuted ? Icons.mic_off_rounded : Icons.mic_rounded,
                          color: _isMuted ? Colors.black : Colors.white,
                          size: 22,
                        ),
                        onPressed: _toggleMute,
                      ),

                      // Toggle Camera
                      if (widget.isVideo)
                        IconButton(
                          style: IconButton.styleFrom(
                            backgroundColor: !_isVideoOn ? Colors.white : Colors.white24,
                            padding: const EdgeInsets.all(12),
                          ),
                          icon: Icon(
                            _isVideoOn ? Icons.videocam_rounded : Icons.videocam_off_rounded,
                            color: !_isVideoOn ? Colors.black : Colors.white,
                            size: 22,
                          ),
                          onPressed: _toggleVideo,
                        ),

                      // Speaker
                      IconButton(
                        style: IconButton.styleFrom(
                          backgroundColor: _isSpeakerOn ? const Color(0xFF0284C7) : Colors.white24,
                          padding: const EdgeInsets.all(12),
                        ),
                        icon: Icon(
                          _isSpeakerOn ? Icons.volume_up_rounded : Icons.volume_down_rounded,
                          color: Colors.white,
                          size: 22,
                        ),
                        onPressed: _toggleSpeaker,
                      ),

                      // End Call
                      IconButton(
                        style: IconButton.styleFrom(
                          backgroundColor: const Color(0xFFEF4444),
                          padding: const EdgeInsets.all(12),
                        ),
                        icon: const Icon(Icons.call_end_rounded, color: Colors.white, size: 26),
                        onPressed: _endCall,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
