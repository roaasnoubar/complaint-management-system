# ERD Table Relationships

This document maps the Entity-Relationship Diagram to the Laravel database schema and Eloquent models.

## Relationship Diagram

```
┌─────────────┐     role_id      ┌─────────────┐     role_id      ┌──────────────┐
│   ROLES     │◄─────────────────│   USERS     │─────────────────►│ role_permission│
└─────────────┘                  └──────┬──────┘                  └──────┬───────┘
                                        │ authority_id                   │ permission_id
                                        │ department_id                  │
                    ┌───────────────────┼───────────────────┐            │
                    │                   │                   │            │
                    ▼                   ▼                   ▼            ▼
            ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
            │ AUTHORITIES │     │ DEPARTMENTS │     │ PERMISSIONS │
            └──────┬──────┘     └──────┬──────┘     └─────────────┘
                   │                   │
                   │  authority_id     │  department_id
                   │  department_id    │  current_department_id
                   │                   │
                   └─────────┬─────────┘
                             │
                             ▼
                    ┌─────────────────┐     user_id
                    │   COMPLAINTS    │◄────────────── USERS
                    └────────┬────────┘
                             │
         ┌───────────────────┼───────────────────┐
         │                   │                   │
         ▼                   ▼                   ▼
┌─────────────────┐ ┌───────────────┐   ┌─────────────┐
│   ATTACHMENTS   │ │ COMPLAIN_CHAT │   │   RATINGS   │
│  user_id,       │ │ complain_id   │   │ complain_id │
│  complaint_id   │ │ user_id       │   │ user_id     │
└─────────────────┘ └───────┬───────┘   │ authority_id│
                            │           └─────────────┘
                            │ chat_id
                            ▼
                    ┌───────────────┐
                    │ CHAT_MESSAGES │  sender_id → USERS
                    │ chat_id       │
                    │ sender_id     │
                    └───────────────┘
```

## Foreign Key Mappings

| Child Table     | Foreign Key Column    | References       | On Delete    |
|-----------------|-----------------------|------------------|--------------|
| users           | role_id               | roles.id         | SET NULL     |
| users           | authority_id          | authorities.id   | SET NULL     |
| users           | department_id         | departments.id   | SET NULL     |
| departments     | authority_id          | authorities.id   | SET NULL     |
| role_permission | role_id               | roles.id         | CASCADE      |
| role_permission | permission_id         | permissions.id   | CASCADE      |
| complaints      | user_id               | users.id         | CASCADE      |
| complaints      | department_id         | departments.id   | SET NULL     |
| complaints      | current_department_id | departments.id   | SET NULL     |
| complaints      | authority_id          | authorities.id   | SET NULL     |
| attachments     | user_id               | users.id         | CASCADE      |
| attachments     | complaint_id          | complaints.id    | SET NULL     |
| complain_chats  | complain_id           | complaints.id    | CASCADE      |
| complain_chats  | user_id               | users.id         | CASCADE      |
| chat_messages   | chat_id               | complain_chats.id| CASCADE      |
| chat_messages   | sender_id             | users.id         | CASCADE      |
| ratings         | complain_id           | complaints.id    | CASCADE      |
| ratings         | user_id               | users.id         | CASCADE      |
| ratings         | authority_id          | authorities.id   | CASCADE      |

## Eloquent Relationships by Model

### User
- `belongsTo` Role, Authority, Department
- `hasMany` Complaint, Attachment, ComplainChat, Rating
- `hasMany` ChatMessage (as sentMessages, via sender_id)

### Role
- `belongsToMany` Permission (via role_permission)
- `hasMany` User

### Permission
- `belongsToMany` Role (via role_permission)

### Authority
- `hasMany` Department, User, Complaint, Rating

### Department
- `belongsTo` Authority
- `hasMany` User, Complaint (department_id), Complaint (current_department_id)

### Complaint
- `belongsTo` User, Department, Department (currentDepartment), Authority
- `hasMany` Attachment, ComplainChat, Rating

### Attachment
- `belongsTo` User, Complaint

### ComplainChat
- `belongsTo` Complaint (complain_id), User
- `hasMany` ChatMessage (chat_id)

### ChatMessage
- `belongsTo` ComplainChat (chat_id), User (sender_id)

### Rating
- `belongsTo` Complaint (complain_id), User, Authority
