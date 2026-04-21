# Complaint Management System - ERD Schema

This document describes the database schema and entity relationships for the complaint management application.

## Entity Relationship Overview

```
USER ←→ ROLE (via role_id)
USER ←→ AUTHORITY (via authority_id)
USER ←→ DEPARTMENT (via department_id)
ROLE ←→ PERMISSION (via role_permission junction)
DEPARTMENT ←→ AUTHORITY (via authority_id)
COMPLAINT ←→ USER (creator)
COMPLAINT ←→ DEPARTMENT (assigned/current)
COMPLAINT ←→ AUTHORITY
ATTACHMENT ←→ USER (uploader)
ATTACHMENT ←→ COMPLAINT
COMPLAIN_CHAT ←→ COMPLAINT
COMPLAIN_CHAT ←→ USER (participant)
CHAT_MESSAGE ←→ COMPLAIN_CHAT
CHAT_MESSAGE ←→ USER (sender)
RATING ←→ COMPLAINT
RATING ←→ USER (rater)
RATING ←→ AUTHORITY (rated)
```

## Tables and Columns

### users
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| role_id | bigint FK | Links to roles |
| authority_id | bigint FK | Links to authorities |
| department_id | bigint FK | Links to departments |
| name | string | User full name |
| email | string | Unique email |
| phone | string | Phone number |
| password | string | Hashed password |
| verification | boolean | Account verified |
| score | integer | User score |
| is_active | boolean | Account active status |
| email_verified_at | timestamp | Email verification time |
| created_at, updated_at | timestamps | Laravel timestamps |

### roles
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| level | integer | Role hierarchy level |
| type | string | Role type (citizen, employee, etc.) |
| created_at, updated_at | timestamps | Laravel timestamps |

### permissions
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| name | string | Permission identifier |
| description | text | Permission description |
| created_at, updated_at | timestamps | Laravel timestamps |

### role_permission (Junction)
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| role_id | bigint FK | Links to roles |
| permission_id | bigint FK | Links to permissions |
| created_at, updated_at | timestamps | Laravel timestamps |

### authorities
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| name | string | Authority name |
| description | text | Authority description |
| created_at, updated_at | timestamps | Laravel timestamps |

### departments
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| authority_id | bigint FK | Links to authorities |
| name | string | Department name |
| description | text | Department description |
| is_active | boolean | Active status |
| created_at, updated_at | timestamps | Laravel timestamps |

### complaints
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| user_id | bigint FK | Creator (user) |
| department_id | bigint FK | Assigned department |
| current_department_id | bigint FK | Currently handling department |
| authority_id | bigint FK | Handling authority |
| title | string | Complaint title |
| description | text | Complaint details |
| status | string | pending, in_progress, resolved |
| is_valid | boolean | Validity flag |
| assigned_level | integer | Escalation level |
| resolved_at | timestamp | Resolution timestamp |
| created_at, updated_at | timestamps | Laravel timestamps |

### attachments
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| user_id | bigint FK | Uploader (user) |
| complaint_id | bigint FK | Associated complaint |
| file_path | string | Storage path |
| file_type | string | MIME type |
| created_at, updated_at | timestamps | Laravel timestamps |

### complain_chats
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| complain_id | bigint FK | Links to complaints |
| user_id | bigint FK | Chat participant |
| is_open | boolean | Chat status |
| closed_at | timestamp | Close timestamp |
| created_at, updated_at | timestamps | Laravel timestamps |

### chat_messages
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| chat_id | bigint FK | Links to complain_chats |
| sender_id | bigint FK | Message sender (user) |
| message | text | Message content |
| sent_at | timestamp | Send timestamp |
| created_at, updated_at | timestamps | Laravel timestamps |

### ratings
| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | Primary key |
| complain_id | bigint FK | Related complaint |
| user_id | bigint FK | User who rated |
| authority_id | bigint FK | Authority being rated |
| response_speed_score | integer | Score value |
| created_at, updated_at | timestamps | Laravel timestamps |

## Model Files

| ERD Entity | Model Class | File |
|------------|-------------|------|
| USER | User | app/Models/User.php |
| ROLE | Role | app/Models/Role.php |
| PERMISSION | Permission | app/Models/Permission.php |
| ROLE_PERMISSION | (pivot) | role_permission table |
| AUTHORITY | Authority | app/Models/Authority.php |
| DEPARTMENT | Department | app/Models/Department.php |
| COMPLAIN | Complaint | app/Models/Complaint.php |
| ATTACHMENTS | Attachment | app/Models/Attachment.php |
| COMPLAIN CHAT | ComplainChat | app/Models/ComplainChat.php |
| CANT MESSAGE / CHAT MESSAGE | ChatMessage | app/Models/ChatMessage.php |
| RATTING / RATING | Rating | app/Models/Rating.php |

## Migration Files

All migrations are in `database/migrations/` and run in chronological order.
