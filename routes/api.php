<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmployeeComplaintController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ComplaintProcessingController;

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
    Route::get('/authorities',         [AuthorityController::class, 'index']);
    Route::get('/authorities/{id}',    [AuthorityController::class, 'show']);
    Route::post('/authorities',        [AuthorityController::class, 'store']);
    Route::put('/authorities/{id}',    [AuthorityController::class, 'update']);
    Route::delete('/authorities/{id}', [AuthorityController::class, 'destroy']);

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
    Route::post('/complaints',               [ComplaintController::class, 'submitComplaint']);
    Route::get('/my-complaints',             [ComplaintController::class, 'getMyComplaints']);
    Route::post('/complaints/{id}/escalate', [ComplaintController::class, 'escalateComplaint']);
    Route::post('/complaints/{id}/rate',     [RatingController::class, 'submitRating']);

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
});