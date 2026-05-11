class ApiConstants {
  ApiConstants._();

  // ──────────────────────────────────────────────
  // الرابط الأساسي
  // ──────────────────────────────────────────────
  static const String baseUrl = "http://192.168.111.133:8000/api";

  // ──────────────────────────────────────────────
  // Auth
  // ──────────────────────────────────────────────
  static const String register = "/auth/register";
  static const String verifyEmail = "/auth/verify-email";
  static const String login = "/auth/login";
  static const String logout = "/auth/logout";
  static const String me = "/auth/me";

  // ──────────────────────────────────────────────
  // Complaints — المواطن
  // ──────────────────────────────────────────────
  static const String storeComplaint = "/complaints";
  static const String myComplaints = "/my-complaints";
  static String complaintById(int id) => "/complaints/$id";

  // ──────────────────────────────────────────────
  // Employee & Management — الموظف والمدراء
  // الباك إند يُرجع البيانات حسب التوكن تلقائياً
  // ──────────────────────────────────────────────

  /// جلب جميع الشكاوي
  static const String allComplaints = "/employee/list";

  /// عرض تفاصيل شكوى
  static String viewComplaint(int id) => "/employee/view/$id";

  /// تغيير حالة الشكوى
  static String updateStatus(int id) => "/complaints/$id/status";

  /// الرد الرسمي
  static String respondToComplaint(int id) =>
      "/employee/complaints/$id/respond";

  /// رفض الشكوى
  static String rejectComplaint(int id) => "/complaints/$id/reject";

  /// فلترة الشكاوي حسب الحالة
  /// ⚠️ الباك إند يتوقع: "Pending" | "In Progress" | "Resolved"
  static String filterComplaints(String status) => "/complaints/filter/$status";

  // ──────────────────────────────────────────────
  // حالات الشكاوي — القيم التي يتوقعها الباك إند
  // استخدمي هذه الثوابت بدل النصوص المباشرة
  // ──────────────────────────────────────────────
  static const String statusPending = "Pending";
  static const String statusInProgress = "In Progress";
  static const String statusResolved = "Resolved";

  // ──────────────────────────────────────────────
  // Department Manager — مدير القسم
  // ──────────────────────────────────────────────
  static const String myDepartments = "/manager/my-departments";
  static const String managerStats = "/manager/statistics";
  static const String createEmployee = "/manager/create-employee";

  // ──────────────────────────────────────────────
  // Authority Manager — مدير الجهة
  // ──────────────────────────────────────────────
  static const String adminCreateUser = "/admin/create-user";
  static const String authorityDashboardStats = "/dashboard/statistics";
  static const String statsByAuthority = "/dashboard/complaints-by-authority";
  static const String statsByDepartment = "/dashboard/complaints-by-department";
  static const String monthlyStats = "/dashboard/monthly-complaints";

  // ──────────────────────────────────────────────
  // Chat
  // ──────────────────────────────────────────────
  static String openChat(int complainId) => "/chat/open/$complainId";
  static String chatHistory(int complainId) => "/chat/complaints/$complainId";
  static String sendMessage(int complainId) => "/chat/send-message/$complainId";
  static const String allChats = "/chat/all";
  static const String toggleChatStatus = "/chat/toggle-status";

  // ──────────────────────────────────────────────
  // Notifications
  // ──────────────────────────────────────────────
  static const String notifications = "/notifications";
  static const String unreadNotificationsCount = "/notifications/unread-count";

  // ──────────────────────────────────────────────
  // Escalation — تلقائي من الباك إند
  // ──────────────────────────────────────────────
  static const String runAutomaticEscalation = "/escalate-complaints";
}
