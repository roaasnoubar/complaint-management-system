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
use App\Http\Controllers\Api\ComplainChatController;
use App\Http\Controllers\Api\RatingController;
use App\Models\Complain;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Public Routes (المسارات العامة)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register',    [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login',        [AuthController::class, 'login']);
});

Route::get('/ping', function () {
    return response()->json(['status' => 'OK', 'message' => 'Server is running']);
});


// انقليه إلى هنا (خارج الـ middleware) ليعمل في البوست مان بدون Token
// -------------------------------------------------------------------------
// مسار التصعيد التلقائي المطور (المصحح بالكامل بتوقيت دمشق وحالة Pending)
// -------------------------------------------------------------------------
Route::get('/escalate-complaints', function () {
    $now = Carbon::now('Asia/Damascus');
    
    // نطرح دقيقة واحدة وثانية إضافية لضمان تخطي أي حماية للوقت والتقاط الشكوى المنتهية فوراً
    $delay = (clone $now)->subMinute()->addSeconds(5); 

    $toAuthority = Complain::where('status', 'Pending')
        ->where('assigned_level', 2)
        ->whereNull('processed_by')
        ->where('assigned_at', '<=', $delay)
        ->with('department')
        ->get();

    foreach ($toAuthority as $complaint) {
        $complaint->update([
            'assigned_level' => 1,
            'assigned_at'    => $now, 
            'updated_at'     => $now
        ]);
    }

    // 3. تصعيد إلى مدير القسم (Level 2)
    $toManager = Complain::where('status', 'Pending')
        ->where('assigned_level', 3)
        ->whereNull('processed_by')
        ->where('assigned_at', '<=', $delay)
        ->with('department')
        ->get();

    foreach ($toManager as $complaint) {
        $complaint->update([
            'assigned_level' => 2,
            'assigned_at'    => $now, 
            'updated_at'     => $now
        ]);
    }

    $totalCount = $toAuthority->count() + $toManager->count();

    if ($totalCount > 0) {
        return response()->json([
            'status' => 'success',
            'message' => 'تمت عملية التصعيد الهرمي بتوقيت دمشق بنجاح',
            'summary' => [
                'total_escalated' => $totalCount,
                'sent_to_authority_L1' => $toAuthority->count(),
                'sent_to_manager_L2' => $toManager->count(),
            ],
            'data' => [
                'authority_escalations' => $toAuthority->map(fn($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'new_level' => 1,
                    'level_name' => 'Authority Manager',
                    'department' => $item->department->name ?? 'N/A'
                ]),
                'manager_escalations' => $toManager->map(fn($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'new_level' => 2,
                    'level_name' => 'Department Manager',
                    'department' => $item->department->name ?? 'N/A'
                ])
            ]
        ], 200);
    }

    return response()->json([
        'status' => 'idle',
        'message' => 'النظام مستقر، لا توجد شكاوى تجاوزت المهلة (1 دقيقة)',
        'current_time_damascus' => $now->format('Y-m-d H:i:s'),
        'total_count' => 0
    ], 200);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum) - المسارات المحمية
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckEscalation::class])->group(function () {
    
    // --- حساب المستخدم ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // --- 1. الأدمن العام (Super Admin) ---
    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::post('/create-user', [UserManagementController::class, 'store'])
              ->middleware('role:admin,authority_manager'); 
              
        Route::apiResource('authorities', AuthorityController::class)
              ->names('admin.authorities')
              ->middleware('role:admin');
    
        Route::get('/users', [UserManagementController::class, 'index']);
    });
 
    Route::prefix('manager')->middleware(['auth:sanctum', 'role:manager,dept_manager'])->group(function () {
        Route::get('/my-departments', [DepartmentController::class, 'index']); 
        Route::post('/create-employee', [UserManagementController::class, 'store']);
        Route::get('/statistics', [DashboardController::class, 'getAuthorityStats']);
        Route::get('/complaints', [ComplaintController::class, 'index']); 
        Route::get('/complaints/{id}', [ComplaintController::class, 'show']);
    });

    // --- 3. نظام الشكاوى (الموظف) ---
    // --- تجميع كل المسارات المحمية تحت توثيق واحد ---
    Route::middleware('auth:sanctum')->group(function () {

        // أ. مسارات الموظف (بصلاحيات خاصة)
        Route::prefix('employee')->middleware('role:manager,admin,dept_manager,employee')->group(function () {
            Route::get('/list', [EmployeeComplaintController::class, 'getComplaints']);
            Route::get('/view/{id}', [EmployeeComplaintController::class, 'getComplaint']);
            Route::apiResource('manage-complaints', ComplaintController::class)->only(['index', 'show']);
            Route::post('/complaints/{id}/respond', [ComplaintController::class, 'respond']);
        });

        // ب. مسارات المستخدم العادي (محمية بـ auth:sanctum)
        Route::get('/departments', [DepartmentController::class, 'index']); 
        Route::post('/complaints', [ComplaintController::class, 'store']); 
        Route::get('/my-complaints', [ComplaintController::class, 'userComplaints']);
        Route::get('/authorities', [AuthorityController::class, 'index']);
        Route::get('/complaints/{id}', [ComplaintController::class, 'show']); 

        // --- 5. نظام المحادثة (Chat API) ---
        Route::prefix('chat')->group(function () {
            Route::get('/complaints/{complainId}', [ChatController::class, 'getChat']); 
            Route::get('/full-details/{complain}', [ComplaintController::class, 'show']);
            Route::post('/send-message/{complainId}', [ChatController::class, 'sendMessage']); 
            Route::get('/all', [ChatController::class, 'getAllChats']);
            Route::post('/open/{complainId}', [ChatController::class, 'openChat']);
            Route::post('/read/{complainId}', [ComplainChatController::class, 'markAsRead']); 
        });

        // --- 6. الإحصائيات (Dashboard) ---
        Route::prefix('dashboard')->group(function () {
            Route::get('/statistics',                 [DashboardController::class, 'getStatistics']);
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

        Route::post('/complaints/{id}/escalate', [ComplaintController::class, 'escalateToManager']);
        
        //Route::middleware(['auth:sanctum', 'role:admin,authority_manager,dept_manager,employee'])->group(function () {
            Route::get('/complaints/filter/{status}', [ComplaintController::class, 'getComplaintsByStatus']);
            Route::post('/complaints/{id}/status', [ComplaintProcessingController::class, 'updateStatus']);
            Route::post('/complaints/{id}/reject', [ComplaintProcessingController::class, 'reject']);
        //});

        Route::post('/complains/{id}/rate', [RatingController::class, 'submitRating']);

        // رابط جلب تقييمات وتوزيع نجوم جهة معينة للـ Dashboard
        Route::get('/authorities/{id}/ratings', [RatingController::class, 'getAuthorityRatings']);
    });
    
});