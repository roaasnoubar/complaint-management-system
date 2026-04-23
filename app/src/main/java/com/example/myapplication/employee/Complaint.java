package com.example.myapplication.employee;

import java.util.ArrayList;

public class Complaint {private String id;
    private String title;
    private String status;
    private String description;
    private String sender;
    private ArrayList<String> attachments;

    // 1. منشئ فارغ (ضروري لبعض العمليات)
    public Complaint() {
        this.attachments = new ArrayList<>();
    }

    // 2. منشئ يستقبل 6 متغيرات (الذي كان لديكِ سابقاً)
    public Complaint(String id, String title, String status, String description, String sender, ArrayList<String> attachments) {
        this.id = id;
        this.title = title;
        this.status = status;
        this.description = description;
        this.sender = sender;
        this.attachments = attachments;
    }

    // 3. المنشئ الجديد (المطلوب لحل المشكلة)
    // هذا المنشئ يسمح لكِ بإضافة شكوى بـ 4 نصوص فقط كما تفعلين في الـ Activity
    public Complaint(String id, String title, String status, String sender) {
        this.id = id;
        this.title = title;
        this.status = status;
        this.sender = sender;
        this.description = ""; // قيمة افتراضية
        this.attachments = new ArrayList<>(); // قائمة فارغة افتراضية
    }

    // Getters and Setters
    public String getId() { return id; }
    public void setId(String id) { this.id = id; }

    public String getTitle() { return title; }
    public void setTitle(String title) { this.title = title; }

    public String getStatus() { return status; }
    public void setStatus(String status) { this.status = status; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getSender() { return sender; }
    public void setSender(String sender) { this.sender = sender; }

    public ArrayList<String> getAttachments() { return attachments; }
    public void setAttachments(ArrayList<String> attachments) { this.attachments = attachments; }
}
