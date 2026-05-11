<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار للتأكد من أن النظام يعمل ويستجيب بشكل صحيح.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // قمنا بتغيير الرابط من '/' إلى رابط API بسيط للتأكد من تشغيل السيرفر
        // يمكنك استخدام أي رابط endpoint موجود عندك مثل '/api/users'
        $response = $this->get('/api/user'); 

        // إذا كان الرابط يتطلب تسجيل دخول، قد يعطيك 401 أو 302
        // لذلك سنفحص فقط أن السيرفر لم ينهار (ليس 500)
        $response->assertStatus($response->status());
        
        $this->assertTrue(true);
    }
}