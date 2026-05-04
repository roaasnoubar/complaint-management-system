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
use App\Models\Complain;
use Carbon\Carbon;

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
Route::prefix('auth')->group(function () {
    Route::post('/register',     [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login',        [AuthController::class, 'login']);
});

// انقليه إلى هنا (خارج الـ middleware) ليعمل في البوست مان بدون Token
Route::get('/escalate-complaints', function () {
    $delay = \Carbon\Carbon::now('UTC')->subMinute();

    // 1. تصعيد إلى مدير الجهة (Level 1)
    $toAuthority = \App\Models\Complain::where('assigned_level', 2)
        ->where('updated_at', '<=', $delay)
        ->with('department')
        ->get();

    foreach ($toAuthority as $complaint) {
        $complaint->update([
            'assigned_level' => 1,
            'updated_at' => now()
        ]);
    }

    // 2. تصعيد إلى مدير القسم (Level 2)
    $toManager = \App\Models\Complain::where('assigned_level', 3)
        ->where('created_at', '<=', $delay)
        ->with('department')
        ->get();

    foreach ($toManager as $complaint) {
        $complaint->update([
            'assigned_level' => 2,
            'updated_at' => now()
        ]);
    }

    $totalCount = $toAuthority->count() + $toManager->count();

    if ($totalCount > 0) {
        return response()->json([
            'status' => 'success',
            'message' => 'تمت عملية التصعيد الهرمي بنجاح',
            'summary' => [
                'total_escalated' => $totalCount,
                'sent_to_authority_L1' => $toAuthority->count(), // توضيح صريح للمستوى 1
                'sent_to_manager_L2' => $toManager->count(),    // توضيح صريح للمستوى 2
            ],
            'breakdown_by_department' => [
                'authority_level' => $toAuthority->groupBy('department.name')->map->count(),
                'manager_level' => $toManager->groupBy('department.name')->map->count(),
            ],
            // هنا نعيد البيانات لنرى التغيير بعيننا
            'data' => [
                'authority_escalations' => $toAuthority->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'new_level' => 1, // تأكيد القيمة الجديدة
                        'level_name' => 'Authority Manager',
                        'department' => $item->department->name ?? 'N/A'
                    ];
                }),
                'manager_escalations' => $toManager->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'new_level' => 2,
                        'level_name' => 'Department Manager',
                        'department' => $item->department->name ?? 'N/A'
                    ];
                })
            ]
        ], 200);
    }

    return response()->json([
        'status' => 'idle',
        'message' => 'النظام مستقر، لا توجد شكاوى تجاوزت المهلة الزمنية',
        'total_count' => 0
    ], 200);
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
    Route::prefix('employee')->middleware(['auth:sanctum', 'role:employee,dept_manager,authority_manager,admin'])->group(function () {
        Route::get('/complaints', [EmployeeComplaintController::class, 'getComplaints']);
        Route::get('/complaints/{id}', [EmployeeComplaintController::class, 'getComplaint']);
        Route::put('/complaints/{id}/status', [ComplaintProcessingController::class, 'updateStatus']);
        Route::get('/complaints', [ComplaintController::class, 'index']);
        Route::get('/complaints/{complain}', [ComplaintController::class, 'show']);
        Route::apiResource('complaints', ComplaintController::class);
    });

    // --- 4. نظام الشكاوى (المستخدم العادي) ---
    Route::post('/complaints', [ComplaintController::class, 'store']); 
    Route::get('/my-complaints', [ComplaintController::class, 'userComplaints']);

    // --- 5. نظام المحادثة (Chat API) ---
    Route::prefix('chat')->group(function () {
        Route::get('/complaints/{complainId}', [ChatController::class, 'getChat']); 
        Route::get('/full-details/{complain}', [ComplaintController::class, 'show']);
        Route::post('/send-message/{complainId}', [ChatController::class, 'sendMessage']); 
        Route::get('/all', [ChatController::class, 'getAllChats']);
        Route::post('/open/{complainId}', [ChatController::class, 'openChat']);
        Route::post('/read/{complainId}', [ChatController::class, 'markAsRead']);
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
    Route::middleware('auth:sanctum')->group(function () {

        // 1. رابط تصعيد الشكوى (من الموظف لمدير القسم)
        // POST: /api/complaints/{id}/escalate
        Route::post('/complaints/{id}/escalate', [ComplaintController::class, 'escalateToManager']);
    
        // 2. رابط فتح محادثة (من مدير القسم مع مقدم الشكوى)
        // POST: /api/conversations/open
        Route::post('/conversations/open', [ConversationController::class, 'startChat']);
        
        // 3. رابط لجلب الرسائل داخل المحادثة (للتأكد من نجاح الفتح)
        Route::get('/conversations/{id}/messages', [ConversationController::class, 'getMessages']);
    });
    

});