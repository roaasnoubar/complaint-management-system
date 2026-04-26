<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeComplaintController;
use App\Http\Controllers\Api\AuthorityController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\ComplaintProcessingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\ChatController;

/*
|--------------------------------------------------------------------------
| Public Routes (المسارات العامة)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register',     [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login',         [AuthController::class, 'login']);
});

Route::get('/ping', function () {
    return response()->json(['status' => 'OK', 'message' => 'Server is running']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum) - المسارات المحمية
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- حساب المستخدم ---
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // --- 1. الأدمن العام (Super Admin) ---
    Route::prefix('admin')->group(function () {
        Route::post('/create-user', [UserManagementController::class, 'store'])
             ->middleware('role:admin,manager'); 
             
        Route::apiResource('authorities', AuthorityController::class)->middleware('role:admin');
        Route::get('/users', [UserManagementController::class, 'index']);
    });

    // --- 2. مدير الجهة (Authority Management) ---
    Route::prefix('manager')->middleware(['role:manager'])->group(function () {
        Route::get('/my-departments', [DepartmentController::class, 'index']); 
        
        // تعديل: تم توجيه الطلب إلى UserManagementController لأن AuthorityManager غير موجود
        Route::post('/create-employee', [UserManagementController::class, 'store']);
        
        Route::get('/statistics', [DashboardController::class, 'getAuthorityStats']);
    });

    // --- 3. نظام الشكاوى (الموظف) ---
    Route::prefix('employee')->middleware(['role:employee'])->group(function () {
        Route::get('/complaints', [EmployeeComplaintController::class, 'getComplaints']);
        Route::get('/complaints/{id}', [EmployeeComplaintController::class, 'getComplaint']);
        Route::put('/complaints/{id}/status', [ComplaintProcessingController::class, 'updateStatus']);
    });

    // --- 4. نظام الشكاوى (المستخدم العادي) ---
    Route::post('/complaints', [ComplaintController::class, 'store']); 
    Route::get('/my-complaints', [ComplaintController::class, 'userComplaints']);

    // --- 5. نظام المحادثة (Chat API) ---
    Route::prefix('chat')->group(function () {
        Route::get('/complaints/{complainId}', [ChatController::class, 'getChat']); 
        Route::post('/send-message/{complainId}', [ChatController::class, 'sendMessage']); 
        Route::get('/all', [ChatController::class, 'getAllChats']);
        
        // المسار المطلوب تعديله لفتح المحادثة
        Route::post('/open/{complainId}', [ChatController::class, 'openChat']);
    });

    // --- 6. الإحصائيات (Dashboard) ---
    Route::prefix('dashboard')->group(function () {
        Route::get('/statistics',                [DashboardController::class, 'getStatistics']);
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