<?php

namespace Tests\Unit;

use Tests\TestCase; // تأكدي من استخدام TestCase الخاص بـ Laravel
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatMessageTest extends TestCase
{
    use RefreshDatabase; // لتنظيف قاعدة بيانات الاختبار بعد كل تجربة

    /** @test */
    public function a_message_has_is_read_default_to_false()
    {
        // 1. تجهيز البيانات (مثال لرسالة جديدة)
        $message = new ChatMessage([
            'message' => 'اختبار وحدة برمجي',
            'sender_id' => 1,
            'chat_id' => 1,
        ]);

        // 2. التحقق من القيمة الافتراضية لحقل is_read
        // نتوقع أن تكون القيمة false (0)
        $this->assertEquals(0, $message->is_read);
    }
}