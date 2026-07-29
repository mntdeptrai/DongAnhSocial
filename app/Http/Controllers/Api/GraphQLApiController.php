<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eatery;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * GraphQLApiController - Triển khai giao thức GraphQL (Flexible Query)
 * 
 * Cho phép Client gửi câu truy vấn linh hoạt, tự định hình cấu trúc dữ liệu trả về,
 * giải quyết triệt để vấn đề Over-fetching (nhận thừa dữ liệu) và Under-fetching (thiếu dữ liệu).
 */
class GraphQLApiController extends Controller
{
    /**
     * Endpoint chính cho GraphQL Query
     * POST /api/v1/graphql
     */
    public function query(Request $request)
    {
        $queryStr = $request->input('query');
        if (empty($queryStr)) {
            return response()->json(['errors' => [['message' => 'GraphQL query is empty.']]], 400);
        }

        try {
            $result = $this->parseAndExecute($queryStr);
            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['errors' => [['message' => $e->getMessage()]]], 500);
        }
    }

    /**
     * Trình phân tích cú pháp GraphQL đơn giản (Regex-based parser)
     * Nhận diện các query dạng:
     * query {
     *   eatery(id: 1) {
     *     id
     *     name
     *     rating
     *   }
     * }
     */
    protected function parseAndExecute($queryStr)
    {
        // Loại bỏ khoảng trắng thừa
        $queryStr = preg_replace('/\s+/', ' ', trim($queryStr));

        // Phân tích loại truy vấn và tên field chính
        // Ví dụ: eatery(id:1) { id name } hoặc eateries { id name }
        if (preg_match('/(?:query)?\s*\{\s*([a-zA-Z_]+)(?:\(([^)]+)\))?\s*\{\s*([^}]+)\s*\}\s*\}/', $queryStr, $matches)) {
            $fieldName = $matches[1];
            $argsRaw = $matches[2] ?? '';
            $fieldsRaw = $matches[3];

            // Parse danh sách các trường cần lấy
            $fields = array_filter(array_map('trim', explode(' ', $fieldsRaw)));
            
            // Parse arguments
            $args = [];
            if (!empty($argsRaw)) {
                $parts = explode(',', $argsRaw);
                foreach ($parts as $part) {
                    $kv = explode(':', $part);
                    if (count($kv) === 2) {
                        $args[trim($kv[0])] = trim($kv[1], " '\"");
                    }
                }
            }

            return $this->resolveField($fieldName, $args, $fields);
        }

        throw new \Exception('Cú pháp GraphQL chưa được hỗ trợ hoặc bị sai định dạng.');
    }

    /**
     * Resolver xử lý dữ liệu động dựa trên tên trường
     */
    protected function resolveField($fieldName, $args, $requestedFields)
    {
        // Chuyển đổi tên trường để chọn model tương ứng
        switch ($fieldName) {
            case 'eatery':
                $id = $args['id'] ?? null;
                if (!$id) throw new \Exception('Tham số id là bắt buộc đối với truy vấn eatery.');
                
                // Chỉ select những cột được client yêu cầu (Tránh Over-fetching)
                $dbFields = array_intersect($requestedFields, ['id', 'name', 'slug', 'address', 'phone', 'rating', 'description', 'price_range', 'is_featured']);
                if (empty($dbFields)) $dbFields = ['id', 'name'];

                $eatery = Eatery::select($dbFields)->find($id);
                return $eatery ? $eatery->toArray() : null;

            case 'eateries':
                $limit = $args['limit'] ?? 10;
                $dbFields = array_intersect($requestedFields, ['id', 'name', 'slug', 'address', 'rating', 'price_range']);
                if (empty($dbFields)) $dbFields = ['id', 'name'];

                return Eatery::select($dbFields)->limit($limit)->get()->toArray();

            case 'users':
                $dbFields = array_intersect($requestedFields, ['id', 'name', 'email', 'role', 'status']);
                if (empty($dbFields)) $dbFields = ['id', 'name', 'email'];

                return User::select($dbFields)->limit(10)->get()->toArray();

            case 'categories':
                $dbFields = array_intersect($requestedFields, ['id', 'name', 'slug']);
                if (empty($dbFields)) $dbFields = ['id', 'name', 'slug'];

                return Category::select($dbFields)->get()->toArray();

            default:
                throw new \Exception("Không tìm thấy resolver cho field: '{$fieldName}'");
        }
    }
}
