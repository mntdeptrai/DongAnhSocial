<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful multi-upload of valid image and video files.
     */
    public function test_successful_multi_upload(): void
    {
        Storage::fake('public');
        Storage::fake('r2');

        $user = User::factory()->create();
        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4'); // 5MB video

        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'files' => [$file1, $file2]
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'files' => [
                         '*' => [
                             'original_name',
                             'stored_name',
                             'url',
                             'size',
                             'formatted_size',
                             'mime_type',
                             'file_type'
                         ]
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'message' => 'Tải lên các tệp thành công!'
                 ]);

        // Kiểm tra xem các tệp có được lưu trên R2 hay không
        $data = $response->json();
        Storage::disk('r2')->assertExists('uploads/' . $data['files'][0]['stored_name']);
        Storage::disk('r2')->assertExists('uploads/' . $data['files'][1]['stored_name']);
    }

    /**
     * Test validation failure when a file exceeds the 500MB limit.
     */
    public function test_upload_fails_when_file_exceeds_500mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        // 500MB = 512,000 KB. Hãy tạo file 512,001 KB (> 500MB)
        $largeFile = UploadedFile::fake()->create('movie.mp4', 512001, 'video/mp4');

        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'files' => [$largeFile]
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['files.0']);
    }

    /**
     * Test validation failure when the combined size of all files exceeds the 500MB limit.
     */
    public function test_upload_fails_when_total_size_exceeds_500mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        // Tạo 3 tệp tin, mỗi tệp 200MB (Tổng 600MB > 500MB)
        // 200MB = 204,800 KB
        $file1 = UploadedFile::fake()->create('part1.mp4', 204800, 'video/mp4');
        $file2 = UploadedFile::fake()->create('part2.mp4', 204800, 'video/mp4');
        $file3 = UploadedFile::fake()->create('part3.mp4', 204800, 'video/mp4');

        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'files' => [$file1, $file2, $file3]
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Tổng dung lượng của tất cả các tệp tải lên vượt quá giới hạn cho phép (500MB).'
                 ]);
     }

    /**
     * Test that rate limit is enforced after exceeding limit (5 uploads per minute).
     */
    public function test_upload_rate_limiting(): void
    {
        Storage::fake('public');
        Storage::fake('r2');
        
        // Đảm bảo cache sạch sẽ để bắt đầu test rate limit
        RateLimiter::clear('uploads:' . request()->ip());

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('small.jpg');

        // Gửi 5 yêu cầu upload hợp lệ liên tiếp (nằm trong giới hạn)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($user)->postJson('/api/v1/upload', [
                'files' => [$file]
            ]);
            $response->assertStatus(200);
        }

        // Gửi yêu cầu thứ 6 (vượt quá giới hạn)
        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'files' => [$file]
        ]);

        $response->assertStatus(429)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Bạn đã gửi quá nhiều yêu cầu tải lên. Vui lòng thử lại sau 1 phút.'
                 ]);
    }

    /**
     * Test that an image larger than 1200px is resized to 1200px before uploading.
     */
    public function test_image_is_resized_before_upload(): void
    {
        Storage::fake('public');
        Storage::fake('r2');

        $user = User::factory()->create();
        // Tạo 1 ảnh giả lập kích thước 2000x1500px
        $largeImage = UploadedFile::fake()->image('large_photo.jpg', 2000, 1500);

        $response = $this->actingAs($user)->postJson('/api/v1/upload', [
            'files' => [$largeImage]
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        
        $storedName = $data['files'][0]['stored_name'];
        Storage::disk('r2')->assertExists('uploads/' . $storedName);

        // Lấy nội dung tệp tin đã lưu trên R2 và kiểm tra kích thước thực tế bằng GD
        $storedContent = Storage::disk('r2')->get('uploads/' . $storedName);
        $imageResource = imagecreatefromstring($storedContent);
        
        $this->assertNotFalse($imageResource);
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);
        
        imagedestroy($imageResource);

        // Chiều dài lớn nhất (chiều rộng) phải được resize về đúng 1200px
        $this->assertEquals(1200, $width);
        // Chiều cao phải co giãn tỷ lệ (1200 / 2000 * 1500 = 900px)
        $this->assertEquals(900, $height);
    }
}
