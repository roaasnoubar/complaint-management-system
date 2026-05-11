import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../core/routes/app_routes.dart';
import '../core/storage/token_storage.dart';

class AuthMiddleware extends GetMiddleware {
  @override
  int? get priority => 1;

  @override
  RouteSettings? redirect(String? route) {
    return null;
  }
}
