package com.example.myapplication;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import androidx.appcompat.app.AppCompatActivity;

public class RegisterActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        // تعريف زر المتابعة
        Button btnContinue = findViewById(R.id.btnContinue);

        // برمجة أمر الانتقال للواجهة الرابعة
        btnContinue.setOnClickListener(v -> {
            // الانتقال من واجهة التسجيل إلى واجهة التحقق
            Intent intent = new Intent(RegisterActivity.this, VerifyActivity.class);
            startActivity(intent);

            // إضافة حركة انتقال انسيابية
            overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
        });
    }
}