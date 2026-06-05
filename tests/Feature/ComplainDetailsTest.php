<?php

namespace Tests\Feature;
use App\Http\Controllers\ComplaintController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ComplainDetailsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_calculate_priority_returns_correct_values()
{
    // نستخدم ReflectionMethod للوصول للدالة الخاصة (Private) واختبارها مباشرة
    $controller = new ComplaintController();
    $reflection = new \ReflectionClass(get_class($controller));
    $method = $reflection->getMethod('calculatePriority');
    $method->setAccessible(true);

    // اختبار القيم
    $this->assertEquals('High', $method->invokeArgs($controller, [15]));
    $this->assertEquals('Medium', $method->invokeArgs($controller, [7]));
    $this->assertEquals('Low', $method->invokeArgs($controller, [2]));
}
}
