import React, { useState, useEffect, useRef } from 'react';
import { usePage, router } from '@inertiajs/react';
import axios from 'axios';
import Pusher from 'pusher-js';

export default function SocialHub() {
    const { auth, friends: initialFriends, pendingReceived: initialPendingReceived, pendingSent: initialPendingSent, suggestions: initialSuggestions, myFoodTours = [] } = usePage().props;
    const currentUser = auth.user;

    const [friends, setFriends] = useState(initialFriends || []);
    const [pendingReceived, setPendingReceived] = useState(initialPendingReceived || []);
    const [pendingSent, setPendingSent] = useState(initialPendingSent || []);
    const [suggestions, setSuggestions] = useState(initialSuggestions || []);
    
    const [activeFriend, setActiveFriend] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMessageText, setNewMessageText] = useState('');
    const [isSending, setIsSending] = useState(false);
    const [showTourSelector, setShowTourSelector] = useState(false);
    const [selectedFile, setSelectedFile] = useState(null);
    const [previewUrl, setPreviewUrl] = useState(null);
    const [isUploading, setIsUploading] = useState(false);
    
    // GPS / Nearby states
    const [coords, setCoords] = useState(null);
    const [gpsLoading, setGpsLoading] = useState(false);
    const [nearbyUsers, setNearbyUsers] = useState([]);
    const [nearbyError, setNearbyError] = useState('');
    const [activeTab, setActiveTab] = useState('friends'); // 'friends', 'requests', 'suggestions', 'nearby', 'search'

    // Search states
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const searchDebounceRef = useRef(null);

    // Responsive screen width triggers
    const [isMobile, setIsMobile] = useState(false);
    const [isTablet, setIsTablet] = useState(false);
    const [isNavOpen, setIsNavOpen] = useState(false);
    const [isNavCollapse, setIsNavCollapse] = useState(false);

    useEffect(() => {
        const handleResize = () => {
            const width = window.innerWidth;
            setIsMobile(width < 768);
            setIsTablet(width >= 768 && width < 1024);
            setIsNavCollapse(width <= 1200); // Khớp với Media Query 1200px trong CSS
        };
        handleResize();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    const messagesEndRef = useRef(null);
    const messagesContainerRef = useRef(null);
    const loadMoreTopRef = useRef(null);
    const echoRef = useRef(null);
    const activeFriendRef = useRef(activeFriend);

    // Pagination state
    const [hasMore, setHasMore] = useState(false);
    const [isLoadingMore, setIsLoadingMore] = useState(false);

    // Keep activeFriendRef updated with the latest activeFriend
    useEffect(() => {
        activeFriendRef.current = activeFriend;
    }, [activeFriend]);

    // Scroll chat container to bottom
    const scrollToBottom = (smooth = false) => {
        const container = messagesContainerRef.current;
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    };

    // Scroll to bottom when new messages arrive (sent or received)
    useEffect(() => {
        // Only auto-scroll if we're near the bottom already
        const container = messagesContainerRef.current;
        if (!container) return;
        const distFromBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
        if (distFromBottom < 200) {
            scrollToBottom();
        }
    }, [messages]);

    // IntersectionObserver: auto-load older messages when scrolling to top
    useEffect(() => {
        if (!loadMoreTopRef.current || !hasMore) return;
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && hasMore && !isLoadingMore) {
                    loadOlderMessages();
                }
            },
            { root: messagesContainerRef.current, threshold: 0.1 }
        );
        observer.observe(loadMoreTopRef.current);
        return () => observer.disconnect();
    }, [hasMore, isLoadingMore, messages]);

    // Connect to Laravel Reverb via Pusher Client
    useEffect(() => {
        if (!currentUser) return;

        const isProduction = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';

        // Initialize custom Pusher connection
        const pusher = new Pusher('donganhreverbkey', {
            wsHost: isProduction ? window.location.hostname : '127.0.0.1',
            wsPort: isProduction ? (window.location.protocol === 'https:' ? 443 : 80) : 8090,
            wssPort: isProduction ? (window.location.protocol === 'https:' ? 443 : 80) : 8090,
            forceTLS: isProduction ? window.location.protocol === 'https:' : false,
            enabledTransports: ['ws', 'wss'],
            cluster: 'mt1',
            disableStats: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            }
        });

        // Listen to private user channel
        const channel = pusher.subscribe(`private-chat.${currentUser.id}`);
        
        channel.bind('MessageSent', (data) => {
            const currentActiveFriend = activeFriendRef.current;
            // If the message is from the active chat friend, append it
            if (currentActiveFriend && data.sender_id === currentActiveFriend.id) {
                setMessages(prev => {
                    // Check duplicate
                    if (prev.some(m => m.id === data.id)) return prev;
                    return [...prev, data];
                });
                
                // Mark as read on server
                axios.get(`/social/messages/${currentActiveFriend.id}`);
            } else {
                // Otherwise, show notification/badge or alert
                alert(`🔔 Tin nhắn mới từ ${data.sender.name}: "${data.message}"`);
            }
        });

        // Lắng nghe sự kiện nhận lời mời kết bạn mới
        channel.bind('FriendRequestSent', (data) => {
            setPendingReceived(prev => {
                if (prev.some(r => r.id === data.id)) return prev;
                return [...prev, {
                    id: data.id,
                    user_id: data.user_id,
                    friend_id: data.friend_id,
                    status: data.status,
                    sender: data.sender
                }];
            });
            alert(`🔔 Bạn nhận được lời mời kết bạn mới từ ${data.sender.name}!`);
        });

        // Lắng nghe sự kiện lời mời kết bạn của mình được chấp nhận
        channel.bind('FriendRequestAccepted', (data) => {
            // Xóa khỏi danh sách lời mời đã gửi (pendingSent)
            setPendingSent(prev => prev.filter(r => r.friend_id !== data.friend.id));
            // Thêm vào danh sách bạn bè (friends)
            setFriends(prev => {
                if (prev.some(f => f.id === data.friend.id)) return prev;
                return [...prev, data.friend];
            });
            alert(`🎉 ${data.friend.name} đã chấp nhận lời mời kết bạn của bạn!`);
        });

        echoRef.current = pusher;

        return () => {
            pusher.unsubscribe(`private-chat.${currentUser.id}`);
            pusher.disconnect();
        };
    }, [currentUser]);

    // Fetch message history when active friend changes
    useEffect(() => {
        if (!activeFriend) return;
        setHasMore(false);
        setIsLoadingMore(false);

        axios.get(`/social/messages/${activeFriend.id}`)
            .then(res => {
                const data = res.data;
                setMessages(data.messages || data); // backward compat
                setHasMore(data.has_more || false);
                // Scroll to bottom after initial load
                setTimeout(() => scrollToBottom(), 50);
            })
            .catch(err => {
                console.error("Lỗi tải tin nhắn:", err);
            });
    }, [activeFriend]);

    // Poll friends presence & active chat messages every 4 seconds for real-time Web sync
    useEffect(() => {
        if (!currentUser) return;

        const timer = setInterval(() => {
            // 1. Refresh friends list & online status
            axios.get('/api/v1/friends')
                .then(res => {
                    const freshFriends = res.data;
                    if (Array.isArray(freshFriends) && freshFriends.length > 0) {
                        setFriends(freshFriends);
                        if (activeFriendRef.current) {
                            const updatedActive = freshFriends.find(f => f.id === activeFriendRef.current.id);
                            if (updatedActive) {
                                setActiveFriend(prev => prev ? { ...prev, is_online: updatedActive.is_online } : prev);
                            }
                        }
                    }
                })
                .catch(() => {});

            // 2. Refresh active chat messages
            const currentActive = activeFriendRef.current;
            if (currentActive) {
                axios.get(`/social/messages/${currentActive.id}`)
                    .then(res => {
                        const data = res.data;
                        const newMsgs = data.messages || data;
                        setMessages(prev => {
                            if (newMsgs.length !== prev.length) {
                                return newMsgs;
                            }
                            return prev;
                        });
                    })
                    .catch(() => {});
            }
        }, 4000);

        return () => clearInterval(timer);
    }, [currentUser]);

    // Load older messages (infinite scroll up)
    const loadOlderMessages = () => {
        if (!activeFriend || isLoadingMore || !hasMore) return;
        const oldestId = messages[0]?.id;
        if (!oldestId) return;

        setIsLoadingMore(true);
        const container = messagesContainerRef.current;
        const prevScrollHeight = container ? container.scrollHeight : 0;

        axios.get(`/social/messages/${activeFriend.id}`, { params: { before_id: oldestId } })
            .then(res => {
                const data = res.data;
                const older = data.messages || [];
                setHasMore(data.has_more || false);
                setMessages(prev => [...older, ...prev]);
                // Restore scroll position so view doesn't jump
                setTimeout(() => {
                    if (container) {
                        container.scrollTop = container.scrollHeight - prevScrollHeight;
                    }
                }, 0);
            })
            .catch(err => console.error("Lỗi tải tin cũ:", err))
            .finally(() => setIsLoadingMore(false));
    };

    // Periodically update active presence status of friends
    useEffect(() => {
        const checkPresence = () => {
            axios.get('/social/friends/presence')
                .then(res => {
                    const onlineIds = res.data.online_ids || [];
                    setFriends(prevFriends => 
                        prevFriends.map(f => ({
                            ...f,
                            is_online: onlineIds.includes(f.id)
                        }))
                    );
                    setActiveFriend(prevActive => {
                        if (!prevActive) return null;
                        return {
                            ...prevActive,
                            is_online: onlineIds.includes(prevActive.id)
                        };
                    });
                })
                .catch(err => {
                    console.warn('Presence status update skipped:', err);
                });
        };
        checkPresence();
        const interval = setInterval(checkPresence, 45000); // 45s
        return () => clearInterval(interval);
    }, []);

    // Auto-select active friend on mount/load if chat_with query param is present
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const chatWithId = params.get('chat_with');
        if (chatWithId && friends.length > 0) {
            const friend = friends.find(f => f.id === parseInt(chatWithId));
            if (friend) {
                setActiveFriend(friend);
                // Clear the query parameter from URL bar to prevent re-opening on page refresh
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        }
    }, [friends]);

    // Handle sending a new message
    const handleSendMessage = async (e) => {
        e.preventDefault();
        const hasText = !!newMessageText.trim();
        if (!hasText && !selectedFile) return;
        if (!activeFriend || isSending || isUploading) return;

        setIsSending(true);
        const tempText = newMessageText;
        setNewMessageText('');

        try {
            let mediaPath = null;
            let mediaType = null;

            if (selectedFile) {
                setIsUploading(true);
                const formData = new FormData();
                formData.append('files[]', selectedFile);

                const uploadRes = await axios.post('/api/v1/upload', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                if (uploadRes.data?.success && uploadRes.data?.files?.length > 0) {
                    const uploadedFile = uploadRes.data.files[0];
                    mediaPath = uploadedFile.url;
                    mediaType = uploadedFile.file_type; // 'image' hoặc 'video'
                } else {
                    throw new Error("Tải lên tệp thất bại.");
                }
                setIsUploading(false);
            }

            const res = await axios.post('/social/messages', {
                receiver_id: activeFriend.id,
                message: tempText,
                media_path: mediaPath,
                media_type: mediaType
            });

            if (res.data.status === 'success') {
                setMessages(prev => [...prev, res.data.message]);
                handleClearFile();
            }
        } catch (err) {
            alert(err.response?.data?.message || err.message || "Không thể gửi tin nhắn.");
            setNewMessageText(tempText); // restore text if failed
        } finally {
            setIsSending(false);
            setIsUploading(false);
        }
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        // Check size limit: 500MB
        if (file.size > 500 * 1024 * 1024) {
            alert("Kích thước tệp không được vượt quá 500MB.");
            return;
        }

        setSelectedFile(file);
        setPreviewUrl(URL.createObjectURL(file));
    };

    const handleClearFile = () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        setSelectedFile(null);
        setPreviewUrl(null);
    };

    // Handle sharing a custom food tour in the chat
    const handleShareTour = (tour) => {
        if (!activeFriend || isSending) return;
        setIsSending(true);
        setShowTourSelector(false);

        axios.post('/social/messages', {
            receiver_id: activeFriend.id,
            message: `Đã chia sẻ lộ trình: ${tour.name}`,
            food_tour_id: tour.id
        })
        .then(res => {
            if (res.data.status === 'success') {
                setMessages(prev => [...prev, res.data.message]);
            }
        })
        .catch(err => {
            alert(err.response?.data?.message || "Không thể chia sẻ lộ trình.");
        })
        .finally(() => {
            setIsSending(false);
        });
    };

    // Get current GPS coords and update on server
    const shareLocation = () => {
        if (!navigator.geolocation) {
            setNearbyError("Trình duyệt không hỗ trợ định vị GPS.");
            return;
        }

        setGpsLoading(true);
        setNearbyError('');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                setCoords({ latitude: lat, longitude: lon });

                axios.post('/social/location', {
                    latitude: lat,
                    longitude: lon
                })
                .then(res => {
                    if (res.data.status === 'success') {
                        setNearbyUsers(res.data.nearby);
                        setActiveTab('nearby');
                    }
                })
                .catch(err => {
                    setNearbyError("Lỗi đồng bộ vị trí với máy chủ.");
                })
                .finally(() => {
                    setGpsLoading(false);
                });
            },
            (error) => {
                setGpsLoading(false);
                setNearbyError("Không thể lấy tọa độ GPS. Hãy cấp quyền truy cập vị trí.");
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    };

    // Friend Request Actions
    const sendFriendRequest = (friendId) => {
        router.post('/social/friends', { friend_id: friendId }, {
            preserveScroll: true,
            onSuccess: () => {
                // Update lists
                alert("Đã gửi lời mời kết bạn!");
            }
        });
    };

    const acceptFriendRequest = (friendshipId) => {
        router.post(`/social/friends/${friendshipId}/accept`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh data
            }
        });
    };

    const declineFriendRequest = (friendshipId) => {
        router.post(`/social/friends/${friendshipId}/decline`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh data
            }
        });
    };

    // Debounced friend search
    const handleSearchChange = (e) => {
        const q = e.target.value;
        setSearchQuery(q);

        if (searchDebounceRef.current) clearTimeout(searchDebounceRef.current);

        if (q.trim().length < 2) {
            setSearchResults([]);
            setIsSearching(false);
            if (q.trim().length === 0) setActiveTab('friends');
            return;
        }

        setIsSearching(true);
        setActiveTab('search');

        searchDebounceRef.current = setTimeout(() => {
            axios.get('/social/search', { params: { q: q.trim() } })
                .then(res => { setSearchResults(res.data); })
                .catch(() => {})
                .finally(() => setIsSearching(false));
        }, 350);
    };

    const clearSearch = () => {
        setSearchQuery('');
        setSearchResults([]);
        setActiveTab('friends');
    };

    const [dropdownOpen, setDropdownOpen] = useState(false);
    const [isGuideOpen, setIsGuideOpen] = useState(false);

    useEffect(() => {
        window.openGuideModal = (e) => {
            if (e) e.preventDefault();
            setIsGuideOpen(true);
        };
        window.closeGuideModal = () => {
            setIsGuideOpen(false);
        };
        return () => {
            delete window.openGuideModal;
            delete window.closeGuideModal;
        };
    }, []);

    return (
        <div style={{ display: 'flex', flexDirection: 'column', height: '100vh', overflow: 'hidden', background: 'var(--bg-base)', color: '#f4f4f5' }}>
            <style>{`
                .header-action-btn {
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.08);
                    border: none;
                    color: var(--text-main, #f4f4f5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    position: relative;
                    transition: background 0.2s, transform 0.1s;
                }
                .header-action-btn:hover {
                    background: rgba(255, 255, 255, 0.15);
                }
                .header-action-btn:active {
                    transform: scale(0.95);
                }
                .header-action-btn .badge {
                    position: absolute;
                    top: -4px;
                    right: -4px;
                    background: #ef4444;
                    color: #ffffff;
                    font-size: 0.7rem;
                    font-weight: 800;
                    min-width: 16px;
                    height: 16px;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0 4px;
                    border: 2px solid #18181b;
                }
                .profile-avatar-container {
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    overflow: hidden;
                    border: 2px solid rgba(59, 130, 246, 0.2);
                    transition: border-color 0.2s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: rgba(255,255,255,0.05);
                }
                .profile-trigger-btn {
                    padding: 0 !important;
                    background: transparent !important;
                    border: none !important;
                    box-shadow: none !important;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    position: relative;
                }
                .profile-trigger-btn:hover .profile-avatar-container {
                    border-color: #3b82f6;
                }
                .profile-chevron-badge {
                    position: absolute;
                    bottom: -3px;
                    right: -3px;
                    width: 15px;
                    height: 15px;
                    border-radius: 50%;
                    background: #3a3b3c;
                    border: 2px solid #18181b;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #b0b3b8;
                }
            `}</style>
            
            {/* Sticky Glass Navigation Header */}
            <header className="glass-nav">
                <div className="container nav-wrapper">
                    <a href="/" className="logo">
                        <span>🗺️</span> DongAnh Map Discovery
                    </a>
                    
                    <button 
                        className={`mobile-menu-btn ${isNavOpen ? 'open' : ''}`} 
                        onClick={() => setIsNavOpen(!isNavOpen)} 
                        aria-label="Toggle navigation"
                        aria-expanded={isNavOpen}
                        style={{ display: isNavCollapse ? 'flex' : 'none' }}
                    >
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <div className={`nav-collapse main-nav-container ${(!isNavCollapse || isNavOpen) ? 'show' : ''}`} id="navCollapse">
                        <nav>
                            <ul className="nav-menu">
                                <li><a href="/" className="nav-link">Trang chủ</a></li>
                                <li><a href="/tim-kiem" className="nav-link">Bản đồ & Tìm kiếm</a></li>
                                <li><a href="/food-tours" className="nav-link">Food Tour</a></li>
                                <li><a href="/exp-corner" className="nav-link">Góc trải nghiệm thực tế</a></li>
                                <li><a href="/checkin" className="nav-link">Góc Check-in</a></li>
                                <li><a href="/social" className="nav-link active">💬 Kết nối bạn bè</a></li>
                                <li>
                                    <a 
                                        href="#" 
                                        onClick={(e) => { e.preventDefault(); setIsGuideOpen(true); }} 
                                        className="nav-link"
                                    >
                                        Giới thiệu & Hướng dẫn
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    
                        <div className="user-actions" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                            {currentUser && (
                                <>
                                    {/* Nút Chat */}
                                    <button 
                                        onClick={() => {
                                            setActiveTab('friends');
                                            setActiveFriend(null);
                                        }} 
                                        className="header-action-btn"
                                        title="Tin nhắn"
                                    >
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                    </button>

                                    {/* Nút Yêu cầu kết bạn (Thông báo) */}
                                    <button 
                                        onClick={() => setActiveTab('requests')} 
                                        className="header-action-btn"
                                        title="Yêu cầu kết bạn"
                                    >
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                        </svg>
                                        {pendingReceived.length > 0 && (
                                            <span className="badge">{pendingReceived.length}</span>
                                        )}
                                    </button>
                                </>
                            )}

                            {currentUser ? (
                                <div className="profile-dropdown" style={{ position: 'relative' }}>
                                    <button onClick={() => setDropdownOpen(!dropdownOpen)} className="profile-trigger-btn">
                                        <div className="profile-avatar-container">
                                            {currentUser.avatar_url ? (
                                                <img 
                                                    src={currentUser.avatar_url} 
                                                    alt="avatar" 
                                                    style={{ width: '100%', height: '100%', objectFit: 'cover' }} 
                                                />
                                            ) : (
                                                <span style={{ fontSize: '1.2rem' }}>{currentUser.avatar || '👤'}</span>
                                            )}
                                        </div>
                                        <div className="profile-chevron-badge">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                                                <path d="M6 9l6 6 6-6"></path>
                                            </svg>
                                        </div>
                                    </button>
                                    {dropdownOpen && (
                                        <div className="profile-dropdown-menu" style={{ display: 'block', position: 'absolute', right: 0, top: '100%', zIndex: 1010 }}>
                                            <div className="user-info-header">
                                                <div className="user-name">{currentUser.name}</div>
                                                <div className="user-role">
                                                    {currentUser.role === 'admin' && '🏛️ Quản trị viên'}
                                                    {currentUser.role === 'seller' && '🏪 Chủ cơ sở kinh doanh'}
                                                    {currentUser.role === 'user' && '👤 Thành viên cộng đồng'}
                                                </div>
                                            </div>
                                            
                                            {(currentUser.role === 'admin' || currentUser.role === 'seller') && (
                                                <a href="/admin/dashboard" className="dropdown-item">
                                                    <span>📊</span> Trang quản lý
                                                </a>
                                            )}
                                            
                                            <a href="/profile" className="dropdown-item">
                                                <span>👤</span> Trang cá nhân
                                            </a>
                                            
                                            <form action="/auth/logout" method="POST" style={{ margin: 0, width: '100%' }}>
                                                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                                                <button type="submit" className="dropdown-item dropdown-item-logout" style={{ background: 'none', border: 'none', width: '100%', textAlign: 'left', cursor: 'pointer' }}>
                                                    <span>🚪</span> Đăng xuất
                                                </button>
                                            </form>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <>
                                    <a href="/auth/login" className="btn-secondary" style={{ textDecoration: 'none', padding: '6px 14px', fontSize: '0.85rem', borderRadius: '8px' }}>Đăng nhập</a>
                                    <a href="/auth/register" className="btn-primary" style={{ textDecoration: 'none', padding: '6px 14px', fontSize: '0.85rem', borderRadius: '8px' }}>Đăng ký</a>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </header>

            {/* Main Application Container */}
            <div style={{ 
                display: 'flex', 
                flex: 1, 
                maxWidth: '1200px', 
                margin: isMobile ? '0 auto' : '24px auto', 
                width: '100%', 
                gap: isMobile ? '0' : '20px', 
                padding: isMobile ? '0' : '0 20px', 
                height: isMobile ? 'calc(100vh - 64px)' : 'calc(100vh - 120px)',
                boxSizing: 'border-box'
            }}>
                
                {/* LEFT SIDE PANEL (Friends list, requests, suggestions) */}
                {(!isMobile || !activeFriend) && (
                    <div className="glass-panel" style={{ 
                        width: isMobile ? '100%' : (isTablet ? '280px' : '380px'), 
                        display: 'flex', 
                        flexDirection: 'column', 
                        padding: isMobile ? '12px' : '20px', 
                        overflowY: 'auto',
                        height: '100%',
                        boxSizing: 'border-box',
                        borderRadius: isMobile ? '0' : '16px',
                        border: isMobile ? 'none' : '1px solid var(--border-glow)'
                    }}>
                    
                    {/* Friend Search Box */}
                    <div style={{ marginBottom: '16px' }}>
                        <div style={{ position: 'relative' }}>
                            <span style={{ position: 'absolute', left: '12px', top: '50%', transform: 'translateY(-50%)', fontSize: '1rem', color: 'var(--text-muted)', pointerEvents: 'none' }}>🔍</span>
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={handleSearchChange}
                                placeholder="Tìm kiếm bạn bè theo tên..."
                                style={{ width: '100%', padding: '10px 36px 10px 36px', background: 'var(--bg-surface)', border: '1.5px solid var(--border-glow)', borderRadius: '12px', color: 'var(--text-main)', fontSize: '0.88rem', outline: 'none', boxSizing: 'border-box', transition: 'border-color 0.2s' }}
                                onFocus={e => e.target.style.borderColor = 'var(--primary)'}
                                onBlur={e => e.target.style.borderColor = 'var(--border-glow)'}
                            />
                            {searchQuery && (
                                <button onClick={clearSearch} style={{ position: 'absolute', right: '10px', top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-muted)', fontSize: '1rem', padding: '2px' }}>✕</button>
                            )}
                        </div>
                    </div>

                    {/* GPS Location & Search Trigger */}
                    <div style={{ marginBottom: '20px', borderBottom: '1px dashed var(--border-glow)', paddingBottom: '20px' }}>
                        <h3 style={{ fontSize: '1rem', fontWeight: 800, margin: '0 0 12px 0', color: 'var(--text-main)', display: 'flex', alignItems: 'center', gap: '8px' }}>
                            <span>🛰️</span> Khám phá & Định vị GPS
                        </h3>
                        <button 
                            onClick={shareLocation}
                            disabled={gpsLoading}
                            style={{ width: '100%', padding: '10px', background: 'var(--primary-grad)', border: 'none', color: '#fff', fontWeight: 700, borderRadius: '12px', cursor: 'pointer', transition: 'all 0.2s', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px' }}
                        >
                            {gpsLoading ? '⏳ Đang quét tọa độ...' : '📍 Chia sẻ vị trí & Tìm bạn ở gần'}
                        </button>
                        {coords && (
                            <div style={{ marginTop: '8px', fontSize: '0.75rem', color: '#10b981', textAlign: 'center' }}>
                                Tọa độ: {coords.latitude.toFixed(5)}, {coords.longitude.toFixed(5)}
                            </div>
                        )}
                        {nearbyError && (
                            <div style={{ marginTop: '8px', fontSize: '0.78rem', color: '#ef4444', textAlign: 'center' }}>
                                ⚠️ {nearbyError}
                            </div>
                        )}
                    </div>

                    {/* Tab Navigation */}
                    <div style={{ display: 'flex', gap: '6px', marginBottom: '16px', background: 'rgba(var(--primary-rgb),0.06)', padding: '4px', borderRadius: '12px' }}>
                        <button 
                            onClick={() => setActiveTab('friends')}
                            style={{ flex: 1, padding: '8px', border: 'none', borderRadius: '8px', fontSize: '0.8rem', fontWeight: 700, background: activeTab === 'friends' ? 'rgba(var(--primary-rgb),0.12)' : 'transparent', color: activeTab === 'friends' ? 'var(--primary)' : 'var(--text-muted)', cursor: 'pointer' }}
                        >
                            Bạn bè
                        </button>
                        <button 
                            onClick={() => setActiveTab('requests')}
                            style={{ flex: 1, padding: '8px', border: 'none', borderRadius: '8px', fontSize: '0.8rem', fontWeight: 700, background: activeTab === 'requests' ? 'rgba(var(--primary-rgb),0.12)' : 'transparent', color: activeTab === 'requests' ? 'var(--primary)' : 'var(--text-muted)', cursor: 'pointer', position: 'relative' }}
                        >
                            Yêu cầu
                            {pendingReceived.length > 0 && (
                                <span style={{ position: 'absolute', top: '-4px', right: '-4px', background: '#ef4444', color: '#fff', fontSize: '0.65rem', padding: '2px 6px', borderRadius: '10px', fontWeight: 900 }}>
                                    {pendingReceived.length}
                                </span>
                            )}
                        </button>
                        <button 
                            onClick={() => setActiveTab('suggestions')}
                            style={{ flex: 1, padding: '8px', border: 'none', borderRadius: '8px', fontSize: '0.8rem', fontWeight: 700, background: activeTab === 'suggestions' ? 'rgba(var(--primary-rgb),0.12)' : 'transparent', color: activeTab === 'suggestions' ? 'var(--primary)' : 'var(--text-muted)', cursor: 'pointer' }}
                        >
                            Gợi ý
                        </button>
                        <button 
                            onClick={() => setActiveTab('nearby')}
                            style={{ flex: 1, padding: '8px', border: 'none', borderRadius: '8px', fontSize: '0.8rem', fontWeight: 700, background: activeTab === 'nearby' ? 'rgba(var(--primary-rgb),0.12)' : 'transparent', color: activeTab === 'nearby' ? 'var(--primary)' : 'var(--text-muted)', cursor: 'pointer' }}
                        >
                            Ở gần
                        </button>
                    </div>

                    {/* Tab contents */}
                    <div style={{ flex: 1, overflowY: 'auto' }}>

                        {/* 0. SEARCH RESULTS tab */}
                        {activeTab === 'search' && (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                {isSearching ? (
                                    <div style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)', fontSize: '0.88rem' }}>
                                        <span style={{ display: 'block', fontSize: '1.5rem', marginBottom: '8px' }}>⏳</span>
                                        Đang tìm kiếm...
                                    </div>
                                ) : searchResults.length === 0 ? (
                                    <div style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)', fontSize: '0.88rem' }}>
                                        <span style={{ display: 'block', fontSize: '2rem', marginBottom: '8px' }}>🔍</span>
                                        Không tìm thấy người dùng nào.
                                    </div>
                                ) : (
                                    searchResults.map(user => (
                                        <div key={user.id} style={{ display: 'flex', alignItems: 'center', gap: '12px', padding: '12px', background: 'var(--bg-surface)', borderRadius: '12px', border: '1px solid var(--border-glow)' }}>
                                            {/* Avatar */}
                                            <div style={{ width: '42px', height: '42px', borderRadius: '50%', overflow: 'hidden', flexShrink: 0, background: 'rgba(var(--primary-rgb),0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1.3rem' }}>
                                                {user.avatar_url
                                                    ? <img src={user.avatar_url} alt={user.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                                    : <span>{user.avatar || '👤'}</span>
                                                }
                                            </div>
                                            {/* Name */}
                                            <div style={{ flex: 1, minWidth: 0 }}>
                                                <div style={{ fontSize: '0.9rem', fontWeight: 700, color: 'var(--text-main)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{user.name}</div>
                                                <div style={{ fontSize: '0.72rem', color: 'var(--text-muted)' }}>Thành viên cộng đồng</div>
                                            </div>
                                            {/* Action */}
                                            <div style={{ flexShrink: 0 }}>
                                                {user.friendship_status === 'none' && (
                                                    <button
                                                        onClick={() => sendFriendRequest(user.id)}
                                                        style={{ background: 'rgba(var(--primary-rgb),0.12)', border: 'none', color: 'var(--primary)', fontSize: '0.75rem', padding: '6px 10px', borderRadius: '8px', cursor: 'pointer', fontWeight: 700, whiteSpace: 'nowrap' }}
                                                    >
                                                        + Kết bạn
                                                    </button>
                                                )}
                                                {user.friendship_status === 'pending' && user.is_sender && (
                                                    <span style={{ fontSize: '0.72rem', color: 'var(--text-muted)', fontStyle: 'italic' }}>Đang chờ</span>
                                                )}
                                                {user.friendship_status === 'pending' && !user.is_sender && (
                                                    <button
                                                        onClick={() => acceptFriendRequest(user.friendship_id)}
                                                        style={{ background: '#10b981', border: 'none', color: '#fff', fontSize: '0.72rem', padding: '5px 8px', borderRadius: '6px', cursor: 'pointer', fontWeight: 700 }}
                                                    >
                                                        ✓ Đồng ý
                                                    </button>
                                                )}
                                                {user.friendship_status === 'accepted' && (
                                                    <span style={{ fontSize: '0.72rem', color: '#10b981', fontWeight: 700 }}>✓ Bạn bè</span>
                                                )}
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}

                        {/* 1. Friends list tab */}
                        {activeTab === 'friends' && (
                            <div>
                                {friends.length === 0 ? (
                                    <div style={{ textAlign: 'center', padding: '40px 10px', color: '#71717a', fontSize: '0.88rem' }}>
                                        Chưa có người bạn nào. Hãy chia sẻ GPS vị trí hoặc tìm bạn mới!
                                    </div>
                                ) : (
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                        {friends.map(friend => (
                                            <div 
                                                key={friend.id}
                                                onClick={() => setActiveFriend(friend)}
                                                style={{ display: 'flex', alignItems: 'center', gap: '12px', padding: '12px', borderRadius: '12px', background: activeFriend?.id === friend.id ? 'rgba(var(--primary-rgb),0.1)' : 'var(--bg-surface)', border: activeFriend?.id === friend.id ? '1px solid rgba(var(--primary-rgb),0.3)' : '1px solid var(--border-glow)', cursor: 'pointer', transition: 'all 0.2s' }}
                                            >
                                                <div style={{ width: '40px', height: '40px', background: 'rgba(var(--primary-rgb),0.1)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1.2rem' }}>
                                                    {friend.avatar || '👤'}
                                                </div>
                                                <div style={{ flex: 1 }}>
                                                    <div style={{ fontSize: '0.9rem', fontWeight: 700, color: 'var(--text-main)' }}>{friend.name}</div>
                                                    <div style={{ fontSize: '0.72rem', color: 'var(--text-muted)' }}>{friend.is_online ? 'Đang hoạt động' : 'Ngoại tuyến'}</div>
                                                </div>
                                                <div style={{ width: '8px', height: '8px', background: friend.is_online ? '#10b981' : '#a1a1aa', borderRadius: '50%', boxShadow: friend.is_online ? '0 0 6px #10b981' : 'none' }} title={friend.is_online ? 'Đang hoạt động' : 'Ngoại tuyến'}></div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* 2. Friend Requests received / sent */}
                        {activeTab === 'requests' && (
                            <div>
                                <h4 style={{ fontSize: '0.8rem', textTransform: 'uppercase', color: 'var(--text-muted)', letterSpacing: '0.05em', margin: '0 0 10px 0' }}>Lời mời đã nhận</h4>
                                {pendingReceived.length === 0 ? (
                                    <div style={{ fontSize: '0.82rem', color: 'var(--text-muted)', marginBottom: '20px' }}>Không có yêu cầu kết bạn nào.</div>
                                ) : (
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', marginBottom: '20px' }}>
                                        {pendingReceived.map(req => (
                                            <div key={req.id} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px', background: 'var(--bg-surface)', borderRadius: '12px', border: '1px solid var(--border-glow)' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flex: 1 }}>
                                                    <span style={{ fontSize: '1.2rem' }}>{req.sender.avatar || '👤'}</span>
                                                    <span style={{ fontSize: '0.85rem', fontWeight: 700, color: 'var(--text-main)' }}>{req.sender.name}</span>
                                                </div>
                                                <div style={{ display: 'flex', gap: '6px' }}>
                                                    <button onClick={() => acceptFriendRequest(req.id)} style={{ background: '#10b981', border: 'none', color: '#fff', fontSize: '0.75rem', padding: '4px 8px', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }}>✓</button>
                                                    <button onClick={() => declineFriendRequest(req.id)} style={{ background: '#ef4444', border: 'none', color: '#fff', fontSize: '0.75rem', padding: '4px 8px', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }}>✕</button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <h4 style={{ fontSize: '0.8rem', textTransform: 'uppercase', color: 'var(--text-muted)', letterSpacing: '0.05em', margin: '0 0 10px 0' }}>Lời mời đã gửi</h4>
                                {pendingSent.length === 0 ? (
                                    <div style={{ fontSize: '0.82rem', color: 'var(--text-muted)' }}>Không có lời mời nào đang chờ.</div>
                                ) : (
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                        {pendingSent.map(req => (
                                            <div key={req.id} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px', background: 'var(--bg-surface)', borderRadius: '12px', border: '1px solid var(--border-glow)' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flex: 1 }}>
                                                    <span style={{ fontSize: '1.2rem' }}>{req.receiver.avatar || '👤'}</span>
                                                    <span style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--text-main)' }}>{req.receiver.name}</span>
                                                </div>
                                                <button onClick={() => declineFriendRequest(req.id)} style={{ background: 'rgba(239,68,68,0.15)', border: 'none', color: '#ef4444', fontSize: '0.72rem', padding: '4px 8px', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }}>Hủy</button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* 3. Suggestions Tab */}
                        {activeTab === 'suggestions' && (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                {suggestions.length === 0 ? (
                                    <div style={{ fontSize: '0.82rem', color: 'var(--text-muted)', textAlign: 'center', padding: '20px' }}>Không có gợi ý mới nào.</div>
                                ) : (
                                    suggestions.map(user => (
                                        <div key={user.id} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px', background: 'var(--bg-surface)', borderRadius: '12px', border: '1px solid var(--border-glow)' }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                                <span style={{ fontSize: '1.2rem' }}>{user.avatar || '👤'}</span>
                                                <span style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--text-main)' }}>{user.name}</span>
                                            </div>
                                            <button 
                                                onClick={() => sendFriendRequest(user.id)}
                                                style={{ background: 'rgba(14,165,233,0.15)', border: 'none', color: 'var(--primary)', fontSize: '0.75rem', padding: '6px 10px', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }}
                                            >
                                                Kết bạn
                                            </button>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}

                        {/* 4. Nearby Users Tab */}
                        {activeTab === 'nearby' && (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                {nearbyUsers.length === 0 ? (
                                    <div style={{ fontSize: '0.82rem', color: 'var(--text-muted)', textAlign: 'center', padding: '20px' }}>
                                        Không tìm thấy ai trong bán kính 10km. Hãy thử chia sẻ lại GPS!
                                    </div>
                                ) : (
                                    nearbyUsers.map(user => (
                                        <div key={user.id} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px', background: 'var(--bg-surface)', borderRadius: '12px', border: '1px solid var(--border-glow)' }}>
                                            <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                    <span style={{ fontSize: '1.1rem' }}>{user.avatar}</span>
                                                    <span style={{ fontSize: '0.85rem', fontWeight: 700, color: 'var(--text-main)' }}>{user.name}</span>
                                                </div>
                                                <div style={{ fontSize: '0.7rem', color: '#10b981', fontWeight: 600 }}>
                                                    📍 Cách {user.distance} km • {user.last_active}
                                                </div>
                                            </div>

                                            <div>
                                                {user.friendship_status === 'none' && (
                                                    <button 
                                                        onClick={() => sendFriendRequest(user.id)}
                                                        style={{ background: 'rgba(14,165,233,0.15)', border: 'none', color: 'var(--primary)', fontSize: '0.75rem', padding: '6px 10px', borderRadius: '8px', cursor: 'pointer', fontWeight: 'bold' }}
                                                    >
                                                        Kết bạn
                                                    </button>
                                                )}
                                                {user.friendship_status === 'pending' && user.is_sender && (
                                                    <span style={{ fontSize: '0.72rem', color: '#71717a', fontStyle: 'italic' }}>Đang chờ</span>
                                                )}
                                                {user.friendship_status === 'pending' && !user.is_sender && (
                                                    <button 
                                                        onClick={() => acceptFriendRequest(user.friendship_id)}
                                                        style={{ background: '#10b981', border: 'none', color: '#fff', fontSize: '0.72rem', padding: '4px 8px', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }}
                                                    >
                                                        Đồng ý
                                                    </button>
                                                )}
                                                {user.friendship_status === 'accepted' && (
                                                    <span style={{ fontSize: '0.75rem', color: '#10b981', fontWeight: 'bold' }}>✓ Bạn bè</span>
                                                )}
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}

                    </div>

                </div>
                )}

                {/* RIGHT CHAT WINDOW PANEL */}
                {(!isMobile || activeFriend) && (
                    <div className="glass-panel" style={{ 
                        flex: 1, 
                        display: 'flex', 
                        flexDirection: 'column', 
                        overflow: 'hidden',
                        height: '100%',
                        borderRadius: isMobile ? '0' : '16px',
                        border: isMobile ? 'none' : '1px solid var(--border-glow)'
                    }}>
                    
                    {activeFriend ? (
                        <>
                            {/* Chat Header */}
                            <div style={{ padding: isMobile ? '12px 16px' : '16px 20px', borderBottom: '1px solid var(--border-glow)', background: 'var(--bg-surface)', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: isMobile ? '8px' : '12px' }}>
                                    {isMobile && (
                                        <button 
                                            onClick={() => setActiveFriend(null)} 
                                            style={{ background: 'transparent', border: 'none', color: 'var(--text-main)', fontSize: '1.2rem', padding: '0 4px 0 0', cursor: 'pointer', display: 'flex', alignItems: 'center' }}
                                            title="Quay lại danh sách"
                                        >
                                            ⬅️
                                        </button>
                                    )}
                                    <div style={{ width: isMobile ? '36px' : '42px', height: isMobile ? '36px' : '42px', background: 'rgba(var(--primary-rgb),0.1)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: isMobile ? '1.1rem' : '1.3rem' }}>
                                        {activeFriend.avatar || '👤'}
                                    </div>
                                    <div>
                                        <h3 style={{ fontSize: '0.95rem', fontWeight: 800, margin: 0, color: 'var(--text-main)' }}>{activeFriend.name}</h3>
                                        <span style={{ fontSize: '0.72rem', color: activeFriend.is_online ? '#10b981' : '#71717a', display: 'flex', alignItems: 'center', gap: '4px' }}>
                                            <span style={{ width: '6px', height: '6px', background: activeFriend.is_online ? '#10b981' : '#a1a1aa', borderRadius: '50%', boxShadow: activeFriend.is_online ? '0 0 6px #10b981' : 'none' }}></span> {activeFriend.is_online ? 'Đang hoạt động' : 'Ngoại tuyến'}
                                        </span>
                                    </div>
                                </div>
                                <button 
                                    onClick={() => setActiveFriend(null)}
                                    style={{ background: 'transparent', border: 'none', color: '#a1a1aa', fontSize: '1.2rem', cursor: 'pointer' }}
                                >
                                    ✕
                                </button>
                            </div>

                            {/* Chat Messages Body */}
                            <div
                                ref={messagesContainerRef}
                                style={{ flex: 1, minHeight: 0, padding: '20px', overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '12px', background: 'var(--bg-base)' }}
                            >
                                {/* Load more older messages trigger */}
                                {hasMore && (
                                    <div ref={loadMoreTopRef} style={{ display: 'flex', justifyContent: 'center', padding: '8px 0' }}>
                                        {isLoadingMore ? (
                                            <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                <span style={{ display: 'inline-block', width: '14px', height: '14px', border: '2px solid var(--primary)', borderTopColor: 'transparent', borderRadius: '50%', animation: 'spin 0.7s linear infinite' }}></span>
                                                Đang tải tin cũ hơn...
                                            </span>
                                        ) : (
                                            <button
                                                onClick={loadOlderMessages}
                                                style={{ fontSize: '0.75rem', color: 'var(--primary)', background: 'rgba(var(--primary-rgb),0.08)', border: '1px solid rgba(var(--primary-rgb),0.2)', borderRadius: '20px', padding: '4px 14px', cursor: 'pointer' }}
                                            >
                                                ↑ Tải tin nhắn cũ hơn
                                            </button>
                                        )}
                                    </div>
                                )}

                                {/* Spacer: đẩy tin nhắn xuống đáy khi ít, tự co khi nhiều để scroll hoạt động */}
                                <div style={{ flex: 1, minHeight: 0 }} />
                                {messages.length === 0 ? (
                                    <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)', gap: '8px' }}>
                                        <span style={{ fontSize: '2.5rem' }}>💬</span>
                                        <div style={{ fontSize: '0.85rem' }}>Hãy gửi lời chào đầu tiên đến {activeFriend.name}!</div>
                                    </div>
                                ) : (
                                    messages.map((msg) => {
                                        const isSelf = msg.sender_id === currentUser.id;
                                        return (
                                            <div 
                                                key={msg.id}
                                                style={{ display: 'flex', justifyContent: isSelf ? 'flex-end' : 'flex-start' }}
                                            >
                                                <div style={{ display: 'flex', flexDirection: 'column', alignItems: isSelf ? 'flex-end' : 'flex-start', maxWidth: '70%' }}>
                                                    {msg.food_tour ? (
                                                        <div style={{
                                                            background: 'var(--bg-card)',
                                                            border: '1.5px solid var(--border-glow)',
                                                            borderRadius: '16px',
                                                            overflow: 'hidden',
                                                            width: '280px',
                                                            boxShadow: '0 8px 20px rgba(0, 0, 0, 0.25)',
                                                            display: 'flex',
                                                            flexDirection: 'column',
                                                            textAlign: 'left'
                                                        }}>
                                                            <div style={{ position: 'relative' }}>
                                                                <img 
                                                                    src={msg.food_tour.thumbnail || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=400&q=80'} 
                                                                    alt={msg.food_tour.name}
                                                                    style={{ width: '100%', height: '120px', objectFit: 'cover' }}
                                                                />
                                                                <div style={{
                                                                    position: 'absolute',
                                                                    top: '8px',
                                                                    right: '8px',
                                                                    background: 'rgba(0,0,0,0.6)',
                                                                    backdropFilter: 'blur(4px)',
                                                                    padding: '3px 8px',
                                                                    borderRadius: '20px',
                                                                    fontSize: '0.65rem',
                                                                    fontWeight: 'bold',
                                                                    color: '#fff'
                                                                }}>
                                                                    🗺️ Lộ trình của tôi
                                                                </div>
                                                            </div>
                                                            <div style={{ padding: '12px', display: 'flex', flexDirection: 'column', gap: '6px' }}>
                                                                <div style={{ fontSize: '0.85rem', fontWeight: 800, color: 'var(--text-main)', lineHeight: '1.3' }}>
                                                                    {msg.food_tour.name}
                                                                </div>
                                                                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '6px', fontSize: '0.7rem', color: 'var(--text-muted)' }}>
                                                                    <span>⏱️ {msg.food_tour.duration}</span>
                                                                    <span>•</span>
                                                                    <span>📍 {msg.food_tour.distance}</span>
                                                                    <span>•</span>
                                                                    <span>💰 {msg.food_tour.budget}</span>
                                                                </div>
                                                                {msg.food_tour.description && (
                                                                    <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden', lineHeight: '1.4' }}>
                                                                        {msg.food_tour.description}
                                                                    </div>
                                                                )}
                                                                <a 
                                                                    href={`/food-tour/${msg.food_tour.slug}`}
                                                                    style={{
                                                                        marginTop: '6px',
                                                                        padding: '8px',
                                                                        background: 'var(--primary-grad)',
                                                                        color: '#fff',
                                                                        borderRadius: '8px',
                                                                        fontSize: '0.78rem',
                                                                        fontWeight: 'bold',
                                                                        textAlign: 'center',
                                                                        textDecoration: 'none',
                                                                        display: 'block',
                                                                        transition: 'opacity 0.15s'
                                                                    }}
                                                                    onMouseEnter={(e) => e.target.style.opacity = 0.9}
                                                                    onMouseLeave={(e) => e.target.style.opacity = 1}
                                                                >
                                                                    Xem chi tiết lộ trình ➔
                                                                </a>
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
                                                            {msg.media_path && (
                                                                <div style={{
                                                                    borderRadius: '12px',
                                                                    overflow: 'hidden',
                                                                    maxWidth: '300px',
                                                                    border: '1px solid var(--border-glow)',
                                                                    boxShadow: '0 4px 10px rgba(0,0,0,0.15)',
                                                                    background: '#000'
                                                                }}>
                                                                    {msg.media_type === 'video' ? (
                                                                        <video 
                                                                            src={msg.media_path} 
                                                                            controls 
                                                                            style={{ width: '100%', maxHeight: '240px', display: 'block' }}
                                                                        />
                                                                    ) : (
                                                                        <a href={msg.media_path} target="_blank" rel="noopener noreferrer">
                                                                            <img 
                                                                                src={msg.media_path} 
                                                                                alt="Shared media" 
                                                                                style={{ width: '100%', maxHeight: '240px', objectFit: 'contain', display: 'block', cursor: 'zoom-in' }}
                                                                            />
                                                                        </a>
                                                                    )}
                                                                </div>
                                                            )}
                                                            {msg.message && (
                                                                <div style={{ 
                                                                    padding: '10px 14px', 
                                                                    borderRadius: isSelf ? '16px 16px 2px 16px' : '16px 16px 16px 2px', 
                                                                    background: isSelf ? 'var(--primary-grad)' : 'var(--bg-card)', 
                                                                    color: isSelf ? '#fff' : 'var(--text-main)', 
                                                                    fontSize: '0.88rem', 
                                                                    lineHeight: '1.4', 
                                                                    boxShadow: isSelf ? '0 4px 12px rgba(14,165,233,0.2)' : 'none',
                                                                    border: isSelf ? 'none' : '1px solid var(--border-glow)',
                                                                    alignSelf: isSelf ? 'flex-end' : 'flex-start',
                                                                    textAlign: 'left'
                                                                }}>
                                                                    {msg.message}
                                                                </div>
                                                            )}
                                                        </div>
                                                    )}
                                                    <span style={{ fontSize: '0.65rem', color: 'var(--text-muted)', marginTop: '4px' }}>
                                                        {msg.created_at_format || 'Vừa xong'}
                                                    </span>
                                                </div>
                                            </div>
                                        );
                                    })
                                )}
                                <div ref={messagesEndRef} />
                            </div>

                            {/* Chat Input Bar */}
                            <form onSubmit={handleSendMessage} style={{ position: 'relative', padding: '16px 20px', borderTop: '1px solid var(--border-glow)', background: 'var(--bg-surface)', display: 'flex', gap: '10px' }}>
                                <button 
                                    type="button" 
                                    onClick={() => setShowTourSelector(!showTourSelector)}
                                    title="Chia sẻ lộ trình của tôi"
                                    style={{ 
                                        padding: '0 14px', 
                                        background: showTourSelector ? 'var(--primary)' : 'var(--bg-base)', 
                                        border: '1.5px solid var(--border-glow)', 
                                        borderRadius: '30px', 
                                        color: showTourSelector ? '#fff' : 'var(--text-muted)', 
                                        cursor: 'pointer',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        fontSize: '1.2rem',
                                        transition: 'all 0.2s'
                                    }}
                                >
                                    🗺️
                                </button>

                                <button 
                                    type="button" 
                                    onClick={() => document.getElementById('chat-file-input').click()}
                                    title="Đính kèm ảnh hoặc video"
                                    style={{ 
                                        padding: '0 14px', 
                                        background: selectedFile ? 'var(--primary)' : 'var(--bg-base)', 
                                        border: '1.5px solid var(--border-glow)', 
                                        borderRadius: '30px', 
                                        color: selectedFile ? '#fff' : 'var(--text-muted)', 
                                        cursor: 'pointer',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        fontSize: '1.2rem',
                                        transition: 'all 0.2s'
                                    }}
                                >
                                    📷
                                </button>
                                <input 
                                    type="file"
                                    id="chat-file-input"
                                    onChange={handleFileChange}
                                    accept="image/*,video/*"
                                    style={{ display: 'none' }}
                                />

                                {previewUrl && (
                                    <div style={{
                                        position: 'absolute',
                                        bottom: '80px',
                                        left: '20px',
                                        width: '180px',
                                        padding: '8px',
                                        background: 'var(--bg-card)',
                                        border: '1.5px solid var(--border-glow)',
                                        borderRadius: '12px',
                                        boxShadow: '0 8px 20px rgba(0, 0, 0, 0.3)',
                                        display: 'flex',
                                        flexDirection: 'column',
                                        gap: '6px',
                                        zIndex: 10
                                    }}>
                                        <div style={{ position: 'relative', width: '100%', height: '100px', borderRadius: '8px', overflow: 'hidden', background: '#000' }}>
                                            {selectedFile?.type?.startsWith('video/') ? (
                                                <video 
                                                    src={previewUrl} 
                                                    style={{ width: '100%', height: '100%', objectFit: 'cover' }} 
                                                />
                                            ) : (
                                                <img 
                                                    src={previewUrl} 
                                                    alt="Preview" 
                                                    style={{ width: '100%', height: '100%', objectFit: 'cover' }} 
                                                />
                                            )}
                                            <button 
                                                type="button"
                                                onClick={handleClearFile}
                                                style={{
                                                    position: 'absolute',
                                                    top: '4px',
                                                    right: '4px',
                                                    width: '20px',
                                                    height: '20px',
                                                    borderRadius: '50%',
                                                    background: 'rgba(0,0,0,0.6)',
                                                    border: 'none',
                                                    color: '#fff',
                                                    fontSize: '0.75rem',
                                                    cursor: 'pointer',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    justifyContent: 'center'
                                                }}
                                            >
                                                ✕
                                            </button>
                                        </div>
                                        <div style={{ fontSize: '0.7rem', color: 'var(--text-muted)', display: '-webkit-box', WebkitLineClamp: 1, WebkitBoxOrient: 'vertical', overflow: 'hidden', wordBreak: 'break-all' }}>
                                            {selectedFile?.name}
                                        </div>
                                        {isUploading && (
                                            <div style={{ fontSize: '0.7rem', color: 'var(--primary)', fontWeight: 'bold' }}>
                                                Đang tải lên... ⏳
                                            </div>
                                        )}
                                    </div>
                                )}

                                {showTourSelector && (
                                    <div style={{
                                        position: 'absolute',
                                        bottom: '80px',
                                        left: '20px',
                                        width: '320px',
                                        maxHeight: '280px',
                                        overflowY: 'auto',
                                        background: 'var(--bg-card)',
                                        border: '1.5px solid var(--border-glow)',
                                        borderRadius: '12px',
                                        boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3)',
                                        padding: '12px',
                                        display: 'flex',
                                        flexDirection: 'column',
                                        gap: '8px',
                                        zIndex: 10
                                    }}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px solid var(--border-glow)', paddingBottom: '6px', marginBottom: '4px' }}>
                                            <span style={{ fontSize: '0.85rem', fontWeight: 'bold', color: 'var(--text-main)' }}>Chia sẻ lộ trình của bạn 🗺️</span>
                                            <button 
                                                type="button"
                                                onClick={() => setShowTourSelector(false)}
                                                style={{ background: 'transparent', border: 'none', color: 'var(--text-muted)', cursor: 'pointer', fontSize: '0.9rem' }}
                                            >
                                                ✕
                                            </button>
                                        </div>
                                        {myFoodTours.length === 0 ? (
                                             <div style={{ padding: '20px 10px', textAlign: 'center', color: 'var(--text-muted)', fontSize: '0.8rem' }}>
                                                 Bạn chưa tự thiết kế lộ trình nào. <br/>
                                                 <a href="/food-tours/create" style={{ color: 'var(--primary)', textDecoration: 'underline' }}>Tạo ngay tại đây!</a>
                                             </div>
                                        ) : (
                                            myFoodTours.map(tour => (
                                                <div 
                                                    key={tour.id}
                                                    onClick={() => handleShareTour(tour)}
                                                    style={{
                                                        display: 'flex',
                                                        gap: '8px',
                                                        padding: '8px',
                                                        borderRadius: '8px',
                                                        background: 'var(--bg-base)',
                                                        cursor: 'pointer',
                                                        transition: 'all 0.15s',
                                                        border: '1px solid transparent',
                                                        textAlign: 'left'
                                                    }}
                                                    onMouseEnter={(e) => {
                                                        e.currentTarget.style.borderColor = 'var(--primary)';
                                                        e.currentTarget.style.background = 'var(--bg-card)';
                                                    }}
                                                    onMouseLeave={(e) => {
                                                        e.currentTarget.style.borderColor = 'transparent';
                                                        e.currentTarget.style.background = 'var(--bg-base)';
                                                    }}
                                                >
                                                    <img 
                                                        src={tour.thumbnail || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=150&q=80'} 
                                                        alt={tour.name} 
                                                        style={{ width: '45px', height: '45px', borderRadius: '6px', objectFit: 'cover' }}
                                                    />
                                                    <div style={{ flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
                                                        <span style={{ fontSize: '0.8rem', fontWeight: 600, color: 'var(--text-main)', display: '-webkit-box', WebkitLineClamp: 1, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>{tour.name}</span>
                                                        <span style={{ fontSize: '0.7rem', color: 'var(--text-muted)' }}>⏱️ {tour.duration} • 💰 {tour.budget}</span>
                                                    </div>
                                                </div>
                                            ))
                                        )}
                                    </div>
                                )}

                                <input 
                                    type="text"
                                    value={newMessageText}
                                    onChange={(e) => setNewMessageText(e.target.value)}
                                    placeholder={selectedFile ? "Viết chú thích cho ảnh/video..." : "Viết tin nhắn của bạn..."}
                                    style={{ flex: 1, padding: '12px 16px', background: 'var(--bg-base)', border: '1.5px solid var(--border-glow)', borderRadius: '30px', color: 'var(--text-main)', fontSize: '0.88rem', outline: 'none', transition: 'border-color 0.2s' }}
                                    onFocus={(e) => e.target.style.borderColor = 'var(--primary)'}
                                    onBlur={(e) => e.target.style.borderColor = 'var(--border-glow)' }
                                />
                                <button 
                                    type="submit"
                                    disabled={(!newMessageText.trim() && !selectedFile) || isSending || isUploading}
                                    style={{ 
                                        padding: '0 24px', 
                                        background: 'var(--primary-grad)', 
                                        border: 'none', 
                                        color: '#fff', 
                                        fontWeight: 700, 
                                        borderRadius: '30px', 
                                        cursor: 'pointer', 
                                        transition: 'all 0.2s',
                                        opacity: ((!newMessageText.trim() && !selectedFile) || isSending || isUploading) ? 0.6 : 1
                                    }}
                                >
                                    {isUploading ? 'Đang tải... ⏳' : isSending ? 'Đang gửi... ⏳' : 'Gửi 💬'}
                                </button>
                            </form>
                        </>
                    ) : (
                        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)', gap: '12px', padding: '40px' }}>
                            <span style={{ fontSize: '4rem' }}>💬</span>
                            <h3 style={{ fontSize: '1.2rem', fontWeight: 800, color: 'var(--text-main)', margin: 0 }}>Social Chat Hub</h3>
                            <p style={{ fontSize: '0.88rem', color: 'var(--text-muted)', maxWidth: '350px', textAlign: 'center', margin: 0, lineHeight: 1.6 }}>
                                Chọn một người bạn từ danh sách bên trái hoặc quét vị trí GPS để bắt đầu nhắn tin realtime và kết nối trực tuyến!
                            </p>
                        </div>
                    )}

                </div>
                )}

            </div>

            {isGuideOpen && (
                <div 
                    id="guideModal" 
                    style={{ 
                        position: 'fixed', 
                        inset: 0, 
                        zIndex: 99999, 
                        background: 'rgba(0, 0, 0, 0.75)', 
                        backdropFilter: 'blur(12px)', 
                        display: 'flex', 
                        alignItems: 'center', 
                        justifyContent: 'center',
                        animation: 'fadeIn 0.25s ease-out forwards'
                    }}
                    onClick={() => setIsGuideOpen(false)}
                >
                    <style>{`
                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }
                        @keyframes scaleIn {
                            from { transform: scale(0.9); opacity: 0; }
                            to { transform: scale(1); opacity: 1; }
                        }
                    `}</style>
                    <div 
                        className="lightbox-content" 
                        style={{ 
                            background: 'var(--bg-card)', 
                            border: '1px solid var(--border-glow)', 
                            width: '90%', 
                            maxWidth: '650px', 
                            maxHeight: '85vh', 
                            borderRadius: '24px', 
                            boxShadow: '0 20px 50px rgba(0, 0, 0, 0.5)', 
                            overflow: 'hidden', 
                            display: 'flex', 
                            flexDirection: 'column', 
                            position: 'relative',
                            animation: 'scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards'
                        }}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Modal Header */}
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px dashed var(--border-glow)', padding: '20px 24px', background: 'rgba(255,255,255,0.01)' }}>
                            <h3 style={{ margin: 0, fontSize: '1.4rem', fontWeight: 800, color: 'var(--text-main)', display: 'flex', alignItems: 'center', gap: '10px', fontFamily: 'var(--font-heading)' }}>
                                ℹ️ Giới thiệu & Hướng dẫn sử dụng
                            </h3>
                            <button 
                                onClick={() => setIsGuideOpen(false)} 
                                style={{ background: 'transparent', border: 'none', fontSize: '1.5rem', color: 'var(--text-muted)', cursor: 'pointer', transition: 'color 0.2s' }}
                                onMouseOver={(e) => { e.target.style.color = 'var(--primary)' }}
                                onMouseOut={(e) => { e.target.style.color = 'var(--text-muted)' }}
                            >
                                ✕
                            </button>
                        </div>
                        
                        {/* Modal Content (Scrollable) */}
                        <div style={{ overflowY: 'auto', padding: '24px 28px', flex: 1, display: 'flex', flexDirection: 'column', gap: '24px', lineHeight: '1.6', color: 'var(--text-muted)' }}>
                            {/* Section 1: Giới thiệu Đông Anh */}
                            <div>
                                <h4 style={{ color: 'var(--text-main)', fontSize: '1.1rem', marginTop: 0, marginBottom: '8px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                                    🌾 Giới thiệu về Đông Anh
                                </h4>
                                <p style={{ fontSize: '0.9rem', margin: 0 }}>
                                    Đông Anh là vùng đất địa linh nhân kiệt có bề dày lịch sử và truyền thống văn hóa lâu đời, gắn liền với di tích Cổ Loa thành. Hiện nay, Đông Anh đang chuyển mình mạnh mẽ trong tiến trình đô thị hóa và chuyển đổi số. Bản đồ số Đông Anh ra đời nhằm cung cấp giải pháp số hóa toàn diện các hạ tầng dịch vụ: trường học, y tế, lưu trú, ẩm thực địa phương và các sản phẩm OCOP đặc trưng của xã, hỗ trợ nâng cao đời sống dân cư và thúc đẩy phát triển du lịch bền vững.
                                </p>
                            </div>
                            
                            {/* Section 2: Hướng dẫn sử dụng */}
                            <div>
                                <h4 style={{ color: 'var(--text-main)', fontSize: '1.1rem', marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                                    🗺️ Hướng dẫn sử dụng bản đồ
                                </h4>
                                
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
                                    <div style={{ display: 'flex', gap: '12px' }}>
                                        <span style={{ fontSize: '1.2rem', background: 'rgba(59, 130, 246, 0.1)', color: 'var(--primary)', width: '28px', height: '28px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontWeight: 'bold' }}>1</span>
                                        <div>
                                            <strong style={{ color: 'var(--text-main)', display: 'block', fontSize: '0.9rem' }}>Tìm kiếm địa điểm</strong>
                                            <span style={{ fontSize: '0.85rem' }}>Nhập từ khóa như tên trường học, bệnh viện, khách sạn, món ăn tại ô tìm kiếm ở trang chủ để hiển thị vị trí trên bản đồ vệ tinh.</span>
                                        </div>
                                    </div>
                                    
                                    <div style={{ display: 'flex', gap: '12px' }}>
                                        <span style={{ fontSize: '1.2rem', background: 'rgba(59, 130, 246, 0.1)', color: 'var(--primary)', width: '28px', height: '28px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontWeight: 'bold' }}>2</span>
                                        <div>
                                            <strong style={{ color: 'var(--text-main)', display: 'block', fontSize: '0.9rem' }}>Lọc nâng cao & Bán kính GPS</strong>
                                            <span style={{ fontSize: '0.85rem' }}>Trong phần "Bản đồ & Tìm kiếm", bạn có thể kích hoạt định vị GPS thực tế để quét các tiện ích xung quanh mình trong phạm vi từ 1km đến 10km.</span>
                                        </div>
                                    </div>
                                    
                                    <div style={{ display: 'flex', gap: '12px' }}>
                                        <span style={{ fontSize: '1.2rem', background: 'rgba(59, 130, 246, 0.1)', color: 'var(--primary)', width: '28px', height: '28px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontWeight: 'bold' }}>3</span>
                                        <div>
                                            <strong style={{ color: 'var(--text-main)', display: 'block', fontSize: '0.9rem' }}>Góc trải nghiệm thực tế</strong>
                                            <span style={{ fontSize: '0.85rem' }}>Tham gia các lớp học nghề, hoạt động vui chơi giải trí và trải nghiệm thực tế các ngành nghề, văn hóa truyền thống Đông Anh.</span>
                                        </div>
                                    </div>

                                    <div style={{ display: 'flex', gap: '12px' }}>
                                        <span style={{ fontSize: '1.2rem', background: 'rgba(59, 130, 246, 0.1)', color: 'var(--primary)', width: '28px', height: '28px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontWeight: 'bold' }}>4</span>
                                        <div>
                                            <strong style={{ color: 'var(--text-main)', display: 'block', fontSize: '0.9rem' }}>Gửi phản hồi chất lượng dịch vụ</strong>
                                            <span style={{ fontSize: '0.85rem' }}>Nếu phát hiện thông tin địa điểm không chính xác hoặc dịch vụ không đạt chất lượng/an toàn vệ sinh, hãy nhấn "Báo cáo / Góp ý" trực tiếp tại trang chi tiết để gửi thông tin ẩn danh đến Ban quản lý.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Modal Footer */}
                        <div style={{ borderTop: '1px dashed var(--border-glow)', padding: '16px 24px', background: 'rgba(255,255,255,0.01)', display: 'flex', justifyContent: 'flex-end' }}>
                            <button onClick={() => setIsGuideOpen(false)} className="btn-primary" style={{ fontSize: '0.85rem', padding: '8px 20px', borderRadius: '8px', cursor: 'pointer' }}>Đã hiểu</button>
                        </div>
                    </div>
                </div>
            )}

        </div>
    );
}
