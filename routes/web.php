<?php
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthorityController;
use App\Http\Controllers\Api\ComplainChatController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController; 
use Illuminate\Support\Facades\Route;
use App\Models\Complain;
use Carbon\Carbon;

// تأكدي أن الكنترولر مستدعى صح
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ... باقي الراوتات ستعمل الآن لأننا حدثنا الـ use فوق
Route::resource('complaints', ComplaintController::class);
// Attachments (nested under complaints)
Route::post('attachments', [AttachmentController::class, 'store'])->name('attachments.store');
Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

// Chat
Route::get('complaints/{complaint}/chat', [ComplainChatController::class, 'show'])->name('chats.show');
Route::post('chat/messages', [ComplainChatController::class, 'storeMessage'])->name('chats.messages.store');
Route::post('chats/{chat}/close', [ComplainChatController::class, 'close'])->name('chats.close');

// Ratings
Route::post('ratings', [RatingController::class, 'store'])->name('ratings.store');

// Users
Route::resource('users', UserController::class);

// Departments
Route::resource('departments', DepartmentController::class);

// Authorities
Route::resource('authorities', AuthorityController::class);

// Roles
Route::resource('roles', RoleController::class);

// Permissions
Route::resource('permissions', PermissionController::class);
Route::get('/run-logic', function () {
    $now = Carbon::now('UTC');
    $delay = $now->copy()->subMinute(); // حد الدقيقة الواحدة
    
    // 1. تصعيد من الموظف (3) إلى مدير القسم (2)
    $toManager = Complain::where('assigned_level', 3)
        ->where('assigned_at', '<=', $delay) // استخدمنا assigned_at لضمان الدقة
        ->update([
            'assigned_level' => 2,
            'assigned_at' => $now, // تحديث الوقت لتبدأ دقيقة المدير الجديد من الآن
            'updated_at' => $now
        ]);

    // 2. تصعيد من مدير القسم (2) إلى مدير الجهة (1)
    $toAuthority = Complain::where('assigned_level', 2)
        ->where('assigned_at', '<=', $delay)
        ->update([
            'assigned_level' => 1,
            'assigned_at' => $now,
            'updated_at' => $now
        ]);

    return response()->json([
        'status' => 'success',
        'message' => 'تمت عملية التصعيد بنجاح',
        'escalated_to_manager' => $toManager,
        'escalated_to_authority' => $toAuthority,
        'current_time' => $now->toDateTimeString()
    ]);
});