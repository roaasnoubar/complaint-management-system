import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../controllers/tracking_controller.dart';
import '../../core/routes/app_routes.dart';

class TrackingScreen extends StatelessWidget {
  TrackingScreen({super.key});

  static const Color _primary    = Color(0xFF00838F);
  static const Color _dark       = Color(0xFF006064);
  static const Color _background = Color(0xFFE0F7FA);

  final TrackingController controller = Get.put(TrackingController());
  final TextEditingController idController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _background,
      appBar: AppBar(
        backgroundColor: _dark,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        title: const Text(
          'متابعة الشكوى',
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
          onPressed: () => Get.back(),
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(4),
          child: Container(
            height: 4,
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [_dark, _primary]),
            ),
          ),
        ),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            _buildSearchSection(),
            const SizedBox(height: 20),

            Expanded(
              child: Obx(() {
                if (controller.isLoading.value) {
                  return const Center(
                    child: CircularProgressIndicator(color: _primary),
                  );
                }

                final err = controller.error.value;
                if (err != null) return _buildErrorBox(err);

                final complaint = controller.complaint.value;
                if (complaint == null) return _buildInitialState();

                return ListView(
                  physics: const BouncingScrollPhysics(),
                  children: [
                    _buildComplaintCard(complaint),
                    const SizedBox(height: 16),
                    _buildStatusStepper(complaint.statusStepIndex),
                    const SizedBox(height: 16),
                    if (complaint.canChat)
                      _buildChatButton(complaint),
                  ],
                );
              }),
            ),
          ],
        ),
      ),
    );
  }


  Widget _buildSearchSection() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: _primary.withOpacity(0.1),
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
                'الاستعلام عن الشكوى',
                style: TextStyle(
                  color: _dark,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(width: 8),
              Icon(Icons.search_rounded, color: _primary, size: 20),
            ],
          ),
          const SizedBox(height: 4),
          Container(
            width: 40,
            height: 3,
            decoration: BoxDecoration(
              color: _primary,
              borderRadius: BorderRadius.circular(8),
            ),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: idController,
            keyboardType: TextInputType.number,
            textAlign: TextAlign.right,
            textDirection: TextDirection.ltr,
            style: const TextStyle(
              color: Color(0xFF37474F),
              fontSize: 15,
              fontWeight: FontWeight.w500,
            ),
            decoration: InputDecoration(
              hintText: 'أدخل رقم الشكوى',
              hintTextDirection: TextDirection.rtl,
              hintStyle: const TextStyle(
                color: Color(0xFFB0BEC5),
                fontSize: 13,
              ),
              prefixIcon: const Icon(Icons.tag_rounded, color: _primary, size: 20),
              filled: true,
              fillColor: const Color(0xFFF5FAFB),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(color: _primary.withOpacity(0.2)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(color: _primary.withOpacity(0.2)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: _primary, width: 1.5),
              ),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 14,
              ),
            ),
            onSubmitted: (_) => _onSearch(),
          ),
          const SizedBox(height: 14),
          Obx(() => GestureDetector(
                onTap: controller.isLoading.value ? null : _onSearch,
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: double.infinity,
                  height: 50,
                  decoration: BoxDecoration(
                    gradient: controller.isLoading.value
                        ? const LinearGradient(
                            colors: [Color(0xFF80CBC4), Color(0xFF80CBC4)],
                          )
                        : const LinearGradient(
                            colors: [_dark, _primary],
                            begin: Alignment.centerRight,
                            end: Alignment.centerLeft,
                          ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: controller.isLoading.value
                        ? []
                        : [
                            BoxShadow(
                              color: _primary.withOpacity(0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                  ),
                  child: Center(
                    child: controller.isLoading.value
                        ? const SizedBox(
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 2.5,
                            ),
                          )
                        : const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                'استعلام عن الحالة',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              SizedBox(width: 8),
                              Icon(Icons.search_rounded,
                                  color: Colors.white, size: 20),
                            ],
                          ),
                  ),
                ),
              )),
        ],
      ),
    );
  }

  void _onSearch() => controller.trackById(idController.text.trim());


  Widget _buildInitialState() {
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
              Icons.assignment_outlined,
              color: _primary.withOpacity(0.5),
              size: 44,
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'أدخل رقم الشكوى للاستعلام',
            style: TextStyle(
              color: Color(0xFF546E7A),
              fontSize: 15,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'ستجد الرقم في رسالة التأكيد التي وصلتك',
            style: TextStyle(color: Color(0xFF90A4AE), fontSize: 12),
          ),
        ],
      ),
    );
  }

  // ──────────────────────────────────────────────
  // بطاقة معلومات الشكوى
  // ──────────────────────────────────────────────
  Widget _buildComplaintCard(complaint) {
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: _primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  complaint.statusLabel,
                  style: const TextStyle(
                    color: _primary,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              Text(
                'شكوى #${complaint.id ?? '--'}',
                style: const TextStyle(
                  color: _dark,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFECEFF1)),
          const SizedBox(height: 14),
          _DetailRow(label: 'عنوان الشكوى', value: complaint.title),
          const SizedBox(height: 10),
          if (complaint.currentLevelName != null)
            _DetailRow(
              label: 'الجهة المعالجة',
              value: complaint.currentLevelName!,
            ),
          const SizedBox(height: 10),
          if (complaint.createdAt != null)
            _DetailRow(
              label: 'تاريخ التقديم',
              value:
                  '${complaint.createdAt!.year}/${complaint.createdAt!.month.toString().padLeft(2, '0')}/${complaint.createdAt!.day.toString().padLeft(2, '0')}',
            ),
        ],
      ),
    );
  }


  Widget _buildStatusStepper(int currentStep) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: _primary.withOpacity(0.07),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Stepper(
        physics: const NeverScrollableScrollPhysics(),
        currentStep: currentStep.clamp(0, 2),
        controlsBuilder: (_, __) => const SizedBox.shrink(),
        connectorColor: MaterialStateProperty.resolveWith(
          (states) => states.contains(MaterialState.selected)
              ? _primary
              : const Color(0xFFB0BEC5),
        ),
        steps: [
          _buildStep(
            title: 'تم الاستلام',
            subtitle: 'تم استلام شكواك بنجاح',
            isActive: currentStep >= 0,
            isComplete: currentStep > 0,
            icon: Icons.inbox_rounded,
          ),
          _buildStep(
            title: 'قيد المعالجة',
            subtitle: 'يتم العمل على معالجة شكواك',
            isActive: currentStep >= 1,
            isComplete: currentStep > 1,
            icon: Icons.pending_actions_rounded,
          ),
          _buildStep(
            title: 'تم الإغلاق',
            subtitle: 'تمت معالجة شكواك',
            isActive: currentStep >= 2,
            isComplete: currentStep == 2,
            icon: Icons.check_circle_rounded,
          ),
        ],
      ),
    );
  }

  Step _buildStep({
    required String title,
    required String subtitle,
    required bool isActive,
    required bool isComplete,
    required IconData icon,
  }) {
    return Step(
      title: Text(
        title,
        style: TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.bold,
          color: isActive ? _dark : const Color(0xFF90A4AE),
        ),
      ),
      subtitle: Text(
        subtitle,
        style: TextStyle(
          fontSize: 11,
          color: isActive ? const Color(0xFF546E7A) : const Color(0xFFB0BEC5),
        ),
      ),
      content: const SizedBox.shrink(),
      isActive: isActive,
      state: isComplete ? StepState.complete : StepState.indexed,
    );
  }


  Widget _buildChatButton(complaint) {
    return GestureDetector(
      onTap: () => Get.toNamed(
        Routes.CHAT,
        arguments: {
          'complaint_id': complaint.id,
          'complaint_title': complaint.title,
        },
      ),
      child: Container(
        width: double.infinity,
        height: 52,
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [_dark, _primary],
            begin: Alignment.centerRight,
            end: Alignment.centerLeft,
          ),
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: _primary.withOpacity(0.3),
              blurRadius: 8,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: const Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'عرض الرسائل والتوضيحات',
              style: TextStyle(
                color: Colors.white,
                fontSize: 15,
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(width: 10),
            Icon(Icons.chat_bubble_outline_rounded,
                color: Colors.white, size: 20),
          ],
        ),
      ),
    );
  }


  Widget _buildErrorBox(String message) {
    return Center(
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.red.shade50,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.red.shade200),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.error_outline_rounded,
                color: Colors.red.shade400, size: 40),
            const SizedBox(height: 12),
            Text(
              message,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.red.shade700,
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 16),
            GestureDetector(
              onTap: () => controller.clearError(),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: _primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Text(
                  'حاول مرة أخرى',
                  style: TextStyle(
                    color: _primary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}


class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.left,
            style: const TextStyle(
              color: Color(0xFF37474F),
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        const SizedBox(width: 16),
        Text(
          label,
          style: const TextStyle(
            color: Color(0xFF90A4AE),
            fontSize: 13,
          ),
        ),
      ],
    );
  }
}
