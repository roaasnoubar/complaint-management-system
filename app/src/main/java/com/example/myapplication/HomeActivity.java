package com.example.myapplication;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class HomeActivity extends AppCompatActivity {

    Button btnNextStep;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        btnNextStep = findViewById(R.id.btnNextStep);

        btnNextStep.setOnClickListener(v -> {

            Toast.makeText(HomeActivity.this, "جاري الانتقال لصفحة تسجيل الدخول...", Toast.LENGTH_SHORT).show();

            /* ملاحظة مهندسة: إذا أردتِ الانتقال لصفحة الـ Login فعلياً، استخدمي الكود التالي:
            Intent intent = new Intent(HomeActivity.this, LoginActivity.class);
            startActivity(intent);
            */
        });
    }
}
