<<<<<<< HEAD
# complaint_app

A new Flutter project.

## Getting Started

This project is a starting point for a Flutter application.

A few resources to get you started if this is your first Flutter project:

- [Learn Flutter](https://docs.flutter.dev/get-started/learn-flutter)
- [Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Flutter learning resources](https://docs.flutter.dev/reference/learning-resources)

For help getting started with Flutter development, view the
[online documentation](https://docs.flutter.dev/), which offers tutorials,
samples, guidance on mobile development, and a full API reference.
=======
# نظام إدارة الشكاوى (Complaint Management System) ⚖️

هذا هو المستودع الخاص بالجانب البرمجي الخلفي (Backend) لنظام إدارة الشكاوى، تم تطويره باستخدام إطار العمل **Laravel**.

## 📋 حول المشروع
نظام متكامل يهدف إلى تسهيل عملية تقديم الشكاوى ومتابعتها، مع ميزات متقدمة تشمل:
- تقديم الشكاوى مع المرفقات (صور، فيديو، ملفات PDF).
- توزيع الشكاوى تلقائياً على الأقسام والجهات المعنية.
- نظام تصعيد (Escalation) هرمي للشكاوى في حال عدم الرد.
- تقارير وإحصائيات حول أداء الأقسام.

## 🛠 التقنيات المستخدمة
- **Framework:** Laravel 10/11
- **Language:** PHP
- **Database:** MySQL
- **Tools:** Android Studio (للتطبيق المرتبط), Jira (لإدارة المهام), VMware.

## 👥 فريق العمل
مشروع تخرج مقدم من قبل مجموعة من طلاب هندسة المعلوماتية (تخصص برمجيات).

## 🚀 تعليمات التشغيل
1. قم بعمل `git clone` للمشروع.
2. قم بتثبيت المكتبات: `composer install`.
3. قم بإنشاء ملف الإعدادات: `cp .env.example .env`.
4. قم بتوليد مفتاح التشفير: `php artisan key:generate`.
5. قم بإعداد قاعدة البيانات في ملف `.env` ثم شغل التهجير: `php artisan migrate`.
6. ابدأ السيرفر: `php artisan serve`.
>>>>>>> 3f9468b251dd26f1ddbfa008a2d04657742b9a04
