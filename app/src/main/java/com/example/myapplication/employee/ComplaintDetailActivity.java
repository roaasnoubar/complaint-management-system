package com.example.myapplication.employee;

import android.os.Bundle;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.myapplication.R;
import java.util.ArrayList;

public class ComplaintDetailActivity extends AppCompatActivity {

    // 1. تعريف العناصر الموجودة في الـ XML
    private TextView tvTitle, tvDesc, tvComplaintNumber, tvSenderName, tvComplaintStatus;
    private RecyclerView rvAttachments;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_complaint_detail);

        // 2. ربط العناصر (تأكدي أن الـ IDs في الـ XML مطابقة لهذه تماماً)
        tvComplaintNumber = findViewById(R.id.tvComplaintNumber);
        tvTitle = findViewById(R.id.tvComplaintTitle);
        tvSenderName = findViewById(R.id.tvSenderName);
        tvComplaintStatus = findViewById(R.id.tvComplaintStatus);
        tvDesc = findViewById(R.id.tvComplaintDescription);
        rvAttachments = findViewById(R.id.rvAttachments);

        // 3. استقبال البيانات المرسلة من الصفحة السابقة (الـ ListActivity)
        String number = getIntent().getStringExtra("id");
        String title = getIntent().getStringExtra("title");
        String sender = getIntent().getStringExtra("sender");
        String status = getIntent().getStringExtra("status");
        String desc = getIntent().getStringExtra("description");
        ArrayList<String> attachments = getIntent().getStringArrayListExtra("attachments");

        // 4. عرض البيانات في الواجهة
        if (number != null) tvComplaintNumber.setText("رقم الشكوى: #" + number);
        if (title != null) tvTitle.setText(title);
        if (sender != null) tvSenderName.setText("المرسل: " + sender);
        if (status != null) {
            tvComplaintStatus.setText("الحالة: " + status);
            // تغيير اللون بناءً على الحالة (اختياري)
            if (status.equalsIgnoreCase("closed")) {
                tvComplaintStatus.setTextColor(getResources().getColor(android.R.color.holo_red_dark));
            } else {
                tvComplaintStatus.setTextColor(getResources().getColor(android.R.color.holo_green_dark));
            }
        }
        if (desc != null) tvDesc.setText(desc);

        // 5. إعداد الـ RecyclerView لعرض المرفقات
        rvAttachments.setLayoutManager(new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false));
        if (attachments != null) {
            rvAttachments.setAdapter(new AttachmentAdapter(attachments));
        }
    }
}
