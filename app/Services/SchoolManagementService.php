<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Helpers\VietnameseSeoHelper;
use App\Models\CheckinReaction;
use App\Models\Comment;
use App\Models\Eatery;
use App\Models\EateryPhoto;
use App\Models\EducationProgram;
use App\Models\Post;
use App\Models\ReviewVideo;
use App\Models\User;
use App\Services\Interfaces\SchoolManagementServiceInterface;
use App\Services\NotificationService;
use App\Services\YouTubeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SchoolManagementService implements SchoolManagementServiceInterface
{
    public function verifyPrincipalOrAdmin(): void
    {
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        if (!in_array($role, ['principal', 'admin'])) {
            abort(403, 'Bạn không có quyền truy cập Kênh Quản Lý Trường Học! Chỉ tài khoản Hiệu trưởng hoặc Quản trị viên hệ thống mới có quyền truy cập.');
        }
    }

    public function findSchool($idOrSlug)
    {
        if (is_numeric($idOrSlug)) {
            $school = Eatery::on('mysql_education')->find($idOrSlug);
            if (!$school) {
                $school = Eatery::on('mysql')->find($idOrSlug);
            }
        } else {
            $school = Eatery::on('mysql_education')->where('slug', $idOrSlug)->first();
            if (!$school) {
                $school = Eatery::on('mysql')->where('slug', $idOrSlug)->first();
            }
        }
        return $school;
    }

    public function getDashboardRedirectSchool()
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $school = Eatery::on('mysql_education')->where('user_id', $user->id)->first();
        if (!$school) {
            $school = Eatery::on('mysql')->where('user_id', $user->id)->first();
        }
        return $school;
    }

    public function getSchoolsList(User $user)
    {
        $this->verifyPrincipalOrAdmin();

        if ($user->isAdmin()) {
            $schools = Eatery::on('mysql_education')
                ->with('commune')
                ->whereHas('category', function($q) {
                    $q->where('slug', 'smart-education-map');
                })
                ->get();

            if ($schools->isEmpty()) {
                $schools = Eatery::on('mysql')
                    ->with('commune')
                    ->whereHas('category', function($q) {
                        $q->where('slug', 'smart-education-map');
                    })
                    ->get();
            }
        } else {
            $schools = Eatery::on('mysql_education')
                ->where('user_id', $user->id)
                ->with('commune')
                ->get();

            if ($schools->isEmpty()) {
                $schools = Eatery::on('mysql')
                    ->where('user_id', $user->id)
                    ->with('commune')
                    ->get();
            }

            if ($schools->isEmpty() && !empty($user->name)) {
                $searchName = trim(preg_replace('/^(trường\s+|tài\s+khoản\s+)/ui', '', $user->name));
                if (mb_strlen($searchName) >= 3) {
                    $matchedSchools = Eatery::on('mysql_education')
                        ->where(function($q) use ($searchName) {
                            $q->where('name', $searchName)
                              ->orWhere('name', 'Trường ' . $searchName)
                              ->orWhere('name', 'Trường Tiểu học ' . $searchName)
                              ->orWhere('name', 'Trường THCS ' . $searchName)
                              ->orWhere('name', 'Trường THPT ' . $searchName)
                              ->orWhere('name', 'Trường Mầm non ' . $searchName);
                        })
                        ->where(function($q) use ($user) {
                            $q->whereNull('user_id')->orWhere('user_id', 0)->orWhere('user_id', $user->id);
                        })
                        ->with('commune')
                        ->get();

                    if ($matchedSchools->isNotEmpty()) {
                        foreach ($matchedSchools as $mSch) {
                            $mSch->user_id = $user->id;
                            $mSch->save();
                        }
                        $schools = $matchedSchools;
                    }
                }
            }
        }

        return $schools;
    }

    public function getSchoolForEdit($id, User $user): ?array
    {
        $this->verifyPrincipalOrAdmin();
        $school = $this->findSchool($id);

        if (!$school) {
            return null;
        }

        if (!$user->isAdmin() && $school->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa trường học này! Tài khoản Hiệu trưởng chỉ được phép quản lý duy nhất 1 trường học được phân công.');
        }

        return [
            'school'     => $school,
            'storyData'  => $school->storytelling_data ?? [],
            'components' => $school->merged_components,
        ];
    }

    public function updateSchool($id, User $user, array $data, $imageFile, ?string $imageUrl)
    {
        $this->verifyPrincipalOrAdmin();
        $school = $this->findSchool($id);
        if (!$school) {
            return null;
        }

        if (!$user->isAdmin() && $school->user_id !== $user->id) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $stdName = VietnameseSeoHelper::standardizeSchoolName($data['name']);

        $school->name = $stdName;
        $school->phone = $data['phone'] ?? $school->phone;
        $school->address = $data['address'] ?? $school->address;
        if (!empty($data['opening_hours'])) {
            $school->opening_hours = $data['opening_hours'];
        }

        if ($imageFile) {
            $imagePath = R2Helper::upload($imageFile, 'education');
            if ($imagePath) {
                $school->image_path = $imagePath;
            }
        } elseif (!empty($imageUrl)) {
            $school->image_path = $imageUrl;
        }

        $existingData = $school->storytelling_data ?? [];

        $components = [];
        $totalClasses = 0;
        $totalStudents = 0;
        $totalStaff = 0;
        $totalArea = 0;
        $locations = [];

        $existingComponents = $existingData['components'] ?? [];

        if (!empty($data['components']) && is_array($data['components'])) {
            foreach ($data['components'] as $index => $comp) {
                $oldComp = $existingComponents[$index] ?? [];
                
                if ($user->isAdmin()) {
                    $compName = VietnameseSeoHelper::standardizeSchoolName($comp['name'] ?? '');
                    $compAddress = $comp['address'] ?? '';
                    $compPrincipal = $comp['principal'] ?? '';
                    $compPhone = $comp['phone'] ?? '';
                    $gmapLink = $comp['gmap_link'] ?? '';
                    $latVal = (float)($comp['lat'] ?? $school->latitude);
                    $lngVal = (float)($comp['lng'] ?? $school->longitude);

                    $photoUrl = $comp['existing_photo'] ?? ($oldComp['photo'] ?? 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80');
                    if (!empty($comp['photo_file']) && $comp['photo_file'] instanceof UploadedFile) {
                        $uploaded = R2Helper::upload($comp['photo_file'], 'education');
                        if ($uploaded) {
                            $photoUrl = $uploaded;
                        }
                    }
                } else {
                    $compName = $oldComp['name'] ?? VietnameseSeoHelper::standardizeSchoolName($comp['name'] ?? '');
                    $compAddress = $oldComp['address'] ?? ($comp['address'] ?? '');
                    $compPrincipal = $oldComp['principal'] ?? ($comp['principal'] ?? '');
                    $compPhone = $oldComp['phone'] ?? ($comp['phone'] ?? '');
                    $gmapLink = $oldComp['gmap_link'] ?? ($comp['gmap_link'] ?? '');
                    $latVal = (float)($oldComp['lat'] ?? $school->latitude);
                    $lngVal = (float)($oldComp['lng'] ?? $school->longitude);
                    $photoUrl = $oldComp['photo'] ?? ($comp['existing_photo'] ?? 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80');
                }

                if (empty($gmapLink) && $latVal && $lngVal) {
                    $gmapLink = "https://www.google.com/maps?q={$latVal},{$lngVal}";
                }

                if ($user->isAdmin()) {
                    $areaVal = (float)($comp['area'] ?? ($oldComp['area'] ?? 0));
                } else {
                    $areaVal = (float)($oldComp['area'] ?? 0);
                }

                $classesVal = (int)($comp['classes'] ?? ($oldComp['classes'] ?? 0));
                $studentsVal = (int)($comp['students'] ?? ($oldComp['students'] ?? 0));
                $staffVal = (int)($comp['staff'] ?? ($oldComp['staff'] ?? 0));

                $totalClasses += $classesVal;
                $totalStudents += $studentsVal;
                $totalStaff += $staffVal;
                $totalArea += $areaVal;

                if (!empty($compAddress)) {
                    $locations[] = [
                        'label'     => 'Địa điểm ' . ($index + 1),
                        'name'      => $compName,
                        'address'   => $compAddress,
                        'gmap_link' => $gmapLink
                    ];
                }

                $components[] = [
                    'name'      => $compName,
                    'address'   => $compAddress,
                    'principal' => $compPrincipal,
                    'phone'     => $compPhone,
                    'classes'   => $classesVal,
                    'students'  => $studentsVal,
                    'staff'     => $staffVal,
                    'area'      => $areaVal,
                    'lat'       => $latVal,
                    'lng'       => $lngVal,
                    'gmap_link' => $gmapLink,
                    'photo'     => $photoUrl,
                ];
            }
        }

        $mergedSchoolData = $existingData['mergedSchool'] ?? [];
        $mergedSchoolData['name'] = $stdName;
        $mergedSchoolData['address'] = $school->address;
        $mergedSchoolData['phone'] = $school->phone;
        $mergedSchoolData['opening_hours'] = $school->opening_hours;
        $mergedSchoolData['photo'] = $school->image_path ?: ($mergedSchoolData['photo'] ?? '');
        $mergedSchoolData['lat'] = (float)$school->latitude;
        $mergedSchoolData['lng'] = (float)$school->longitude;
        $mergedSchoolData['total_classes'] = $totalClasses;

        $mergedSchoolData['founded_year'] = !empty($data['founded_year']) ? (int)$data['founded_year'] : ($mergedSchoolData['founded_year'] ?? 2008);
        $mergedSchoolData['total_staff'] = !empty($data['total_teachers']) ? (int)$data['total_teachers'] : ($totalStaff ?: ($mergedSchoolData['total_staff'] ?? 63));
        $mergedSchoolData['total_students'] = !empty($data['total_students']) ? (int)$data['total_students'] : ($totalStudents ?: ($mergedSchoolData['total_students'] ?? 759));
        $mergedSchoolData['awards_count'] = !empty($data['awards_count']) ? (int)$data['awards_count'] : ($mergedSchoolData['awards_count'] ?? 12);
        $mergedSchoolData['website'] = $data['website'] ?? ($mergedSchoolData['website'] ?? 'phucloc.edu.vn');
        $mergedSchoolData['total_area'] = $totalArea;
        $mergedSchoolData['locations'] = $locations;

        if (!empty($data['principal_name'])) {
            $mergedSchoolData['principal'] = $data['principal_name'];
        }

        $school->storytelling_data = [
            'mergedSchool' => $mergedSchoolData,
            'components'   => $components,
            'distanceText' => $existingData['distanceText'] ?? '2.0 km',
            'durationText' => $existingData['durationText'] ?? '5 phút'
        ];
        $school->save();

        return $school;
    }

    public function getDashboardData($id, User $user): array
    {
        $this->verifyPrincipalOrAdmin();
        $school = $this->findSchool($id);

        if (!$school) {
            abort(404, 'Không tìm thấy thông tin trường học!');
        }

        if (!$user->isAdmin() && $school->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền quản trị trường học này!');
        }

        $preStoredPhotos = [];
        if ($school->image_path) {
            $preStoredPhotos[] = $school->image_path;
        }

        $components = $school->merged_components;
        if (!empty($components) && is_array($components)) {
            foreach ($components as $comp) {
                if (!empty($comp['photo']) && !in_array($comp['photo'], $preStoredPhotos)) {
                    $preStoredPhotos[] = $comp['photo'];
                }
            }
        }

        $existingPhotos = EateryPhoto::on('mysql_education')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
        if ($existingPhotos->isEmpty()) {
            $existingPhotos = EateryPhoto::on('mysql')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
        }

        if ($existingPhotos->isEmpty() && !empty($preStoredPhotos)) {
            foreach ($preStoredPhotos as $idx => $pUrl) {
                try {
                    EateryPhoto::on('mysql_education')->create([
                        'eatery_id'  => $school->id,
                        'image_path' => $pUrl,
                        'caption'    => 'Hình ảnh cơ sở vật chất & điểm trường - ' . $school->standardized_name,
                        'sort_order' => $idx + 1,
                    ]);
                } catch (\Exception $e) {
                    try {
                        EateryPhoto::on('mysql')->create([
                            'eatery_id'  => $school->id,
                            'image_path' => $pUrl,
                            'caption'    => 'Hình ảnh cơ sở vật chất & điểm trường - ' . $school->standardized_name,
                            'sort_order' => $idx + 1,
                        ]);
                    } catch (\Exception $e2) {}
                }
            }
            $photos = EateryPhoto::on('mysql_education')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
            if ($photos->isEmpty()) {
                $photos = EateryPhoto::on('mysql')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
            }
        } else {
            $photos = $existingPhotos;
        }

        $posts = EducationProgram::on('mysql_education')->where('eatery_id', $school->id)->orderBy('created_at', 'desc')->get();
        if ($posts->isEmpty()) {
            $posts = EducationProgram::on('mysql')->where('eatery_id', $school->id)->orderBy('created_at', 'desc')->get();
        }

        $currentUserId = Auth::id() ?? session('user_id');
        $currentSessionId = session()->getId();
        $postIds = $posts->pluck('id')->toArray();

        $realLikesMap = [];
        $userLikedMap = [];
        $realCommentsMap = [];

        if (!empty($postIds)) {
            $realLikesMap = CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds)
                ->selectRaw('reactionable_id, count(*) as total')
                ->groupBy('reactionable_id')
                ->pluck('total', 'reactionable_id')
                ->toArray();

            $uQuery = CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds);
            if ($currentUserId) {
                $uQuery->where('user_id', $currentUserId);
            } else if (!empty($currentSessionId)) {
                $uQuery->whereNull('user_id')->where('session_id', $currentSessionId);
            } else {
                $uQuery->whereRaw('1 = 0');
            }
            $userLikedMap = $uQuery->pluck('reactionable_id')->toArray();

            $realCommentsMap = Comment::where('commentable_type', 'post')
                ->whereIn('commentable_id', $postIds)
                ->selectRaw('commentable_id, count(*) as total')
                ->groupBy('commentable_id')
                ->pluck('total', 'commentable_id')
                ->toArray();
        }

        foreach ($posts as $p) {
            $p->real_likes_count = (int) ($realLikesMap[$p->id] ?? $p->likes_count ?? 0);
            $p->is_liked = in_array($p->id, $userLikedMap);
            $p->real_comments_count = (int) ($realCommentsMap[$p->id] ?? 0);
            $p->real_shares_count = (int) ($p->shares_count ?? 0);
        }

        $videos = $school->reviewVideos()->orderBy('created_at', 'desc')->get();
        $storyData = $school->storytelling_data ?? [];

        return compact('school', 'posts', 'photos', 'videos', 'storyData');
    }

    public function storePost(User $user, array $data, $imageFile, $imagesFiles, $videosFiles, ?string $imageUrls): array
    {
        $school = null;
        if (!empty($data['eatery_id'])) {
            $school = Eatery::on('mysql_education')->find($data['eatery_id']) ?: Eatery::on('mysql')->find($data['eatery_id']);
        }

        if (!$school && ($user->isPrincipal() || $user->role === 'principal')) {
            $school = Eatery::on('mysql_education')->where('user_id', $user->id)->first() ?: Eatery::on('mysql')->where('user_id', $user->id)->first();
        }

        $uploadedImages = [];
        $uploadedVideos = [];

        if ($imageUrls) {
            $urls = is_array($imageUrls) ? $imageUrls : json_decode($imageUrls, true);
            if (is_array($urls)) {
                foreach ($urls as $url) {
                    if (!empty($url) && is_string($url)) {
                        if (str_contains($url, '.mp4') || str_contains($url, '.mov') || str_contains($url, '.webm')) {
                            $uploadedVideos[] = $url;
                        } else {
                            $uploadedImages[] = $url;
                        }
                    }
                }
            }
        }

        $title = $data['title'] ?? 'Video Hoạt Động Đông Anh';

        if ($imageFile && $imageFile->isValid()) {
            $mime = $imageFile->getClientMimeType() ?: '';
            $isVid = str_contains($mime, 'video') || in_array(strtolower($imageFile->getClientOriginalExtension()), ['mp4', 'mov', 'avi', 'mkv', 'webm']);
            if ($isVid && YouTubeService::isConfigured()) {
                $ytResult = YouTubeService::uploadVideo($imageFile, $title, 'Video hoạt động trên DongAnh Discovery');
                if ($ytResult && !empty($ytResult['url'])) {
                    $uploadedVideos[] = $ytResult['url'];
                    $imageFile = null;
                }
            }
            if ($imageFile) {
                $path = R2Helper::upload($imageFile, 'education');
                if ($path) {
                    if ($isVid) {
                        $uploadedVideos[] = $path;
                    } else {
                        $uploadedImages[] = $path;
                    }
                }
            }
        }

        if (!empty($imagesFiles) && is_array($imagesFiles)) {
            foreach ($imagesFiles as $file) {
                if (!$file || !($file instanceof UploadedFile) || !$file->isValid()) continue;
                $mime = $file->getClientMimeType() ?: '';
                $isVid = str_contains($mime, 'video') || in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', 'mkv', 'webm']);
                if ($isVid && YouTubeService::isConfigured()) {
                    $ytResult = YouTubeService::uploadVideo($file, $title, 'Video hoạt động trên DongAnh Discovery');
                    if ($ytResult && !empty($ytResult['url'])) {
                        $uploadedVideos[] = $ytResult['url'];
                        continue;
                    }
                }
                $path = R2Helper::upload($file, 'education');
                if ($path) {
                    if ($isVid) {
                        $uploadedVideos[] = $path;
                    } else {
                        $uploadedImages[] = $path;
                    }
                }
            }
        }

        if (!empty($videosFiles) && is_array($videosFiles)) {
            foreach ($videosFiles as $file) {
                if (!$file || !($file instanceof UploadedFile) || !$file->isValid()) continue;
                if (YouTubeService::isConfigured()) {
                    $ytResult = YouTubeService::uploadVideo($file, $title, 'Video hoạt động giáo dục và học tập tại Đông Anh');
                    if ($ytResult && !empty($ytResult['url'])) {
                        $uploadedVideos[] = $ytResult['url'];
                        continue;
                    }
                }
                $path = R2Helper::upload($file, 'education');
                if ($path) {
                    $uploadedVideos[] = $path;
                }
            }
        }

        $imagePath = !empty($uploadedImages) ? $uploadedImages[0] : null;
        $videoPath = !empty($uploadedVideos) ? $uploadedVideos[0] : null;

        $post = null;
        if ($school) {
            $postData = [
                'eatery_id'    => $school->id,
                'name'         => $data['name'],
                'description'  => $data['description'],
                'duration'     => $data['duration'] ?? null,
                'tuition_fee'  => $data['tuition_fee'] ?? null,
                'image_path'   => $imagePath,
                'images'       => $uploadedImages,
                'video_path'   => $videoPath,
                'videos'       => $uploadedVideos,
            ];

            try {
                $post = EducationProgram::on('mysql_education')->create($postData);
            } catch (\Throwable $e) {
                $post = EducationProgram::on('mysql')->create($postData);
            }
        } else {
            $postData = [
                'user_id'     => $user->id,
                'name'        => $data['name'],
                'description' => $data['description'],
                'image_path'  => $imagePath,
                'images'      => $uploadedImages,
                'video_path'  => $videoPath,
                'videos'      => $uploadedVideos,
                'status'      => 'active',
            ];

            try {
                $post = Post::on('mysql_education')->create($postData);
            } catch (\Throwable $e) {
                $post = Post::on('mysql')->create($postData);
            }
        }

        try {
            NotificationService::notifyNewPost($user, $data['name'] ?? '', $data['description'] ?? '', $school->id ?? null);
        } catch (\Throwable $e) {}

        if ($post) {
            $post->all_images = $post->all_images;
            $post->formatted_created_at = 'Vừa xong';
            $post->real_likes_count = 0;
            $post->is_liked = false;
            $post->real_comments_count = 0;
            $post->real_shares_count = 0;
        }

        return [
            'post'   => $post,
            'school' => [
                'id'         => $school ? $school->id : null,
                'name'       => $school ? $school->standardized_name : $user->name,
                'image_path' => $school ? ($school->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80') : ($user->avatar ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80'),
            ]
        ];
    }

    public function updatePost($id, User $user, array $data, $imageFile, $imagesFiles): array
    {
        $post = EducationProgram::on('mysql_education')->find($id)
             ?: EducationProgram::on('mysql')->find($id)
             ?: Post::on('mysql_education')->find($id)
             ?: Post::on('mysql')->find($id);

        if (!$post) {
            throw new \InvalidArgumentException('Không tìm thấy bài viết cần cập nhật!');
        }

        $isOwner = ($user->isAdmin() || $user->role === 'admin') || (isset($post->user_id) && $post->user_id == $user->id);
        if (!$isOwner && !empty($post->eatery_id)) {
            $school = Eatery::on('mysql_education')->find($post->eatery_id) ?: Eatery::on('mysql')->find($post->eatery_id);
            if ($school && $school->user_id == $user->id) {
                $isOwner = true;
            }
        }

        if (!$isOwner) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $post->name = $data['name'];
        $post->description = $data['description'];
        if (isset($post->duration)) $post->duration = $data['duration'] ?? null;
        if (isset($post->tuition_fee)) $post->tuition_fee = $data['tuition_fee'] ?? null;

        $existingImages = is_array($post->images) ? $post->images : [];

        if (!empty($imagesFiles) && is_array($imagesFiles)) {
            $newImages = [];
            foreach ($imagesFiles as $file) {
                $path = R2Helper::upload($file, 'education');
                if ($path) {
                    $newImages[] = $path;
                }
            }
            if (!empty($newImages)) {
                $existingImages = array_merge($existingImages, $newImages);
            }
        }

        if ($imageFile) {
            $imagePath = R2Helper::upload($imageFile, 'education');
            if ($imagePath) {
                $post->image_path = $imagePath;
                if (!in_array($imagePath, $existingImages)) {
                    array_unshift($existingImages, $imagePath);
                }
            }
        }

        $post->images = $existingImages;
        if (!empty($existingImages)) {
            $post->image_path = $existingImages[0];
        }

        $post->save();

        return ['post' => $post];
    }

    public function destroyPost($id, User $user): bool
    {
        $post = EducationProgram::on('mysql_education')->find($id)
             ?: EducationProgram::on('mysql')->find($id)
             ?: Post::on('mysql_education')->find($id)
             ?: Post::on('mysql')->find($id);

        if (!$post) {
            return false;
        }

        $isOwner = ($user->isAdmin() || $user->role === 'admin') || (isset($post->user_id) && $post->user_id == $user->id);
        if (!$isOwner && !empty($post->eatery_id)) {
            $school = Eatery::on('mysql_education')->find($post->eatery_id) ?: Eatery::on('mysql')->find($post->eatery_id);
            if ($school && $school->user_id == $user->id) {
                $isOwner = true;
            }
        }

        if (!$isOwner) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $post->delete();
        return true;
    }

    public function storePhoto($eateryId, User $user, $imageFile, ?string $caption): void
    {
        $this->verifyPrincipalOrAdmin();
        $school = Eatery::on('mysql_education')->find($eateryId) ?: Eatery::on('mysql')->find($eateryId);

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $imagePath = R2Helper::upload($imageFile, 'education');
        $maxSort = EateryPhoto::where('eatery_id', $school->id)->max('sort_order') ?? 0;

        EateryPhoto::create([
            'eatery_id'  => $school->id,
            'image_path' => $imagePath,
            'caption'    => $caption ?: '',
            'sort_order' => $maxSort + 1,
        ]);
    }

    public function destroyPhoto($id, User $user): void
    {
        $this->verifyPrincipalOrAdmin();
        $photo = EateryPhoto::findOrFail($id);

        $school = Eatery::on('mysql_education')->find($photo->eatery_id) ?: Eatery::on('mysql')->find($photo->eatery_id);

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $photo->delete();
    }

    public function storeVideo($eateryId, User $user, array $data, $thumbnailFile): void
    {
        $this->verifyPrincipalOrAdmin();
        $school = Eatery::on('mysql_education')->find($eateryId) ?: Eatery::on('mysql')->find($eateryId);

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $thumbnailPath = null;
        if ($thumbnailFile) {
            $thumbnailPath = R2Helper::upload($thumbnailFile, 'education');
        }

        ReviewVideo::create([
            'eatery_id'      => $school->id,
            'user_id'        => $user->id,
            'title'          => $data['title'],
            'video_url'      => $data['video_url'],
            'video_type'     => $data['video_type'],
            'thumbnail_path' => $thumbnailPath,
            'likes_count'    => 0,
            'status'         => 'approved',
        ]);
    }

    public function destroyVideo($id, User $user): void
    {
        $this->verifyPrincipalOrAdmin();
        $video = ReviewVideo::findOrFail($id);

        $school = Eatery::on('mysql_education')->find($video->eatery_id) ?: Eatery::on('mysql')->find($video->eatery_id);

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $video->delete();
    }
}
