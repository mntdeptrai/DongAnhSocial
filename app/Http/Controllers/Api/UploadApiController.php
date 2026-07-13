<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadApiController extends Controller
{
    /**
     * Handle the multi-upload request for images and videos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // 1. Kiểm tra sự tồn tại của tệp tải lên
        if (!$request->hasFile('files')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một tệp để tải lên.'
            ], 400);
        }

        // 2. Validate định dạng từng tệp và kích thước từng tệp (tối đa 500MB = 512,000 KB)
        // Hỗ trợ cả hình ảnh và video thông dụng
        $rules = [
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,mkv,webm|max:512000'
        ];

        $messages = [
            'files.required' => 'Danh sách tệp tải lên không được để trống.',
            'files.array' => 'Định dạng dữ liệu tệp tải lên phải là một danh sách.',
            'files.*.required' => 'Tệp tải lên không hợp lệ.',
            'files.*.file' => 'Dữ liệu tải lên phải là định dạng tệp tin.',
            'files.*.mimes' => 'Định dạng tệp không được hỗ trợ. Chỉ cho phép các định dạng: jpeg, png, jpg, gif, webp, mp4, mov, avi, mkv, webm.',
            'files.*.max' => 'Kích thước của mỗi tệp tin không được vượt quá 500MB.'
        ];

        $request->validate($rules, $messages);

        // 3. Tính toán tổng dung lượng của tất cả các tệp (giới hạn 500MB = 524,288,000 bytes)
        $totalSize = 0;
        $files = $request->file('files');
        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }

        $maxTotalSize = 500 * 1024 * 1024; // 500 MB in bytes
        if ($totalSize > $maxTotalSize) {
            return response()->json([
                'success' => false,
                'message' => 'Tổng dung lượng của tất cả các tệp tải lên vượt quá giới hạn cho phép (500MB).'
            ], 422);
        }

        // 4. Lưu trữ các tệp lên Cloudflare R2 (tự động resize nếu là hình ảnh) và biên soạn thông tin phản hồi
        $uploadedFiles = [];
        foreach ($files as $file) {
            $url = \App\Helpers\R2Helper::upload($file, 'uploads');
            
            // Xác định loại tệp (image hoặc video)
            $mimeType = $file->getClientMimeType();
            $fileType = str_starts_with($mimeType, 'video/') ? 'video' : 'image';

            $uploadedFiles[] = [
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => basename($url),
                'url' => $url,
                'size' => $file->getSize(),
                'formatted_size' => $this->formatBytes($file->getSize()),
                'mime_type' => $mimeType,
                'file_type' => $fileType
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Tải lên các tệp thành công!',
            'files' => $uploadedFiles
        ], 200);
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
