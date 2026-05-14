package com.example.myapplication.employee;

import android.os.Bundle;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.myapplication.R;
import java.util.ArrayList;

public class ComplaintDetailActivity extends AppCompatActivity {

    private TextView tvTitle, tvDesc, tvComplaintNumber, tvSenderName, tvComplaintStatus;
    private RecyclerView rvAttachments;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_complaint_detail);

        tvComplaintNumber = findViewById(R.id.tvComplaintNumber);
        tvTitle = findViewById(R.id.tvComplaintTitle);
        tvSenderName = findViewById(R.id.tvSenderName);
        tvComplaintStatus = findViewById(R.id.tvComplaintStatus);
        tvDesc = findViewById(R.id.tvComplaintDescription);
        rvAttachments = findViewById(R.id.rvAttachments);

        String number = getIntent().getStringExtra("id");
        String title = getIntent().getStringExtra("title");
        String sender = getIntent().getStringExtra("sender");
        String status = getIntent().getStringExtra("status");
        String desc = getIntent().getStringExtra("description");
        ArrayList<String> attachments = getIntent().getStringArrayListExtra("attachments");

        if (number != null) tvComplaintNumber.setText("رقم الشكوى: #" + number);
        if (title != null) tvTitle.setText(title);
        if (sender != null) tvSenderName.setText("المرسل: " + sender);
        if (status != null) {
            tvComplaintStatus.setText("الحالة: " + status);

            if (status.equalsIgnoreCase("closed")) {
                tvComplaintStatus.setTextColor(getResources().getColor(android.R.color.holo_red_dark));
            } else {
                tvComplaintStatus.setTextColor(getResources().getColor(android.R.color.holo_green_dark));
            }
        }
        if (desc != null) tvDesc.setText(desc);


        rvAttachments.setLayoutManager(new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false));
        if (attachments != null) {
            rvAttachments.setAdapter(new AttachmentAdapter(attachments));
        }
    }
}
