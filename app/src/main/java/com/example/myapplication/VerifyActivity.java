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

        Button btnVerify = findViewById(R.id.btnVerify);
        EditText etVerifyCode = findViewById(R.id.etVerifyCode);

        btnVerify.setOnClickListener(v -> {
            String code = etVerifyCode.getText().toString();

            if (code.length() == 6) {
                Toast.makeText(this, "تم التحقق بنجاح! مرحباً بك", Toast.LENGTH_SHORT).show();

                Intent intent = new Intent(VerifyActivity.this, HomeActivity.class);
                startActivity(intent);

                finishAffinity();

                overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
            } else {
                etVerifyCode.setError("يرجى إدخال رمز التحقق المكون من 6 أرقام");
            }
        });
    }
}