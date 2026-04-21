import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../screens/auth/login_screen.dart';
import '../../screens/home/home_screen.dart';
import 'app_routes.dart';
import '../../screens/splash/splash_screen.dart';

/// GetX [GetPage] registry; swap [_DevPlaceholder] routes for real screens as you build them.
abstract class AppPages {
  AppPages._();

  static const String INITIAL = Routes.SPLASH;

  static final List<GetPage<dynamic>> pages = <GetPage<dynamic>>[
    // Auth
    GetPage<void>(name: Routes.SPLASH, page: () => const SplashScreen()),

    GetPage<void>(name: Routes.LOGIN, page: () => const LoginScreen()),
    GetPage<void>(
      name: Routes.REGISTER,
      page: () => const _DevPlaceholder(Routes.REGISTER),
    ),
    GetPage<void>(
      name: Routes.PROFILE,
      page: () => const _DevPlaceholder(Routes.PROFILE),
    ),
    // Complainant
    GetPage<void>(name: Routes.HOME, page: () => HomeScreen()),
    GetPage<void>(
      name: Routes.ADD_COMPLAINT,
      page: () => const _DevPlaceholder(Routes.ADD_COMPLAINT),
    ),
    GetPage<void>(
      name: Routes.MY_COMPLAINTS,
      page: () => const _DevPlaceholder(Routes.MY_COMPLAINTS),
    ),
    GetPage<void>(
      name: Routes.COMPLAINT_DETAILS,
      page: () => const _DevPlaceholder(Routes.COMPLAINT_DETAILS),
    ),
    // Authority / employee
    GetPage<void>(
      name: Routes.AUTHORITY_DASHBOARD,
      page: () => const _DevPlaceholder(Routes.AUTHORITY_DASHBOARD),
    ),
    GetPage<void>(
      name: Routes.AUTHORITY_COMPLAINTS,
      page: () => const _DevPlaceholder(Routes.AUTHORITY_COMPLAINTS),
    ),
    GetPage<void>(
      name: Routes.COMPLAINT_RESPONSE,
      page: () => const _DevPlaceholder(Routes.COMPLAINT_RESPONSE),
    ),
    // Communication & others
    GetPage<void>(
      name: Routes.CHAT,
      page: () => const _DevPlaceholder(Routes.CHAT),
    ),
    GetPage<void>(
      name: Routes.RATING,
      page: () => const _DevPlaceholder(Routes.RATING),
    ),
    GetPage<void>(
      name: Routes.NOTIFICATIONS,
      page: () => const _DevPlaceholder(Routes.NOTIFICATIONS),
    ),
  ];
}

/// Temporary full-screen stub for routes not yet implemented.
class _DevPlaceholder extends StatelessWidget {
  const _DevPlaceholder(this.routePath);

  final String routePath;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(routePath)),
      body: Center(
        child: Text(
          routePath,
          style: Theme.of(context).textTheme.titleMedium,
          textAlign: TextAlign.center,
        ),
      ),
    );
  }
}
