<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\ComplainChatController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Complaints
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
