import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../controllers/employee/employee_controller.dart';
import '../../../core/routes/app_routes.dart';
import '../../../services/auth_service.dart';
import '../../../controllers/auth_controller.dart';

class EmployeeDashboardPage extends StatelessWidget {
  const EmployeeDashboardPage({super.key});

  static const Color _primary = Color(0xFF00838F);
  static const Color _dark = Color(0xFF006064);
  static const Color _background = Color(0xFFE0F7FA);

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<EmployeeController>();

    return Scaffold(
      backgroundColor: _background,
      body: SafeArea(
        child: RefreshIndicator(
          color: _primary,
          onRefresh: () async => controller.fetchComplaints(),
          child: CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              SliverToBoxAdapter(child: _buildHeader()),
              SliverToBoxAdapter(child: _buildComplaintsSection(controller)),
              const SliverToBoxAdapter(child: SizedBox(height: 32)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [_dark, _primary],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(32),
          bottomRight: Radius.circular(32),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          // زر تسجيل الخروج
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _HeaderIconButton(
                icon: Icons.logout_rounded,
                tooltip: 'تسجيل الخروج',
                onTap: () => _confirmLogout(),
              ),
              _HeaderIconButton(
                icon: Icons.notifications_none_rounded,
                tooltip: 'الإشعارات',
                onTap: () => Get.toNamed(Routes.NOTIFICATIONS),
              ),
            ],
          ),
          const SizedBox(height: 20),
          // أيقونة الموظف
          Center(
            child: Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.15),
                shape: BoxShape.circle,
                border: Border.all(
                  color: Colors.white.withOpacity(0.4),
                  width: 2,
                ),
              ),
              child: const Icon(
                Icons.badge_rounded,
                color: Colors.white,
                size: 38,
              ),
            ),
          ),
          const SizedBox(height: 12),
          const Center(
            child: Text(
              'لوحة تحكم الموظف',
              style: TextStyle(
                color: Colors.white,
                fontSize: 20,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
          ),
          const SizedBox(height: 4),
          Center(
            child: Text(
              'مرحباً، يمكنك متابعة الشكاوي المسندة إليك',
              style: TextStyle(
                color: Colors.white.withOpacity(0.8),
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildComplaintsSection(EmployeeController controller) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 28, 20, 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          // العنوان
          const Text(
            'الشكاوي المسندة إليك',
            style: TextStyle(
              color: _dark,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Container(
            width: 48,
            height: 3,
            decoration: BoxDecoration(
              color: _primary,
              borderRadius: BorderRadius.circular(8),
            ),
          ),
          const SizedBox(height: 20),
          // البطاقات الثلاث
          Row(
            children: [
              Expanded(
                child: _ComplaintCategoryCard(
                  icon: Icons.fiber_new_rounded,
                  label: 'الجديدة',
                  statusKey: 'new',
                  color: const Color(0xFF00838F),
                  onTap: () {
                    controller.fetchComplaintsByStatus('new');
                    Get.toNamed(Routes.EMPLOYEE_COMPLAINTS, arguments: 'new');
                  },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _ComplaintCategoryCard(
                  icon: Icons.pending_actions_rounded,
                  label: 'قيد المعالجة',
                  statusKey: 'in_progress',
                  color: const Color(0xFF0097A7),
                  onTap: () {
                    controller.fetchComplaintsByStatus('in_progress');
                    Get.toNamed(
                      Routes.EMPLOYEE_COMPLAINTS,
                      arguments: 'in_progress',
                    );
                  },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _ComplaintCategoryCard(
                  icon: Icons.check_circle_outline_rounded,
                  label: 'المغلقة',
                  statusKey: 'closed',
                  color: const Color(0xFF006064),
                  onTap: () {
                    controller.fetchComplaintsByStatus('closed');
                    Get.toNamed(
                      Routes.EMPLOYEE_COMPLAINTS,
                      arguments: 'closed',
                    );
                  },
                ),
              ),
            ],
          ),
          const SizedBox(height: 28),

          // ── معلومات إضافية ──
          _buildInfoCard(),
        ],
      ),
    );
  }

  // ──────────────────────────────────────────────
  // بطاقة المعلومات
  // ──────────────────────────────────────────────
  Widget _buildInfoCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
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
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          const Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Text(
                'تذكير مهم',
                style: TextStyle(
                  color: _dark,
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(width: 8),
              Icon(Icons.info_outline_rounded, color: _primary, size: 20),
            ],
          ),
          const SizedBox(height: 12),
          _buildInfoRow(
            Icons.touch_app_rounded,
            'عند فتح شكوى جديدة ستنتقل تلقائياً لـ "قيد المعالجة"',
          ),
          const SizedBox(height: 8),
          _buildInfoRow(
            Icons.timer_outlined,
            'الشكاوي التي لم تُعالج تُصعَّد تلقائياً للمستوى الأعلى',
          ),
          const SizedBox(height: 8),
          _buildInfoRow(
            Icons.chat_bubble_outline_rounded,
            'يمكنك فتح محادثة مع المواطن للحصول على معلومات إضافية',
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String text) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisAlignment: MainAxisAlignment.end,
      children: [
        Flexible(
          child: Text(
            text,
            textAlign: TextAlign.right,
            style: const TextStyle(
              color: Color(0xFF546E7A),
              fontSize: 12,
              height: 1.5,
            ),
          ),
        ),
        const SizedBox(width: 8),
        Icon(icon, color: _primary, size: 16),
      ],
    );
  }

  // ──────────────────────────────────────────────
  // تأكيد تسجيل الخروج
  // ──────────────────────────────────────────────
  void _confirmLogout() {
    Get.dialog(
      Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.logout_rounded,
                  color: Colors.red.shade400,
                  size: 30,
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'تسجيل الخروج',
                style: TextStyle(
                  color: _dark,
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'هل أنت متأكد من تسجيل الخروج؟',
                textAlign: TextAlign.center,
                style: TextStyle(color: Color(0xFF546E7A), fontSize: 13),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () => Get.back(),
                      child: Container(
                        height: 44,
                        decoration: BoxDecoration(
                          color: const Color(0xFFECEFF1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Center(
                          child: Text(
                            'إلغاء',
                            style: TextStyle(color: Color(0xFF546E7A)),
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: GestureDetector(
                      onTap: () {
                        Get.back();
                        Get.find<AuthController>().logout();
                      },
                      child: Container(
                        height: 44,
                        decoration: BoxDecoration(
                          color: Colors.red.shade400,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Center(
                          child: Text(
                            'خروج',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _HeaderIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final String tooltip;

  const _HeaderIconButton({
    required this.icon,
    required this.onTap,
    required this.tooltip,
  });

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.15),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white.withOpacity(0.3)),
          ),
          child: Icon(icon, color: Colors.white, size: 22),
        ),
      ),
    );
  }
}

class _ComplaintCategoryCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String statusKey;
  final Color color;
  final VoidCallback onTap;

  const _ComplaintCategoryCard({
    required this.icon,
    required this.label,
    required this.statusKey,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.15),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
          border: Border.all(color: color.withOpacity(0.2), width: 1.5),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 26),
            ),
            const SizedBox(height: 10),
            Text(
              label,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: color,
                fontSize: 12,
                fontWeight: FontWeight.w700,
                height: 1.3,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
