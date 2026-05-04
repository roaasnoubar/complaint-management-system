import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../controllers/complaint_controller.dart';

class ComplaintFormPage extends StatelessWidget {
  final controller = Get.put(ComplaintController());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE0F7FA),
      appBar: AppBar(
        title: const Text(
          'تقديم شكوى جديدة',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: const Color(0xFF006064),
        centerTitle: true,
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
              controller: controller.nameController,
            ),
            const SizedBox(height: 20),

            _buildSectionTitle("بيانات الشكوى"),
            _buildDropdownField(),
            const SizedBox(height: 15),
            _buildTextField(
              label: "الرسالة",
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
        Text(label, style: const TextStyle(color: Color(0xFF00838F))),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          maxLines: maxLines,
          decoration: InputDecoration(
            hintText: hint,
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildDropdownField() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: DropdownButtonHideUnderline(
        child: Obx(
          () => DropdownButton<int>(
            isExpanded: true,
            value: controller.selectedDepartmentId.value == 0
                ? null
                : controller.selectedDepartmentId.value,
            hint: const Text("نوع الشكوى"),
            items: [
              const DropdownMenuItem(
                value: 1,
                child: Text("شكوى ضد متجر إلكتروني"),
              ),
              const DropdownMenuItem(value: 2, child: Text("شكوى تقنية")),
            ],
            onChanged: (val) => controller.selectedDepartmentId.value = val!,
          ),
        ),
      ),
    );
  }

  Widget _buildAttachmentBox() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(15),
        border: Border.all(
          color: const Color(0xFF00838F).withOpacity(0.3),
          style: BorderStyle.solid,
        ),
      ),
      child: Column(
        children: [
          const Icon(
            Icons.cloud_upload_outlined,
            size: 40,
            color: Color(0xFF00838F),
          ),
          const Text("أضف صوراً أو ملفات توضيحية"),
          TextButton(
            onPressed: () => controller.pickAttachment(),
            child: const Text(
              "اختر ملفاً",
              style: TextStyle(color: Color(0xFF00838F)),
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
      child: ElevatedButton(
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF00838F),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        onPressed: () => controller.submitComplaint(),
        child: const Text(
          "تقديم الشكوى",
          style: TextStyle(fontSize: 18, color: Colors.white),
        ),
      ),
    );
  }
}
