import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../controllers/employee/employee_controller.dart';
import '../../../bindings/employee_binding.dart';
import '../../../models/complaint_model.dart';

/// قائمة شكاوي الموظف
class EmployeeComplaintsListPage extends StatelessWidget {
  const EmployeeComplaintsListPage({super.key});

  static const Color _primary = Color(0xFF00838F);
  static const Color _dark = Color(0xFF006064);
  static const Color _background = Color(0xFFE0F7FA);

  @override
  Widget build(BuildContext context) {
    // نتأكد أن الـ Controller موجود — إن لم يكن نُنشئه هنا
    final EmployeeController controller = Get.isRegistered<EmployeeController>()
        ? Get.find<EmployeeController>()
        : Get.put(EmployeeController());

    final String statusArg =
        (Get.arguments is String ? Get.arguments as String : null) ??
        controller.currentStatus.value;

    // تحميل الشكاوي إن اختلفت الحالة
    if (controller.currentStatus.value != statusArg) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        controller.fetchComplaintsByStatus(statusArg);
      });
    }

    return Scaffold(
      backgroundColor: _background,
      appBar: _buildAppBar(statusArg, controller),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(
            child: CircularProgressIndicator(color: _primary),
          );
        }

        if (controller.errorMessage.value.isNotEmpty) {
          return _buildErrorState(controller, statusArg);
        }

        if (controller.complaints.isEmpty) {
          return _buildEmptyState(statusArg);
        }

        return _buildList(controller);
      }),
    );
  }

  // ──────────────────────────────────────────────
  // AppBar
  // ──────────────────────────────────────────────
  PreferredSizeWidget _buildAppBar(
    String status,
    EmployeeController controller,
  ) {
    return AppBar(
      backgroundColor: _dark,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: true,
      title: Text(
        _statusTitle(status),
        style: const TextStyle(
          color: Colors.white,
          fontSize: 18,
          fontWeight: FontWeight.bold,
        ),
      ),
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
        onPressed: () => Get.back(),
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.refresh_rounded, color: Colors.white),
          onPressed: controller.fetchComplaints,
          tooltip: 'تحديث',
        ),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(4),
        child: Container(
          height: 4,
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_dark, _primary]),
          ),
        ),
      ),
    );
  }

  // ──────────────────────────────────────────────
  // القائمة
  // ──────────────────────────────────────────────
  Widget _buildList(EmployeeController controller) {
    return RefreshIndicator(
      color: _primary,
      onRefresh: controller.fetchComplaints,
      child: ListView.separated(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        itemCount: controller.complaints.length,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (_, index) {
          final complaint = controller.complaints[index];
          return _ComplaintCard(
            complaint: complaint,
            onTap: () => controller.openAndProcessComplaint(complaint),
          );
        },
      ),
    );
  }

  // ──────────────────────────────────────────────
  // حالة فارغة
  // ──────────────────────────────────────────────
  Widget _buildEmptyState(String status) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 90,
            height: 90,
            decoration: BoxDecoration(
              color: _primary.withOpacity(0.08),
              shape: BoxShape.circle,
            ),
            child: Icon(
              _statusIcon(status),
              color: _primary.withOpacity(0.5),
              size: 44,
            ),
          ),
          const SizedBox(height: 20),
          Text(
            'لا توجد شكاوي ${_statusTitle(status)}',
            style: const TextStyle(
              color: Color(0xFF546E7A),
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'ستظهر الشكاوي هنا عند ورودها',
            style: TextStyle(color: Color(0xFF90A4AE), fontSize: 13),
          ),
        ],
      ),
    );
  }

  // ──────────────────────────────────────────────
  // حالة خطأ
  // ──────────────────────────────────────────────
  Widget _buildErrorState(EmployeeController controller, String status) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.cloud_off_rounded, color: Colors.red.shade300, size: 56),
            const SizedBox(height: 16),
            Text(
              controller.errorMessage.value,
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.red.shade400, fontSize: 14),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: () => controller.fetchComplaintsByStatus(status),
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('إعادة المحاولة'),
              style: ElevatedButton.styleFrom(
                backgroundColor: _primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ──────────────────────────────────────────────
  // Helpers
  // ──────────────────────────────────────────────
  String _statusTitle(String status) {
    switch (status.toLowerCase()) {
      case 'new':
      case 'pending':
        return 'الشكاوي الجديدة';
      case 'in_progress':
        return 'الشكاوي قيد المعالجة';
      case 'closed':
      case 'resolved':
        return 'الشكاوي المغلقة';
      default:
        return 'الشكاوي';
    }
  }

  IconData _statusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'new':
      case 'pending':
        return Icons.fiber_new_rounded;
      case 'in_progress':
        return Icons.pending_actions_rounded;
      case 'closed':
      case 'resolved':
        return Icons.check_circle_outline_rounded;
      default:
        return Icons.list_alt_rounded;
    }
  }
}

// ══════════════════════════════════════════════════════
// بطاقة الشكوى
// ══════════════════════════════════════════════════════
class _ComplaintCard extends StatelessWidget {
  final ComplaintModel complaint;
  final VoidCallback onTap;

  static const Color _primary = Color(0xFF00838F);

  const _ComplaintCard({required this.complaint, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final statusColor = _statusColor(complaint.status);
    final statusLabel = complaint.statusLabel;
    final statusIcon = _statusIcon(complaint.status);

    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: _primary.withOpacity(0.08),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          children: [
            // شريط الحالة
            Container(
              height: 4,
              decoration: BoxDecoration(
                color: statusColor,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(18),
                  topRight: Radius.circular(18),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: statusColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: statusColor.withOpacity(0.3),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(statusIcon, color: statusColor, size: 14),
                            const SizedBox(width: 4),
                            Text(
                              statusLabel,
                              style: TextStyle(
                                color: statusColor,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ),
                      // رقم الشكوى أو complain_number
                      Text(
                        complaint.complainNumber ??
                            'شكوى #${complaint.id ?? '--'}',
                        style: const TextStyle(
                          color: Color(0xFF90A4AE),
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    complaint.title,
                    textAlign: TextAlign.right,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Color(0xFF004D52),
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    complaint.description,
                    textAlign: TextAlign.right,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Color(0xFF78909C),
                      fontSize: 13,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 14),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (complaint.canChat)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: _primary.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.chat_bubble_outline_rounded,
                                color: _primary,
                                size: 14,
                              ),
                              SizedBox(width: 4),
                              Text(
                                'محادثة',
                                style: TextStyle(
                                  color: _primary,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        )
                      else
                        const SizedBox.shrink(),
                      Row(
                        children: [
                          if (complaint.createdAt != null)
                            Text(
                              _formatDate(complaint.createdAt!),
                              style: const TextStyle(
                                color: Color(0xFF90A4AE),
                                fontSize: 11,
                              ),
                            ),
                          if (complaint.fullName != null) ...[
                            const SizedBox(width: 8),
                            const Icon(
                              Icons.person_outline_rounded,
                              color: Color(0xFF90A4AE),
                              size: 14,
                            ),
                            const SizedBox(width: 3),
                            Text(
                              complaint.fullName!,
                              style: const TextStyle(
                                color: Color(0xFF546E7A),
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _statusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return const Color(0xFF00838F);
      case 'in progress':
        return const Color(0xFF0097A7);
      case 'resolved':
        return const Color(0xFF26A69A);
      case 'rejected':
        return Colors.red.shade400;
      default:
        return const Color(0xFF90A4AE);
    }
  }

  IconData _statusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Icons.fiber_new_rounded;
      case 'in progress':
        return Icons.pending_actions_rounded;
      case 'resolved':
        return Icons.check_circle_outline_rounded;
      default:
        return Icons.help_outline_rounded;
    }
  }

  String _formatDate(DateTime date) {
    return '${date.year}/${date.month.toString().padLeft(2, '0')}/${date.day.toString().padLeft(2, '0')}';
  }
}
