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
    $delay = Carbon::now('UTC')->subMinute(); 
    
    // تصعيد الشكاوى
    $updated = Complain::where('assigned_level', 3)
        ->where('created_at', '<=', $delay)
        ->update(['assigned_level' => 2, 'updated_at' => now()]);

    return "تم تصعيد $updated شكاوى بنجاح!";
});