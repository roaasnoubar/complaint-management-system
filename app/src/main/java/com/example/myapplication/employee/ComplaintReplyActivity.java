package com.example.myapplication.employee;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;
import androidx.appcompat.app.AppCompatActivity;
import com.example.myapplication.R;

public class ComplaintReplyActivity extends AppCompatActivity {

    private LinearLayout boxOfficialReply;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_complaint_reply);

        boxOfficialReply = findViewById(R.id.boxOfficialReply);

        // عند الضغط على بوكس الرد الرسمي يفتح صفحة بيضاء للكتابة
        boxOfficialReply.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(ComplaintReplyActivity.this, FullScreenReplyActivity.class);
                startActivity(intent);
            }
        });
    }
}
