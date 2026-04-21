/// Central route path index for [GetMaterialApp] / navigation.
abstract class Routes {
  Routes._();

  // —— Auth ——
  static const SPLASH = '/splash';
  static const String LOGIN = '/login';
  static const String REGISTER = '/register';
  static const String PROFILE = '/profile';

  // —— Complainant (المواطن) ——
  static const String HOME = '/home';
  static const String ADD_COMPLAINT = '/add-complaint';
  static const String MY_COMPLAINTS = '/my-complaints';
  static const String COMPLAINT_DETAILS = '/complaint-details';

  // —— Authority / employee (الجهة/الموظف) ——
  static const String AUTHORITY_DASHBOARD = '/authority-dashboard';
  static const String AUTHORITY_COMPLAINTS = '/authority-complaints';
  static const String COMPLAINT_RESPONSE = '/complaint-response';

  // —— Communication & others ——
  static const String CHAT = '/chat';
  static const String RATING = '/rating';
  static const String NOTIFICATIONS = '/notifications';
}
