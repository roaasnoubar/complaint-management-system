import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart'; // المكتبة المسؤولة عن حفظ التوكن

import 'bindings/initial_binding.dart';
import 'core/routes/app_pages.dart';
import 'core/theme/app_theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await GetStorage.init();

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return GetMaterialApp(
      title: 'تطبيق ادارة الشكاوي',
      debugShowCheckedModeBanner: false,

      theme: AppTheme.lightTheme,

      initialBinding: InitialBinding(),

      initialRoute:
          AppPages.INITIAL, // سيذهب للـ Splash أولاً ليفحص حالة الدخول
      getPages: AppPages.pages,

      locale: const Locale('ar', 'SA'),
      fallbackLocale: const Locale('en', 'US'),
    );
  }
}
