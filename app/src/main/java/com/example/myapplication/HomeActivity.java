package com.example.myapplication;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class HomeActivity extends AppCompatActivity {

    // تعريف الزر الوحيد الموجود في الـ XML الخاص بكِ
    Button btnNextStep;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        // ربط الزر باستخدام الـ ID الموجود في الكود الخاص بكِ (btnNextStep)
        btnNextStep = findViewById(R.id.btnNextStep);

        // عند الضغط على زر "التالي"
        btnNextStep.setOnClickListener(v -> {
            // حالياً سنعرض رسالة، ويمكنكِ هنا إضافة كود الانتقال لواجهة الـ Login
            Toast.makeText(HomeActivity.this, "جاري الانتقال لصفحة تسجيل الدخول...", Toast.LENGTH_SHORT).show();

            /* ملاحظة مهندسة: إذا أردتِ الانتقال لصفحة الـ Login فعلياً، استخدمي الكود التالي:
            Intent intent = new Intent(HomeActivity.this, LoginActivity.class);
            startActivity(intent);
            */
        });
    }
}
