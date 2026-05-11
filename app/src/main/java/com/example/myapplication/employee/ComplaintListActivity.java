package com.example.myapplication.employee;

import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.myapplication.R;
import java.util.ArrayList;
import java.util.List;

public class ComplaintListActivity extends AppCompatActivity {

    private RecyclerView recyclerView;
    private ComplaintAdapter adapter;
    private List<Complaint> fullList;
    private List<Complaint> filteredList;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // جعل شريط الحالة أبيض والأيقونات سوداء
        Window window = getWindow();
        window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
        window.setStatusBarColor(Color.WHITE);
        window.getDecorView().setSystemUiVisibility(View.SYSTEM_UI_FLAG_LIGHT_STATUS_BAR);

        setContentView(R.layout.activity_complaint_list);

        // إزالة الرمادي نهائياً وجعل الخلفية بيضاء سادة تماماً
        getWindow().getDecorView().setBackgroundColor(Color.WHITE);
        View rootView = findViewById(android.R.id.content);
        if (rootView != null) {
            rootView.setBackgroundColor(Color.WHITE);
        }

        recyclerView = findViewById(R.id.recyclerViewComplaints);
        if (recyclerView != null) {
            recyclerView.setBackgroundColor(Color.WHITE);
        }

        fullList = new ArrayList<>();
        filteredList = new ArrayList<>();

        String type = getIntent().getStringExtra("COMPLAINT_TYPE");

        loadFakeData();
        filterData(type);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        adapter = new ComplaintAdapter(this, filteredList);
        recyclerView.setAdapter(adapter);
    }

    private void filterData(String type) {
        filteredList.clear();
        if (fullList != null && type != null) {
            for (Complaint item : fullList) {
                if (type.equals("NEW") && item.getStatus().equals("قيد الانتظار")) {
                    filteredList.add(item);
                }
                else if (type.equals("PROCESS") && (item.getStatus().equals("قيد المعالجة") || item.getStatus().equals("قيد المراجعة"))) {
                    filteredList.add(item);
                }
                else if (type.equals("CLOSED") && item.getStatus().equals("تم الحل")) {
                    filteredList.add(item);
                }
            }
        }
        if (adapter != null) {
            adapter.notifyDataSetChanged();
        }
    }

    private void loadFakeData() {
        fullList.add(new Complaint("10254", "عطل فني في الطابعة", "قيد الانتظار", "أحمد ياسين"));
        fullList.add(new Complaint("10582", "تحديث نظام البصمة", "قيد المعالجة", "سارة علي"));
        fullList.add(new Complaint("10890", "مشكلة في التكييف", "تم الحل", "خالد محمد"));
        fullList.add(new Complaint("11002", "طلب صيانة مكتب", "قيد المراجعة", "ليلى حسن"));
        fullList.add(new Complaint("11240", "انقطاع شبكة الإنترنت", "قيد الانتظار", "محمد محمود"));
    }
}
