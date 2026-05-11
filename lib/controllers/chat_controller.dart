import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../core/auth/rbac.dart';
import '../models/message_model.dart';
import '../services/chat_service.dart';

class ChatController extends GetxController {
  ChatController({ChatService? chatService})
    : _chatService = chatService ?? ChatService();

  final ChatService _chatService;

  final RxBool isLoading = true.obs;
  final RxBool isSending = false.obs;
  final RxBool chatIsClosed = false.obs;
  final RxBool canSend = false.obs;
  final RxnString error = RxnString();

  final RxList<MessageModel> messages = <MessageModel>[].obs;
  final TextEditingController inputController = TextEditingController();

  late final int complaintId;
  late final String complaintTitle;

  @override
  void onInit() {
    super.onInit();
    _initializeChat();
  }

  void _initializeChat() {
    final dynamic args = Get.arguments;

    if (args is Map) {
      final idRaw = args['complaint_id'] ?? args['complaintId'];
      complaintId = int.tryParse(idRaw?.toString() ?? '0') ?? 0;
      complaintTitle = args['complaint_title']?.toString() ?? 'المحادثة';
    } else {
      final idRaw = Get.parameters['complaintId'] ?? args?.toString();
      complaintId = int.tryParse(idRaw ?? '0') ?? 0;
      complaintTitle = 'المحادثة';
    }

    _loadHistory();
  }

  Future<void> _loadHistory() async {
    if (complaintId == 0) {
      error.value = 'رقم الشكوى غير صالح';
      isLoading.value = false;
      return;
    }

    isLoading.value = true;
    error.value = null;

    try {
      final items = await _chatService.getHistory(complaintId);
      messages.assignAll(items);
      _updateCanSend();
    } catch (e) {
      error.value = e.toString();
      canSend.value = Rbac.isOfficialUser();
    } finally {
      isLoading.value = false;
    }
  }

  void _updateCanSend() {
    if (chatIsClosed.value) {
      canSend.value = false;
      return;
    }
    canSend.value = Rbac.isOfficialUser() || messages.isNotEmpty;
  }

  Future<void> send() async {
    final text = inputController.text.trim();
    if (text.isEmpty || !canSend.value || chatIsClosed.value) return;

    isSending.value = true;
    try {
      final sent = await _chatService.sendMessage(
        complaintId: complaintId,
        message: text,
      );
      inputController.clear();
      messages.add(sent);
    } catch (e) {
      Get.snackbar(
        'خطأ',
        'فشل إرسال الرسالة',
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isSending.value = false;
    }
  }

  Future<void> toggleChatStatus(bool close) async {
    if (!Rbac.isOfficialUser()) return;

    try {
      isLoading.value = true;
      await _chatService.updateChatStatus(complaintId, close);

      chatIsClosed.value = close;
      _updateCanSend();

      Get.snackbar(
        close ? 'تم إغلاق المحادثة' : 'تم فتح المحادثة',
        close
            ? 'لن يتمكن المواطن من إرسال رسائل جديدة'
            : 'يمكن للمواطن الآن إرسال رسائل',
        snackPosition: SnackPosition.BOTTOM,
        duration: const Duration(seconds: 3),
      );
    } catch (e) {
      Get.snackbar(
        'خطأ',
        'فشل تحديث حالة المحادثة',
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> refresh() => _loadHistory();

  @override
  void onClose() {
    inputController.dispose();
    super.onClose();
  }
}
