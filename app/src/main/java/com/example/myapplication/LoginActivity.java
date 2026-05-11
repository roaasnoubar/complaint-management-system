package com.example.myapplication; // تأكدي أن هذا السطر يطابق اسم حزمتك

import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import java.util.HashMap;
import java.util.Map;

public class LoginActivity extends AppCompatActivity {

    // 1. تعريف الرابط (الـ IP الحقيقي لجهازك والمنفذ 8000)
    private static final String LOGIN_URL = "http://192.168.43.x:8000/api/login";

    private EditText etUsername, etPassword;
    private Button btnLogin;
    private TextView tvGoToRegister;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login); // تأكدي أن اسم ملف الـ XML هو activity_login

        // 2. ربط العناصر البرمجية بالـ IDs الموجودة في الـ XML
        etUsername = findViewById(R.id.etUsername);
        etPassword = findViewById(R.id.etPassword);
        btnLogin = findViewById(R.id.btnLogin);
        tvGoToRegister = findViewById(R.id.tvGoToRegister);

        // 3. مستمع حدث الضغط على زر "دخول"
        btnLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String email = etUsername.getText().toString().trim();
                String password = etPassword.getText().toString().trim();

                if (email.isEmpty() || password.isEmpty()) {
                    Toast.makeText(LoginActivity.this, "يرجى ملء كافة الحقول", Toast.LENGTH_SHORT).show();
                } else {
                    loginUser(email, password);
                }
            }
        });

        // مستمع الانتقال لواجهة التسجيل (اختياري)
        tvGoToRegister.setOnClickListener(v -> {
            // هنا يمكنك إضافة Intent للانتقال لصفحة التسجيل
            Toast.makeText(this, "الانتقال لصفحة التسجيل...", Toast.LENGTH_SHORT).show();
        });
    }

    // 4. دالة إرسال الطلب إلى سيرفر Laravel عبر Volley
    private void loginUser(String email, String password) {
        StringRequest stringRequest = new StringRequest(Request.Method.POST, LOGIN_URL,
            response -> {
                // في حال نجاح الاتصال وتلقي رد من السيرفر
                Log.d("Laravel_Response", response);
                Toast.makeText(LoginActivity.this, "تم تسجيل الدخول بنجاح!", Toast.LENGTH_LONG).show();
                // هنا يمكنك الانتقال للشاشة الرئيسية بعد النجاح
            },
            error -> {
                // في حال فشل الاتصال (Timeout أو خطأ 404/500)
                Log.e("Laravel_Error", error.toString());
                Toast.makeText(LoginActivity.this, "فشل الاتصال: تأكدي من السيرفر والشبكة", Toast.LENGTH_LONG).show();
            }) {

            @Override
            protected Map<String, String> getParams() {
                // إرسال البيانات كـ POST Parameters
                Map<String, String> params = new HashMap<>();
                // تأكدي أن المفتاتيح (email, password) تطابق ما هو موجود في Laravel Controller
                params.put("email", email);
                params.put("password", password);
                return params;
            }
        };

        // إضافة الطلب إلى طابور Volley للتنفيذ
        Volley.newRequestQueue(this).add(stringRequest);
    }
}
