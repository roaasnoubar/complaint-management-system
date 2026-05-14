package com.example.myapplication.employee;

import java.util.ArrayList;

public class Complaint {private String id;
    private String title;
    private String status;
    private String description;
    private String sender;
    private ArrayList<String> attachments;

    public Complaint() {
        this.attachments = new ArrayList<>();
    }

    public Complaint(String id, String title, String status, String description, String sender, ArrayList<String> attachments) {
        this.id = id;
        this.title = title;
        this.status = status;
        this.description = description;
        this.sender = sender;
        this.attachments = attachments;
    }

    public Complaint(String id, String title, String status, String sender) {
        this.id = id;
        this.title = title;
        this.status = status;
        this.sender = sender;
        this.description = ""; 
        this.attachments = new ArrayList<>(); 
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
