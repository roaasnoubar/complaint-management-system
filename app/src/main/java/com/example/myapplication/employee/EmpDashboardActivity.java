package com.example.myapplication.employee;

import android.content.Intent;
import android.os.Bundle;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import com.example.myapplication.R;

public class EmpDashboardActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_emp_dashboard);

        // ربط الكروت الموجودة في واجهة الموظف
        CardView cardOpen = findViewById(R.id.card_open);           // الشكاوى الجديدة
        CardView cardProcessing = findViewById(R.id.card_processing); // قيد المعالجة
        CardView cardClosed = findViewById(R.id.card_closed);       // الشكاوى المغلقة

        // عند الضغط على الشكاوى الجديدة
        cardOpen.setOnClickListener(v -> navigateToList("NEW"));

        // عند الضغط على قيد المعالجة / المراجعة
        cardProcessing.setOnClickListener(v -> navigateToList("PROCESS"));

        // عند الضغط على الشكاوى المغلقة / تم الحل
        cardClosed.setOnClickListener(v -> navigateToList("CLOSED"));
    }

    private void navigateToList(String type) {
        Intent intent = new Intent(EmpDashboardActivity.this, ComplaintListActivity.class);
        intent.putExtra("COMPLAINT_TYPE", type); // نرسل النوع المختار
        startActivity(intent);
    }
}
