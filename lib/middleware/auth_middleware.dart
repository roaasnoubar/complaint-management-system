import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../core/routes/app_routes.dart';

class AuthMiddleware extends GetMiddleware {
  @override
  int? get priority => 1;

  @override
  RouteSettings? redirect(String? route) {
    final storage = GetStorage();

    bool isLoggedIn = storage.read('isLoggedIn') ?? false;
    bool isEmailVerified = storage.read('isEmailVerified') ?? false;

    if (isLoggedIn && isEmailVerified) {
      if (route == Routes.LOGIN ||
          route == Routes.REGISTER ||
          route == Routes.OTP) {
        return const RouteSettings(name: '/dashboard');
      }
    }

    if (!isLoggedIn && route == '/dashboard') {
      return const RouteSettings(name: Routes.LOGIN);
    }

    if (isLoggedIn && !isEmailVerified && route == '/dashboard') {
      return const RouteSettings(name: Routes.OTP);
    }

    return null;
  }
}
