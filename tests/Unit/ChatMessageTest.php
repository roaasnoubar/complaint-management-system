<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase; // لاحظي استخدام TestCase الأساسي ليكون اختبار وحدة حقيقي
use App\Models\ChatMessage;

class ChatMessageTest extends TestCase
{
    public function test_a_message_has_is_read_default_to_false()
    {
        // ننشئ كائن بدون حفظه في قاعدة البيانات
        $message = new ChatMessage([
            'message' => 'اختبار بدون قاعدة بيانات',
        ]);

        // نتحقق من القيمة الافتراضية
        $this->assertEquals(0, $message->is_read);
    }
}