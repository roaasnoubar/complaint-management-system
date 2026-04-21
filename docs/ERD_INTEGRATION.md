# ERD Integration Guide

This document explains how the complaint management system components are integrated and how to use them together.

## Quick Start

### Run Migrations
```bash
php artisan migrate
```

### Seed Database
```bash
php artisan db:seed
```

### Fresh Migration with Seeds
```bash
php artisan migrate:fresh --seed
```

## Usage Examples

### Create a Complaint (Full Flow)
```php
use App\Models\{User, Complaint, Department, Authority, Attachment};

// User creates complaint with attachment
$user = User::find(1);
$department = Department::first();
$authority = Authority::first();

// Create attachment first
$attachment = Attachment::create([
    'user_id' => $user->id,
    'file_path' => 'complaints/attachment_123.pdf',
    'file_type' => 'application/pdf',
]);

// Create complaint
$complaint = Complaint::create([
    'user_id' => $user->id,
    'department_id' => $department->id,
    'current_department_id' => $department->id,
    'authority_id' => $authority->id,
    'title' => 'Street lamp not working',
    'description' => 'The lamp on Main St has been broken for 2 weeks.',
    'status' => 'pending',
]);

// Link attachment to complaint
$attachment->update(['complaint_id' => $complaint->id]);
```

### Start a Chat on a Complaint
```php
use App\Models\{Complaint, ComplainChat, ChatMessage, User};

$complaint = Complaint::find(1);
$user = User::find(1);

// Create or get chat
$chat = ComplainChat::firstOrCreate(
    ['complain_id' => $complaint->id, 'user_id' => $user->id],
    ['is_open' => true]
);

// Send message
ChatMessage::create([
    'chat_id' => $chat->id,
    'sender_id' => $user->id,
    'message' => 'When will this be resolved?',
]);
```

### Rate Authority After Resolution
```php
use App\Models\{Complaint, Rating, User};

$complaint = Complaint::find(1);
$user = $complaint->user;
$authority = $complaint->authority;

Rating::create([
    'complain_id' => $complaint->id,
    'user_id' => $user->id,
    'authority_id' => $authority->id,
    'response_speed_score' => 5,
]);
```

### Check User Permissions
```php
$user = User::with('role.permissions')->find(1);

if ($user->hasPermission('create_complaint')) {
    // User can create complaints
}
```

### Get Department Workload
```php
$department = Department::withCount(['complaints', 'currentComplaints'])->find(1);
// $department->complaints_count
// $department->current_complaints_count
```

## File Structure

```
app/Models/
├── User.php          (extends Authenticatable)
├── Role.php
├── Permission.php
├── Authority.php
├── Department.php
├── Complaint.php
├── Attachment.php
├── ComplainChat.php
├── ChatMessage.php
└── Rating.php

database/migrations/
├── 2026_02_17_000001_create_permissions_table.php
├── 2026_02_17_000002_create_roles_table.php
├── 2026_02_17_000003_create_role_permission_table.php
├── 2026_02_17_000004_create_authorities_table.php
├── 2026_02_17_000005_create_departments_table.php
├── 2026_02_17_000006_add_erd_columns_to_users_table.php
├── 2026_02_17_000007_create_attachments_table.php
├── 2026_02_17_000008_add_erd_columns_to_complaints_table.php
├── 2026_02_17_000009_create_complain_chats_table.php
├── 2026_02_17_000010_create_chat_messages_table.php
└── 2026_02_17_000011_create_ratings_table.php

database/seeders/
├── PermissionSeeder.php
├── RoleSeeder.php
├── AuthoritySeeder.php
├── DepartmentSeeder.php
└── ERDDatabaseSeeder.php

docs/
├── ERD_SCHEMA.md
└── ERD_INTEGRATION.md
```

## Role Types & Permissions

| Role            | Level | Permissions                                                  |
|-----------------|-------|--------------------------------------------------------------|
| citizen         | 1     | view_complaints, create_complaint, chat_complaint, rate_authority |
| employee        | 2     | view_complaints, update_complaint, chat_complaint            |
| department_admin| 3     | + assign_complaint, resolve_complaint, manage_departments    |
| super_admin     | 4     | All permissions                                              |
