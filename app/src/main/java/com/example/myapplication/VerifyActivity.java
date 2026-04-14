package com.example.myapplication;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class VerifyActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_verify);

        // 1. تعريف العناصر
        Button btnVerify = findViewById(R.id.btnVerify);
        EditText etVerifyCode = findViewById(R.id.etVerifyCode);

        // 2. برمجة زر التأكيد للربط بالواجهة الخامسة
        btnVerify.setOnClickListener(v -> {
            String code = etVerifyCode.getText().toString();

            // شرط بسيط للتأكد من إدخال الرمز (مثلاً 6 أرقام)
            if (code.length() == 6) {
                Toast.makeText(this, "تم التحقق بنجاح! مرحباً بك", Toast.LENGTH_SHORT).show();

                // الانتقال من واجهة التحقق (الرابعة) إلى القائمة الرئيسية (الخامسة)
                Intent intent = new Intent(VerifyActivity.this, HomeActivity.class);
                startActivity(intent);

                // سطر هام جداً: إغلاق الواجهات السابقة لكي لا يعود المستخدم لصفحة التحقق
                finishAffinity();

                // إضافة حركة انتقال انسيابية
                overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
            } else {
                etVerifyCode.setError("يرجى إدخال رمز التحقق المكون من 6 أرقام");
            }
        });
    }
}