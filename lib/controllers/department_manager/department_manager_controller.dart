import 'package:get/get.dart';

import '../../models/complaint_model.dart';
import '../../models/employee_model.dart';
import '../../services/department_manager_service.dart';
import '../../core/routes/app_routes.dart';

/// Controller مدير القسم — يتحكم في جميع حالات الواجهة والمنطق
/// يُستخدم في: الداش بورد، قوائم الشكاوي، تفاصيل الشكوى، إنشاء موظف
class DepartmentManagerController extends GetxController {
  final DepartmentManagerService _service = DepartmentManagerService.instance;

  // ──────────────────────────────────────────────
  // حالات التحميل
  // ──────────────────────────────────────────────
  final RxBool isLoadingStats      = false.obs;
  final RxBool isLoadingComplaints = false.obs;
  final RxBool isLoadingAction     = false.obs; // للأزرار (إغلاق، رد، إنشاء)

  // ──────────────────────────────────────────────
  // البيانات الرئيسية
  // ──────────────────────────────────────────────
  final RxMap<String, dynamic> stats       = <String, dynamic>{}.obs;
  final RxList<ComplaintModel> complaints  = <ComplaintModel>[].obs;
  final Rx<ComplaintModel?> selectedComplaint = Rx<ComplaintModel?>(null);

  /// الحالة المحددة حالياً في الفلتر
  final RxString currentStatus = 'new'.obs;

  // ──────────────────────────────────────────────
  // بيانات إنشاء الموظف
  // ──────────────────────────────────────────────
  final RxList<Map<String, dynamic>> myDepartments = <Map<String, dynamic>>[].obs;
  final Rx<EmployeeModel?> createdEmployee = Rx<EmployeeModel?>(null);

  // ──────────────────────────────────────────────
  // رسائل الخطأ
  // ──────────────────────────────────────────────
  final RxString complaintsError = ''.obs;
  final RxString statsError      = ''.obs;

  // ──────────────────────────────────────────────
  // Lifecycle
  // ──────────────────────────────────────────────

  @override
  void onInit() {
    super.onInit();
    _loadInitialData();
  }

  /// تحميل البيانات الأولية للداش بورد
  void _loadInitialData() {
    fetchStats();
    fetchComplaintsByStatus(currentStatus.value);
  }

  // ──────────────────────────────────────────────
  // 1. الإحصائيات
  // ──────────────────────────────────────────────

  Future<void> fetchStats() async {
    try {
      isLoadingStats(true);
      statsError('');
      final result = await _service.fetchManagerStats();
      stats.assignAll(result);
    } catch (e) {
      statsError(e.toString());
    } finally {
      isLoadingStats(false);
    }
  }

  // ──────────────────────────────────────────────
  // 2. الشكاوي
  // ──────────────────────────────────────────────

  /// جلب الشكاوي حسب الحالة وتحديث الفلتر النشط
  Future<void> fetchComplaintsByStatus(String status) async {
    try {
      currentStatus(status);
      isLoadingComplaints(true);
      complaintsError('');

      final result = await _service.fetchComplaintsByStatus(status);
      complaints.assignAll(result);
    } catch (e) {
      complaintsError(e.toString());
      _showError('تعذر جلب الشكاوى');
    } finally {
      isLoadingComplaints(false);
    }
  }

  /// فتح تفاصيل شكوى — إذا كانت جديدة تنتقل تلقائياً لـ "قيد المعالجة"
  Future<void> openComplaint(ComplaintModel complaint) async {
    selectedComplaint.value = complaint;

    if (complaint.status == 'new' || complaint.status == 'pending') {
      await _autoMarkInProgress(complaint);
    }

    Get.toNamed(
      Routes.MANAGER_COMPLAINT_DETAIL,
      arguments: selectedComplaint.value,
    );
  }

  /// تحديث حالة الشكوى تلقائياً عند الفتح
  Future<void> _autoMarkInProgress(ComplaintModel complaint) async {
    try {
      await _service.updateComplaintStatus(complaint.id!, 'in_progress');
      // تحديث النموذج المحلي بدون طلب جديد
      selectedComplaint.value = _copyWithStatus(complaint, 'in_progress');
    } catch (_) {
      // لا نوقف التنقل بسبب هذا الخطأ
    }
  }

  /// إغلاق الشكوى مع الرد الرسمي
  Future<void> closeComplaint(int id, String replyText) async {
    if (replyText.trim().isEmpty) {
      _showError('يرجى كتابة الرد قبل الإغلاق');
      return;
    }

    try {
      isLoadingAction(true);
      await _service.respondToComplaint(id, replyText);

      Get.back();
      _showSuccess('تم إغلاق الشكوى وإشعار المواطن بنجاح');
      await fetchComplaintsByStatus(currentStatus.value);
    } catch (e) {
      _showError(e.toString());
    } finally {
      isLoadingAction(false);
    }
  }

  /// تحديث الحالة يدوياً (للحالات الاستثنائية)
  Future<void> updateStatus(int id, String status) async {
    try {
      isLoadingAction(true);
      await _service.updateComplaintStatus(id, status);
      await fetchComplaintsByStatus(currentStatus.value);
    } catch (e) {
      _showError(e.toString());
    } finally {
      isLoadingAction(false);
    }
  }

  // ──────────────────────────────────────────────
  // 3. فتح الشات
  // ──────────────────────────────────────────────

  /// فتح المحادثة مع المواطن الخاصة بشكوى محددة
  void openChat(ComplaintModel complaint) {
    Get.toNamed(Routes.CHAT, arguments: {
      'complaint_id': complaint.id,
      'complaint_title': complaint.title,
    });
  }

  // ──────────────────────────────────────────────
  // 4. إنشاء موظف جديد
  // ──────────────────────────────────────────────

  /// جلب الأقسام التابعة للمدير (لاستخدامها في Dropdown)
  Future<void> fetchMyDepartments() async {
    try {
      final result = await _service.fetchMyDepartments();
      myDepartments.assignAll(result);
    } catch (e) {
      _showError('تعذر جلب الأقسام');
    }
  }

  /// إنشاء موظف جديد
  Future<void> createEmployee({
    required String name,
    required String email,
    required String username,
    required String phone,
    required String password,
    required String passwordConfirmation,
    required int roleId,
    required int authorityId,
    required int departmentId,
  }) async {
    try {
      isLoadingAction(true);
      final employee = await _service.createEmployee(
        name: name,
        email: email,
        username: username,
        phone: phone,
        password: password,
        passwordConfirmation: passwordConfirmation,
        roleId: roleId,
        authorityId: authorityId,
        departmentId: departmentId,
      );

      createdEmployee.value = employee;
      Get.back();
      _showSuccess('تم إنشاء حساب ${employee.name} بنجاح');
    } catch (e) {
      _showError(e.toString());
    } finally {
      isLoadingAction(false);
    }
  }

  // ──────────────────────────────────────────────
  // Helpers
  // ──────────────────────────────────────────────

  /// نسخ الشكوى مع تغيير الحالة فقط (immutable update)
  ComplaintModel _copyWithStatus(ComplaintModel c, String status) {
    return ComplaintModel(
      id: c.id,
      userId: c.userId,
      authorityId: c.authorityId,
      departmentId: c.departmentId,
      currentDepartmentId: c.currentDepartmentId,
      attachmentsId: c.attachmentsId,
      attachments: c.attachments,
      fullName: c.fullName,
      title: c.title,
      description: c.description,
      status: status,
      isValid: c.isValid,
      createdAt: c.createdAt,
      resolvedAt: c.resolvedAt,
      assignedLevel: c.assignedLevel,
      canChat: c.canChat,
      currentLevelName: c.currentLevelName,
    );
  }

  void _showSuccess(String message) {
    Get.snackbar(
      'نجاح ✓',
      message,
      snackPosition: SnackPosition.BOTTOM,
      duration: const Duration(seconds: 3),
    );
  }

  void _showError(String message) {
    Get.snackbar(
      'خطأ',
      message,
      snackPosition: SnackPosition.BOTTOM,
      duration: const Duration(seconds: 3),
    );
  }
}
