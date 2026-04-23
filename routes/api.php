<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeComplaintController;
use App\Http\Controllers\Api\AuthorityController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\ComplaintProcessingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\ComplainChatController;
/*
|--------------------------------------------------------------------------
| Public Routes (لا تحتاج تسجيل دخول)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register',     [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login',        [AuthController::class, 'login']);
});

Route::get('/ping', function () {
    return response()->json(['status' => 'OK', 'message' => 'Server is running']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (تحتاج تسجيل دخول - Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- ملف المستخدم الشخصي ---
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
        
    });

    // --- 1. الأدمن العام (Super Admin) ---
    Route::prefix('admin')->group(function () {
        Route::post('/create-employee', [AuthController::class, 'registerEmployee']);
        Route::apiResource('authorities', AuthorityController::class);
        Route::get('/users',                    [UserManagementController::class, 'index']);
        Route::put('/users/{id}/assign-role',   [UserManagementController::class, 'assignRole']);
        Route::put('/users/{id}/toggle-active', [UserManagementController::class, 'toggleActive']);
    });

    // --- 2. مدير الجهة والأقسام (Management) ---
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/authorities/{id}/departments', [DepartmentController::class, 'index']);

    // --- 3. نظام الشكاوى الأساسي (User Complaints) ---
    // هذه المسارات هي التي جربناها وتعمل بنجاح في بوستمان
    Route::post('/complaints',            [ComplaintController::class, 'store']);
    Route::get('/my-complaints',          [ComplaintController::class, 'index']); 
    Route::get('/complaints/{complain}',  [ComplaintController::class, 'show']);
    Route::post('/complaints/{complain}/rate', [ComplaintController::class, 'rateAuthority']);
    Route::post('/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);
    // --- 4. معالجة الشكاوى (للموظفين والمدراء) ---
    Route::prefix('employee')->group(function () {
        Route::get('/complaints',               [EmployeeComplaintController::class, 'getComplaints']);
        Route::put('/complaints/{id}/status',   [ComplaintProcessingController::class, 'updateStatus']);
    });

    // --- 5. نظام المحادثة (Chat API) ---
    // تم تحديثه ليتناسب مع الكنترولر الذي قمنا بتحويله لـ API
    Route::prefix('chat')->group(function () {
        Route::get('/complaints/{complain}', [ComplainChatController::class, 'show']); // جلب المحادثة ورسائلها
        Route::post('/send-message',          [ComplainChatController::class, 'storeMessage']); // إرسال رسالة
        Route::put('/{chat}/close',          [ComplainChatController::class, 'close']); // إغلاق المحادثة
    });

    // --- 6. الإحصائيات ولوحة التحكم (Dashboard) ---
    Route::prefix('dashboard')->group(function () {
        Route::get('/statistics',               [DashboardController::class, 'getStatistics']);
        Route::get('/complaints-by-authority',   [DashboardController::class, 'complaintsByAuthority']);
        Route::get('/complaints-by-department',  [DashboardController::class, 'complaintsByDepartment']);
        Route::get('/monthly-complaints',        [DashboardController::class, 'monthlyComplaints']);
    });

    // --- 7. التنبيهات (Notifications) ---
    Route::prefix('notifications')->group(function () {
        Route::get('/',            [NotificationController::class, 'index']);
        Route::get('/latest',      [NotificationController::class, 'latest']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/read-all',    [NotificationController::class, 'markAllAsRead']);
        Route::put('/{id}/read',   [NotificationController::class, 'markAsRead']);
        Route::delete('/clear-all', [NotificationController::class, 'deleteAll']);
        Route::delete('/{id}',      [NotificationController::class, 'destroy']);
    });
});