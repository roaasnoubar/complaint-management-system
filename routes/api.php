<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmployeeComplaintController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ComplaintProcessingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\DashboardController;
// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register',     [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login',        [AuthController::class, 'login']);
});

// Public Routes
Route::get('/authorities/{id}/ratings', [RatingController::class, 'getAuthorityRatings']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // Authorities
    Route::apiResource('authorities', AuthorityController::class)->except(['index', 'show']);
    Route::get('/authorities',         [AuthorityController::class, 'index']);
    Route::get('/authorities/{id}',    [AuthorityController::class, 'show']);

    // Departments
    Route::get('/departments',                  [DepartmentController::class, 'index']);
    Route::get('/departments/{id}',             [DepartmentController::class, 'show']);
    Route::post('/departments',                 [DepartmentController::class, 'store']);
    Route::put('/departments/{id}',             [DepartmentController::class, 'update']);
    Route::delete('/departments/{id}',          [DepartmentController::class, 'destroy']);
    Route::get('/authorities/{id}/departments', [DepartmentController::class, 'index']);

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::get('/users',                    [UserManagementController::class, 'index']);
        Route::get('/users/{id}',               [UserManagementController::class, 'show']);
        Route::put('/users/{id}/assign-role',   [UserManagementController::class, 'assignRole']);
        Route::put('/users/{id}/toggle-active', [UserManagementController::class, 'toggleActive']);
    });

    // User Complaints
    Route::post('/complaints', [ComplaintController::class, 'submitComplaint']);
    Route::get('/my-complaints',             [ComplaintController::class, 'getMyComplaints']);
    Route::post('/complaints/{id}/escalate', [ComplaintController::class, 'escalateComplaint']);
    
    // Ratings (Sprint 6)
    Route::post('/ratings',       [RatingController::class, 'store']);
    Route::get('/ratings/{id}',   [RatingController::class, 'show']);
    Route::post('/complaints/{id}/rate', [RatingController::class, 'submitRating']); // الرابط القديم إذا كنتِ تزالين تستخدمينه

    // Chat
    Route::get('/complaints/{id}/chat',  [ChatController::class, 'getChat']);
    Route::post('/complaints/{id}/chat', [ChatController::class, 'sendMessage']);
    Route::get('/chats',                 [ChatController::class, 'getAllChats']);

    // Employee Routes
    Route::prefix('employee')->group(function () {
        Route::get('/complaints',               [EmployeeComplaintController::class, 'getComplaints']);
        Route::get('/complaints/{id}',          [ComplaintProcessingController::class, 'getComplaintDetails']);
        Route::put('/complaints/{id}/status',   [ComplaintProcessingController::class, 'updateStatus']);
    });

    // General complaints list
    Route::get('/complaints', [ComplaintController::class, 'getComplaints']);

    // Notifications
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']); // عرض الكل (Pagination)
    Route::get('/latest', [NotificationController::class, 'latest']); // آخر 5 تنبيهات للـ Dropdown
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']); // الرقم الأحمر
    
    // عمليات التحديث
    Route::put('/{id}/read', [NotificationController::class, 'markAsRead']); // قراءة واحد
    Route::put('/read-all', [NotificationController::class, 'markAllAsRead']); // قراءة الكل
    
    // عمليات الحذف
    Route::delete('/clear-all', [NotificationController::class, 'deleteAll']); // حذف الكل
    Route::delete('/{id}', [NotificationController::class, 'destroy']); // حذف واحد
});
    Route::prefix('dashboard')->group(function () {
        Route::get('/statistics', [DashboardController::class, 'getStatistics']);
        Route::get('/complaints-by-authority', [DashboardController::class, 'complaintsByAuthority']);
        Route::get('/complaints-by-department', [DashboardController::class, 'complaintsByDepartment']);
        Route::get('/monthly-complaints', [DashboardController::class, 'monthlyComplaints']);
    });
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    
    // إضافي: مسار لتحديد الكل كمقروء (اختياري ومفيد جداً)
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});
    Route::get('/ping', function () {
        return response()->json(['status' => 'OK']);
    });
