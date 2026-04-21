package com.example.myapplication;

import android.os.Bundle;
import android.widget.Button;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class HomeActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        Button btnNextStep = findViewById(R.id.btnNextStep);

        btnNextStep.setOnClickListener(v -> {
            // هنا تضعين الواجهة السادسة مستقبلاً
            Toast.makeText(this, "الانتقال إلى واجهة تقديم الشكوى...", Toast.LENGTH_SHORT).show();
        });
    }
}