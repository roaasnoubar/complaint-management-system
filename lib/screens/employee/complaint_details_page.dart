import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../controllers/employee/employee_controller.dart';
import '../../../models/complaint_model.dart';
import '../../../core/routes/app_routes.dart';

class ComplaintDetailsPage extends StatefulWidget {
  const ComplaintDetailsPage({super.key});

  @override
  State<ComplaintDetailsPage> createState() => _ComplaintDetailsPageState();
}

class _ComplaintDetailsPageState extends State<ComplaintDetailsPage> {
  static const Color _primary = Color(0xFF00838F);
  static const Color _dark = Color(0xFF006064);
  static const Color _background = Color(0xFFE0F7FA);

  final TextEditingController _replyController = TextEditingController();
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  late final EmployeeController _controller;
  late final ComplaintModel _complaint;

  @override
  void initState() {
    super.initState();
    _controller = Get.find<EmployeeController>();
    _complaint = Get.arguments as ComplaintModel;
  }

  @override
  void dispose() {
    _replyController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _background,
      appBar: _buildAppBar(),
      body: Column(
        children: [
          Expanded(
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  _buildStatusBanner(),
                  const SizedBox(height: 16),
                  _buildInfoCard(),
                  const SizedBox(height: 16),
                  _buildDescriptionCard(),
                  if (_complaint.attachments != null &&
                      _complaint.attachments!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    _buildAttachmentsCard(),
                  ],
                  const SizedBox(height: 16),
                  _buildMetaCard(),
                  if (_complaint.status != 'closed' &&
                      _complaint.status != 'resolved') ...[
                    const SizedBox(height: 20),
                    _buildReplySection(),
                  ],
                  const SizedBox(height: 100),
                ],
              ),
            ),
          ),
          _buildBottomActions(),
        ],
      ),
    );
  }

  // ──────────────────────────────────────────────
  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: _dark,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: true,
      title: Text(
        'شكوى #${_complaint.id ?? '--'}',
        style: const TextStyle(
          color: Colors.white,
          fontSize: 17,
          fontWeight: FontWeight.bold,
        ),
      ),
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
        onPressed: () => Get.back(),
      ),
      actions: [
        if (_complaint.canChat)
          IconButton(
            icon: const Icon(
              Icons.chat_bubble_outline_rounded,
              color: Colors.white,
            ),
            onPressed: _openChat,
            tooltip: 'فتح المحادثة',
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
  Widget _buildStatusBanner() {
    final color = _statusColor(_complaint.status);
    final label = _statusLabel(_complaint.status);
    final icon = _statusIcon(_complaint.status);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withOpacity(0.3), width: 1.5),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: 15,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(width: 10),
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: color.withOpacity(0.15),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 20),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard() {
    return _SectionCard(
      title: 'معلومات الشكوى',
      icon: Icons.info_outline_rounded,
      children: [
        _InfoRow(label: 'عنوان الشكوى', value: _complaint.title),
        _InfoRow(
          label: 'مقدم الشكوى',
          value: _complaint.fullName ?? 'غير محدد',
        ),
        _InfoRow(
          label: 'المستوى الحالي',
          value: _complaint.currentLevelName ?? '--',
        ),
        _InfoRow(
          label: 'يمكن فتح محادثة',
          value: _complaint.canChat ? 'نعم' : 'لا',
          valueColor: _complaint.canChat ? _primary : Colors.grey,
        ),
      ],
    );
  }

  Widget _buildDescriptionCard() {
    return _SectionCard(
      title: 'تفاصيل الشكوى',
      icon: Icons.description_outlined,
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFFE0F7FA),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Text(
            _complaint.description,
            textAlign: TextAlign.right,
            style: const TextStyle(
              color: Color(0xFF37474F),
              fontSize: 14,
              height: 1.7,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildAttachmentsCard() {
    return _SectionCard(
      title: 'المرفقات',
      icon: Icons.attach_file_rounded,
      children: [
        Wrap(
          spacing: 8,
          runSpacing: 8,
          alignment: WrapAlignment.end,
          children: _complaint.attachments!.map((url) {
            return Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: _primary.withOpacity(0.08),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: _primary.withOpacity(0.2)),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'عرض المرفق',
                    style: TextStyle(
                      color: _primary,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  SizedBox(width: 6),
                  Icon(Icons.open_in_new_rounded, color: _primary, size: 14),
                ],
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Widget _buildMetaCard() {
    return _SectionCard(
      title: 'التواريخ',
      icon: Icons.calendar_today_outlined,
      children: [
        _InfoRow(
          label: 'تاريخ التقديم',
          value: _complaint.createdAt != null
              ? _formatDate(_complaint.createdAt!)
              : '--',
        ),
        _InfoRow(
          label: 'تاريخ الإغلاق',
          value: _complaint.resolvedAt != null
              ? _formatDate(_complaint.resolvedAt!)
              : 'لم يتم الإغلاق بعد',
          valueColor: _complaint.resolvedAt != null
              ? _primary
              : const Color(0xFF90A4AE),
        ),
      ],
    );
  }

  Widget _buildReplySection() {
    return Form(
      key: _formKey,
      child: _SectionCard(
        title: 'الرد الرسمي',
        icon: Icons.rate_review_outlined,
        children: [
          TextFormField(
            controller: _replyController,
            maxLines: 5,
            textAlign: TextAlign.right,
            textDirection: TextDirection.rtl,
            decoration: InputDecoration(
              hintText: 'اكتب ردك الرسمي على الشكوى هنا...',
              hintTextDirection: TextDirection.rtl,
              hintStyle: const TextStyle(
                color: Color(0xFFB0BEC5),
                fontSize: 13,
              ),
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
              errorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.red),
              ),
              contentPadding: const EdgeInsets.all(16),
            ),
            validator: (value) {
              if (value == null || value.trim().isEmpty) {
                return 'يرجى كتابة الرد قبل الإغلاق';
              }
              if (value.trim().length < 10) {
                return 'الرد قصير جداً — أضف المزيد من التفاصيل';
              }
              return null;
            },
          ),
        ],
      ),
    );
  }

  Widget _buildBottomActions() {
    final isClosed =
        _complaint.status == 'closed' || _complaint.status == 'resolved';

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 16,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: isClosed
          ? _buildClosedState()
          : Row(
              children: [
                if (_complaint.canChat) ...[
                  Expanded(
                    flex: 2,
                    child: _ActionButton(
                      label: 'محادثة',
                      icon: Icons.chat_bubble_outline_rounded,
                      color: const Color(0xFF0097A7),
                      outlined: true,
                      onTap: _openChat,
                    ),
                  ),
                  const SizedBox(width: 10),
                ],
                Expanded(
                  flex: 3,
                  child: Obx(
                    () => _ActionButton(
                      label: 'إغلاق الشكوى',
                      icon: Icons.check_circle_outline_rounded,
                      color: _dark,
                      isLoading: _controller.isLoadingAction.value,
                      onTap: _onCloseComplaint,
                    ),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildClosedState() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 14),
      decoration: BoxDecoration(
        color: _primary.withOpacity(0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: _primary.withOpacity(0.2)),
      ),
      child: const Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            'تم إغلاق هذه الشكوى',
            style: TextStyle(
              color: _primary,
              fontSize: 15,
              fontWeight: FontWeight.bold,
            ),
          ),
          SizedBox(width: 8),
          Icon(Icons.check_circle_rounded, color: _primary, size: 20),
        ],
      ),
    );
  }

  void _onCloseComplaint() {
    if (!_formKey.currentState!.validate()) return;

    Get.dialog(
      _ConfirmDialog(
        title: 'تأكيد إغلاق الشكوى',
        message: 'سيتم إغلاق الشكوى وإرسال إشعار للمواطن.\nهل أنت متأكد؟',
        confirmLabel: 'نعم، أغلق الشكوى',
        onConfirm: () {
          Get.back();
          _controller.closeComplaint(
            _complaint.id!,
            _replyController.text.trim(),
          );
        },
      ),
    );
  }

  void _openChat() {
    _controller.openChat(_complaint);
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'new':
      case 'pending':
        return const Color(0xFF00838F);
      case 'in_progress':
        return const Color(0xFF0097A7);
      case 'closed':
      case 'resolved':
        return const Color(0xFF26A69A);
      default:
        return const Color(0xFF90A4AE);
    }
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'new':
      case 'pending':
        return 'جديدة';
      case 'in_progress':
        return 'قيد المعالجة';
      case 'closed':
      case 'resolved':
        return 'مغلقة';
      default:
        return status;
    }
  }

  IconData _statusIcon(String status) {
    switch (status) {
      case 'new':
      case 'pending':
        return Icons.fiber_new_rounded;
      case 'in_progress':
        return Icons.pending_actions_rounded;
      case 'closed':
      case 'resolved':
        return Icons.check_circle_rounded;
      default:
        return Icons.help_outline_rounded;
    }
  }

  String _formatDate(DateTime date) {
    return '${date.year}/${date.month.toString().padLeft(2, '0')}/${date.day.toString().padLeft(2, '0')}';
  }
}

class _SectionCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final List<Widget> children;

  static const Color _primary = Color(0xFF00838F);
  static const Color _dark = Color(0xFF006064);

  const _SectionCard({
    required this.title,
    required this.icon,
    required this.children,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: _primary.withOpacity(0.07),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: _primary.withOpacity(0.05),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(18),
                topRight: Radius.circular(18),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: _dark,
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(width: 8),
                Icon(icon, color: _primary, size: 18),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: children,
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? valueColor;

  const _InfoRow({required this.label, required this.value, this.valueColor});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.left,
              style: TextStyle(
                color: valueColor ?? const Color(0xFF37474F),
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          const SizedBox(width: 16),
          Text(
            label,
            style: const TextStyle(color: Color(0xFF90A4AE), fontSize: 13),
          ),
        ],
      ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;
  final bool outlined;
  final bool isLoading;

  const _ActionButton({
    required this.label,
    required this.icon,
    required this.color,
    required this.onTap,
    this.outlined = false,
    this.isLoading = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: isLoading ? null : onTap,
      child: Container(
        height: 52,
        decoration: BoxDecoration(
          color: outlined ? Colors.transparent : color,
          borderRadius: BorderRadius.circular(14),
          border: outlined ? Border.all(color: color, width: 1.5) : null,
          boxShadow: outlined
              ? null
              : [
                  BoxShadow(
                    color: color.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
        ),
        child: Center(
          child: isLoading
              ? SizedBox(
                  width: 22,
                  height: 22,
                  child: CircularProgressIndicator(
                    color: outlined ? color : Colors.white,
                    strokeWidth: 2.5,
                  ),
                )
              : Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      label,
                      style: TextStyle(
                        color: outlined ? color : Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Icon(
                      icon,
                      color: outlined ? color : Colors.white,
                      size: 18,
                    ),
                  ],
                ),
        ),
      ),
    );
  }
}

class _ConfirmDialog extends StatelessWidget {
  final String title;
  final String message;
  final String confirmLabel;
  final VoidCallback onConfirm;

  static const Color _primary = Color(0xFF00838F);
  static const Color _dark = Color(0xFF006064);

  const _ConfirmDialog({
    required this.title,
    required this.message,
    required this.confirmLabel,
    required this.onConfirm,
  });

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: _primary.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.check_circle_outline_rounded,
                color: _primary,
                size: 34,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: _dark,
                fontSize: 17,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Color(0xFF546E7A),
                fontSize: 13,
                height: 1.6,
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => Get.back(),
                    child: Container(
                      height: 46,
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
                  flex: 2,
                  child: GestureDetector(
                    onTap: onConfirm,
                    child: Container(
                      height: 46,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [_dark, _primary],
                        ),
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: _primary.withOpacity(0.3),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: Center(
                        child: Text(
                          confirmLabel,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
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
    );
  }
}
