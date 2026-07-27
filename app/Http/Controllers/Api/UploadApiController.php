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

        // 4. Lưu trữ các tệp lên Cloudflare R2 (tự động resize nếu là hình ảnh, nén chuẩn 80% GD)
        $folder = $request->input('folder', 'uploads');
        $maxDimension = (int) $request->input('max_dimension', 1200);

        $uploaded = \App\Helpers\R2Helper::uploadMultiple($files, $folder, $maxDimension);
        $uploadedFiles = [];

        foreach ($uploaded as $item) {
            $item['formatted_size'] = $this->formatBytes($item['size']);
            $uploadedFiles[] = $item;
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

    /**
     * Handle chunked upload for large video files.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'chunk'        => 'required|file',
            'upload_id'    => 'required|string',
            'chunk_index'  => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'folder'       => 'nullable|string'
        ]);

        $chunk       = $request->file('chunk');
        $uploadId    = $request->input('upload_id');
        $chunkIndex  = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $folder      = $request->input('folder', 'videos');

        $result = \App\Helpers\R2Helper::uploadChunk($chunk, $uploadId, $chunkIndex, $totalChunks, $folder);

        return response()->json([
            'success' => true,
            'data'    => $result
        ], 200);
    }
}
