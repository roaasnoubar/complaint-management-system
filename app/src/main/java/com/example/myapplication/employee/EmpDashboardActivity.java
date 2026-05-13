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

        CardView cardOpen = findViewById(R.id.card_open);           
        CardView cardProcessing = findViewById(R.id.card_processing); 
        CardView cardClosed = findViewById(R.id.card_closed);       

        cardOpen.setOnClickListener(v -> navigateToList("NEW"));

        cardProcessing.setOnClickListener(v -> navigateToList("PROCESS"));

        cardClosed.setOnClickListener(v -> navigateToList("CLOSED"));
    }

    private void navigateToList(String type) {
        Intent intent = new Intent(EmpDashboardActivity.this, ComplaintListActivity.class);
        intent.putExtra("COMPLAINT_TYPE", type); 
        startActivity(intent);
    }
}
