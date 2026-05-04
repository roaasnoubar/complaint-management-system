/// Central route path index for [GetMaterialApp] / navigation.
abstract class Routes {
  Routes._();

  // —— Auth Section ——
  static const String SPLASH = '/splash';
  static const String LOGIN = '/login';
  static const String REGISTER = '/register';
  static const String OTP = '/otp';
  static const String PROFILE = '/profile';

  // —— Complainant Section (المواطن) ——
  static const String HOME = '/home'; 
  static const String ADD_COMPLAINT = '/add-complaint';
  static const String MY_COMPLAINTS = '/my-complaints';
  static const String COMPLAINT_DETAILS = '/complaint-details';

  // —— Authority / Employee Section (الجهة/الموظف) ——
  static const String AUTHORITY_DASHBOARD = '/authority-dashboard';
  static const String AUTHORITY_COMPLAINTS = '/authority-complaints';
  static const String COMPLAINT_RESPONSE = '/complaint-response';

  // —— Communication & Others ——
  static const String CHAT = '/chat';
  static const String RATING = '/rating';
  static const String NOTIFICATIONS = '/notifications';
}
