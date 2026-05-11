package com.example.myapplication;

import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class RegisterActivity extends AppCompatActivity {

    private EditText etName, etPhone, etEmail, etPassword;
    private Button btnRegister;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        // ربط العناصر البرمجية بالـ XML
        etName = findViewById(R.id.etName);
        etPhone = findViewById(R.id.etPhone);
        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        btnRegister = findViewById(R.id.btnRegister);

        btnRegister.setOnClickListener(v -> {
            String name = etName.getText().toString();
            String phone = etPhone.getText().toString();
            String email = etEmail.getText().toString();
            String password = etPassword.getText().toString();

            if (name.isEmpty() || email.isEmpty() || password.isEmpty()) {
                Toast.makeText(this, "يرجى تعبئة كافة الحقول", Toast.LENGTH_SHORT).show();
            } else {
                // هنا سيتم لاحقاً استدعاء دالة Retrofit لإرسال البيانات
                Toast.makeText(this, "جاري التسجيل...", Toast.LENGTH_SHORT).show();
                performRegistration(name, phone, email, password);
            }
        });
    }

    private void performRegistration(String name, String phone, String email, String password) {
        // سيتم وضع منطق الاتصال بالسيرفر هنا
    }
}
