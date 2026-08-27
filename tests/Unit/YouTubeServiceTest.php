<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\YouTubeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\UploadedFile;

class YouTubeServiceTest extends TestCase
{
    public function test_extract_video_id_from_various_youtube_url_formats()
    {
        $id = 'dQw4w9WgXcQ';

        // 1. Standard watch URL
        $this->assertEquals($id, YouTubeService::extractVideoId("https://www.youtube.com/watch?v={$id}"));
        $this->assertEquals($id, YouTubeService::extractVideoId("http://youtube.com/watch?v={$id}&feature=share"));

        // 2. Short URL (youtu.be)
        $this->assertEquals($id, YouTubeService::extractVideoId("https://youtu.be/{$id}"));
        $this->assertEquals($id, YouTubeService::extractVideoId("https://youtu.be/{$id}?t=42"));

        // 3. Shorts URL
        $this->assertEquals($id, YouTubeService::extractVideoId("https://www.youtube.com/shorts/{$id}"));
        $this->assertEquals($id, YouTubeService::extractVideoId("https://youtube.com/shorts/{$id}?feature=share"));

        // 4. Embed URL
        $this->assertEquals($id, YouTubeService::extractVideoId("https://www.youtube.com/embed/{$id}"));

        // 5. Live URL
        $this->assertEquals($id, YouTubeService::extractVideoId("https://www.youtube.com/live/{$id}"));

        // 6. Direct ID
        $this->assertEquals($id, YouTubeService::extractVideoId($id));

        // 7. Non-YouTube URLs
        $this->assertNull(YouTubeService::extractVideoId("https://tiktok.com/@user/video/123456789"));
        $this->assertNull(YouTubeService::extractVideoId("https://example.com/video.mp4"));
        $this->assertNull(YouTubeService::extractVideoId(""));
        $this->assertNull(YouTubeService::extractVideoId(null));
    }

    public function test_get_embed_and_watch_urls()
    {
        $id = 'dQw4w9WgXcQ';
        $shortUrl = "https://youtu.be/{$id}";

        $this->assertEquals("https://www.youtube.com/embed/{$id}", YouTubeService::getEmbedUrl($shortUrl));
        $this->assertEquals("https://www.youtube.com/watch?v={$id}", YouTubeService::getWatchUrl($shortUrl));
        $this->assertEquals("https://img.youtube.com/vi/{$id}/hqdefault.jpg", YouTubeService::getThumbnailUrl($shortUrl));
    }

    public function test_is_configured_check()
    {
        Config::set('services.youtube.client_id', 'test_client_id');
        Config::set('services.youtube.client_secret', 'test_client_secret');
        Config::set('services.youtube.refresh_token', 'test_refresh_token');

        $this->assertTrue(YouTubeService::isConfigured());

        Config::set('services.youtube.refresh_token', '');
        $this->assertFalse(YouTubeService::isConfigured());
    }

    public function test_get_access_token_via_refresh_token()
    {
        Config::set('services.youtube.client_id', 'test_client_id');
        Config::set('services.youtube.client_secret', 'test_client_secret');
        Config::set('services.youtube.refresh_token', 'test_refresh_token');

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock_access_token_12345',
                'expires_in'   => 3600,
                'token_type'   => 'Bearer',
            ], 200),
        ]);

        $token = YouTubeService::getAccessToken();
        $this->assertEquals('mock_access_token_12345', $token);
    }

    public function test_upload_video_resumable_flow()
    {
        Config::set('services.youtube.client_id', 'test_client_id');
        Config::set('services.youtube.client_secret', 'test_client_secret');
        Config::set('services.youtube.refresh_token', 'test_refresh_token');

        $mockLocation = 'https://www.googleapis.com/upload/youtube/v3/videos?upload_id=mock_session_999';

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock_access_token_12345',
                'expires_in'   => 3600,
            ], 200),
            'https://www.googleapis.com/upload/youtube/v3/videos*' => function (\Illuminate\Http\Client\Request $request) use ($mockLocation) {
                if ($request->method() === 'POST') {
                    return Http::response([], 200, ['Location' => $mockLocation]);
                }
                if ($request->method() === 'PUT') {
                    return Http::response([
                        'id' => 'abc123xyz89',
                        'snippet' => [
                            'title' => 'Test Video Title',
                        ]
                    ], 200);
                }
                return Http::response([], 404);
            },
        ]);

        $fakeVideo = UploadedFile::fake()->create('sample_video.mp4', 1024, 'video/mp4');

        $result = YouTubeService::uploadVideo($fakeVideo, 'Test Video Title', 'Test description', 'unlisted');

        $this->assertNotNull($result);
        $this->assertEquals('abc123xyz89', $result['id']);
        $this->assertEquals('https://www.youtube.com/watch?v=abc123xyz89', $result['url']);
        $this->assertEquals('https://www.youtube.com/embed/abc123xyz89', $result['embed_url']);
    }

    public function test_create_live_event_flow()
    {
        Config::set('services.youtube.client_id', 'test_client_id');
        Config::set('services.youtube.client_secret', 'test_client_secret');
        Config::set('services.youtube.refresh_token', 'test_refresh_token');

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock_access_token_12345',
                'expires_in'   => 3600,
            ], 200),
            'https://www.googleapis.com/youtube/v3/liveBroadcasts*' => Http::response([
                'id' => 'liveBroadcast123',
                'snippet' => ['title' => 'Livestream Test'],
            ], 200),
            'https://www.googleapis.com/youtube/v3/liveStreams*' => Http::response([
                'id' => 'liveStreamCDN456',
                'cdn' => [
                    'ingestionInfo' => [
                        'ingestionAddress' => 'rtmp://a.rtmp.youtube.com/live2',
                        'streamName'       => 'abcd-1234-efgh-5678',
                    ]
                ]
            ], 200),
        ]);

        $result = YouTubeService::createLiveEvent('Livestream Test', 'Mo ta livestream', 'public');

        $this->assertNotNull($result);
        $this->assertEquals('liveBroadcast123', $result['video_id']);
        $this->assertEquals('https://www.youtube.com/watch?v=liveBroadcast123', $result['watch_url']);
        $this->assertEquals('https://www.youtube.com/embed/liveBroadcast123', $result['embed_url']);
        $this->assertEquals('rtmp://a.rtmp.youtube.com/live2', $result['rtmp_server_url']);
        $this->assertEquals('abcd-1234-efgh-5678', $result['stream_key']);
    }
}
