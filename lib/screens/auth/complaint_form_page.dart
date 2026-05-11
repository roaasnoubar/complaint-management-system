import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../controllers/complaint_controller.dart';

class ComplaintFormPage extends StatelessWidget {
  ComplaintFormPage({super.key});

  // استخدام Get.find إذا كان الكنترولر قد تم حقنه مسبقاً، أو Get.put إذا كانت هذه صفحة البداية
  final controller = Get.put(ComplaintController());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE0F7FA),
      appBar: AppBar(
        title: const Text(
          'تقديم شكوى جديدة',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        backgroundColor: const Color(0xFF006064),
        centerTitle: true,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildSectionTitle("البيانات الشخصية"),
            _buildTextField(
              label: "الاسم الكامل",
              hint: "الاسم الثلاثي بالعربية أو الإنجليزية",
              controller: controller.fullNameController,
            ),
            const SizedBox(height: 20),

            _buildSectionTitle("بيانات الشكوى"),
            _buildAuthorityDropdown(),
            const SizedBox(height: 12),
            _buildDepartmentDropdown(),
            const SizedBox(height: 15),
            _buildTextField(
              label: "عنوان الشكوى",
              hint: "عنوان مختصر وواضح",
              controller: controller.titleController,
            ),
            const SizedBox(height: 15),
            _buildTextField(
              label: "وصف الشكوى",
              hint: "اكتب تفاصيل شكواك هنا...",
              controller: controller.descriptionController,
              maxLines: 5,
            ),
            const SizedBox(height: 20),

            _buildSectionTitle("المرفقات (اختياري)"),
            _buildAttachmentBox(),

            const SizedBox(height: 30),
            _buildSubmitButton(),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.bold,
          color: Color(0xFF006064),
        ),
      ),
    );
  }

  Widget _buildTextField({
    required String label,
    required String hint,
    required TextEditingController controller,
    int maxLines = 1,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: Color(0xFF00838F),
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          maxLines: maxLines,
          decoration: InputDecoration(
            hintText: hint,
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 12,
            ),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.white, width: 0),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildAuthorityDropdown() {
    return Obx(() {
      return DropdownButtonFormField<int>(
        value: controller.selectedAuthorityId.value == 0
            ? null
            : controller.selectedAuthorityId.value,
        decoration: InputDecoration(
          labelText: 'الجهة',
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
        ),
        items: controller.authorities.entries
            .map(
              (e) => DropdownMenuItem<int>(value: e.key, child: Text(e.value)),
            )
            .toList(),
        onChanged: (val) {
          controller.selectedAuthorityId.value = val ?? 0;
          controller.selectedDepartmentId.value = 0;
        },
      );
    });
  }

  Widget _buildDepartmentDropdown() {
    return Obx(() {
      final departments = controller.currentDepartments;
      return DropdownButtonFormField<int>(
        // نستخدم key لإجبار الـ Dropdown على إعادة البناء عند تغيير الجهة لتصفير القيمة
        key: ValueKey(controller.selectedAuthorityId.value),
        value: controller.selectedDepartmentId.value == 0
            ? null
            : controller.selectedDepartmentId.value,
        decoration: InputDecoration(
          labelText: 'القسم',
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
        ),
        items: departments.entries
            .map(
              (e) => DropdownMenuItem<int>(value: e.key, child: Text(e.value)),
            )
            .toList(),
        onChanged: departments.isEmpty
            ? null
            : (val) => controller.selectedDepartmentId.value = val ?? 0,
      );
    });
  }

  Widget _buildAttachmentBox() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(15),
        border: Border.all(color: const Color(0xFF00838F).withOpacity(0.3)),
      ),
      child: Column(
        children: [
          const Icon(
            Icons.cloud_upload_outlined,
            size: 40,
            color: Color(0xFF00838F),
          ),
          const SizedBox(height: 8),
          Obx(() {
            final count = controller.attachmentPaths.length;
            return Text(
              count == 0
                  ? "أضف صوراً أو ملفات توضيحية"
                  : "تم اختيار $count ملف/ملفات",
              style: TextStyle(
                color: count == 0 ? Colors.grey : const Color(0xFF006064),
              ),
            );
          }),
          TextButton(
            onPressed: () => controller.pickAttachment(),
            child: const Text(
              "اختر ملفاً",
              style: TextStyle(
                color: Color(0xFF00838F),
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubmitButton() {
    return SizedBox(
      width: double.infinity,
      height: 55,
      child: Obx(
        () => ElevatedButton(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF00838F),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
          onPressed: controller.isUploading.value
              ? null
              : () => controller.submitComplaint(),
          child: controller.isUploading.value
              ? const CircularProgressIndicator(color: Colors.white)
              : const Text(
                  "تقديم الشكوى",
                  style: TextStyle(
                    fontSize: 18,
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
        ),
      ),
    );
  }
}
