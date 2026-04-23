package com.example.myapplication.employee;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.myapplication.R;
import java.util.List;

public class ComplaintAdapter extends RecyclerView.Adapter<ComplaintAdapter.ViewHolder> {

    private List<Complaint> complaints;
    private Context context;

    public ComplaintAdapter(Context context, List<Complaint> complaints) {
        this.context = context;
        this.complaints = complaints;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        // يتم استخدام item_complaint الذي يحتوي على خلفية بيضاء
        View view = LayoutInflater.from(context).inflate(R.layout.item_complaint, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Complaint complaint = complaints.get(position);

        holder.tvId.setText("#" + complaint.getId());
        holder.tvTitle.setText("نوع الشكوى: " + complaint.getTitle());
        holder.tvUser.setText("بواسطة: " + complaint.getSender());
        holder.tvStatus.setText(complaint.getStatus());

        String status = complaint.getStatus();

        // إعداد ألوان الحالة (نصوص ملونة بخلفيات فاتحة جداً بدلاً من الرمادي)
        if (status.equals("قيد الانتظار")) {
            holder.tvStatus.setTextColor(Color.parseColor("#E65100")); // برتقالي
            holder.tvStatus.setBackgroundColor(Color.parseColor("#FFF3E0"));
            holder.btnReply.setVisibility(View.GONE); // إخفاء الزر
        }
        else if (status.equals("قيد المعالجة") || status.equals("قيد المراجعة")) {
            holder.tvStatus.setTextColor(Color.parseColor("#006064")); // تركواز
            holder.tvStatus.setBackgroundColor(Color.parseColor("#E0F2F1"));

            // إظهار زر الرد فقط في هذه الحالة
            holder.btnReply.setVisibility(View.VISIBLE);
            holder.btnReply.setOnClickListener(v -> {
                // التعديل المطلوب: الانتقال مباشرة إلى الواجهة البيضاء السادة
                Intent intent = new Intent(context, FullScreenReplyActivity.class);
                intent.putExtra("complaint_id", complaint.getId());
                context.startActivity(intent);
            });
        }
        else if (status.equals("تم الحل")) {
            holder.tvStatus.setTextColor(Color.parseColor("#2E7D32")); // أخضر
            holder.tvStatus.setBackgroundColor(Color.parseColor("#E8F5E9"));
            holder.btnReply.setVisibility(View.GONE); // إخفاء الزر
        }
    }

    @Override
    public int getItemCount() {
        return complaints.size();
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvTitle, tvStatus, tvUser, tvId;
        Button btnReply;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            // ربط العناصر من ملف item_complaint.xml
            tvTitle = itemView.findViewById(R.id.textViewTitle);
            tvStatus = itemView.findViewById(R.id.textViewStatus);
            tvUser = itemView.findViewById(R.id.textViewUser);
            tvId = itemView.findViewById(R.id.tvComplaintNumber);
            btnReply = itemView.findViewById(R.id.btnReply);
        }
    }
}
