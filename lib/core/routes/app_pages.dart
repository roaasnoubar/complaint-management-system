import 'package:flutter/material.dart';
import 'package:get/get.dart';

// Routes & Middleware
import 'app_routes.dart';
import '../../middleware/auth_middleware.dart';

// Screens
import '../../screens/auth/login_screen.dart';
import '../../screens/auth/register_screen.dart';
import '../../screens/auth/otp_screen.dart';
import '../../screens/home/home_screen.dart';
import '../../screens/splash/splash_screen.dart';
import '../../screens/auth/dashboard_view.dart';
import '../../bindings/dashboard_binding.dart';

abstract class AppPages {
  AppPages._();

  static const String INITIAL = Routes.SPLASH;

  static final List<GetPage<dynamic>> pages = <GetPage<dynamic>>[
    GetPage<void>(name: Routes.SPLASH, page: () => const SplashScreen()),

    GetPage<void>(
      name: Routes.LOGIN,
      page: () => const LoginScreen(),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(
      name: Routes.REGISTER,
      page: () => const RegisterScreen(),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(name: Routes.OTP, page: () => OtpScreen()),

    GetPage(
      name: '/dashboard',
      page: () => const DashboardView(),
      binding: DashboardBinding(),
      middlewares: [AuthMiddleware()],
      transition: Transition.fadeIn,
    ),

    GetPage<void>(
      name: Routes.HOME,
      page: () => HomeScreen(),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(
      name: Routes.PROFILE,
      page: () => const _DevPlaceholder(Routes.PROFILE),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(
      name: Routes.ADD_COMPLAINT,
      page: () => const _DevPlaceholder(Routes.ADD_COMPLAINT),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(
      name: Routes.MY_COMPLAINTS,
      page: () => const _DevPlaceholder(Routes.MY_COMPLAINTS),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(
      name: Routes.AUTHORITY_DASHBOARD,
      page: () => const _DevPlaceholder(Routes.AUTHORITY_DASHBOARD),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(
      name: Routes.CHAT,
      page: () => const _DevPlaceholder(Routes.CHAT),
      middlewares: [AuthMiddleware()],
    ),

    GetPage<void>(
      name: Routes.NOTIFICATIONS,
      page: () => const _DevPlaceholder(Routes.NOTIFICATIONS),
      middlewares: [AuthMiddleware()],
    ),
  ];
}

class _DevPlaceholder extends StatelessWidget {
  const _DevPlaceholder(this.routePath);
  final String routePath;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(routePath), centerTitle: true),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.construction, size: 80, color: Colors.orange),
            const SizedBox(height: 20),
            Text(
              "جاري العمل على واجهة:\n$routePath",
              style: Theme.of(context).textTheme.titleMedium,
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
