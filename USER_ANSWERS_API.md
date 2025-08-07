# User Answers API Documentation

This document describes the API endpoints for submitting and retrieving user answers for methodology questions.

## Authentication

All endpoints require JWT authentication. Include the JWT token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

## Base URL

```
/api/user-answers
```

## Endpoints

### 1. Submit Methodology Answers

Submit user answers for methodology-level questions.

**Endpoint:** `POST /api/user-answers/methodology/{methodologyId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology

**Request Body:**
```json
{
    "answers": [
        {
            "question_id": 1,
            "answerIds": [3]
        },
        {
            "question_id": 2,
            "answerIds": [5, 6, 7]
        }
    ]
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Methodology answers submitted successfully",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "context_type": "methodology",
            "context_id": 1,
            "question": {
                "id": 1,
                "title": "What is your organization size?",
                "type": "MCQSingle",
                "tags": ["organization", "size"]
            },
            "answer": {
                "id": 3,
                "title": "Medium (50-200 employees)",
                "description": "Organization with 50-200 employees"
            },
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        },
        {
            "id": 2,
            "user_id": 1,
            "context_type": "methodology",
            "context_id": 1,
            "question": {
                "id": 2,
                "title": "Which security measures do you have?",
                "type": "MCQMultiple",
                "tags": ["security", "measures"]
            },
            "answer": {
                "id": 5,
                "title": "Firewall",
                "description": "Network firewall protection"
            },
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        },
        {
            "id": 3,
            "user_id": 1,
            "context_type": "methodology",
            "context_id": 1,
            "question": {
                "id": 2,
                "title": "Which security measures do you have?",
                "type": "MCQMultiple",
                "tags": ["security", "measures"]
            },
            "answer": {
                "id": 6,
                "title": "Antivirus",
                "description": "Antivirus software"
            },
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    ]
}
```

### 2. Submit Pillar Answers

Submit user answers for pillar-level questions within a methodology.

**Endpoint:** `POST /api/user-answers/methodology/{methodologyId}/pillar/{pillarId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology
- `pillarId` (path): The ID of the pillar

**Request Body:**
```json
{
    "answers": [
        {
            "question_id": 10,
            "answerIds": [25]
        },
        {
            "question_id": 11,
            "answerIds": [28]
        }
    ]
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Pillar answers submitted successfully",
    "data": [
        {
            "id": 2,
            "user_id": 1,
            "context_type": "pillar",
            "context_id": 2,
            "question": {
                "id": 10,
                "title": "How mature is your security framework?",
                "type": "multiple_choice",
                "tags": ["security", "maturity"]
            },
            "answer": {
                "id": 25,
                "title": "Advanced",
                "description": "Well-established security practices"
            },
            "created_at": "2024-01-15T10:35:00.000000Z",
            "updated_at": "2024-01-15T10:35:00.000000Z"
        }
    ]
}
```

### 3. Submit Module Answers

Submit user answers for module-level questions within a methodology.

**Endpoint:** `POST /api/user-answers/methodology/{methodologyId}/module/{moduleId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology
- `moduleId` (path): The ID of the module

**Request Body:**
```json
{
    "answers": [
        {
            "question_id": 20,
            "answerIds": [45]
        },
        {
            "question_id": 21,
            "answerIds": [48]
        }
    ]
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Module answers submitted successfully",
    "data": [
        {
            "id": 3,
            "user_id": 1,
            "context_type": "module",
            "context_id": 3,
            "question": {
                "id": 20,
                "title": "Do you have incident response procedures?",
                "type": "yes_no",
                "tags": ["incident", "response"]
            },
            "answer": {
                "id": 45,
                "title": "Yes",
                "description": "We have documented incident response procedures"
            },
            "created_at": "2024-01-15T10:40:00.000000Z",
            "updated_at": "2024-01-15T10:40:00.000000Z"
        }
    ]
}
```

### 4. Submit Pillar Module Answers

Submit user answers for module-level questions within a specific pillar of a methodology.

**Endpoint:** `POST /api/user-answers/methodology/{methodologyId}/pillar/{pillarId}/module/{moduleId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology
- `pillarId` (path): The ID of the pillar
- `moduleId` (path): The ID of the module

**Request Body:**
```json
{
    "answers": [
        {
            "question_id": 30,
            "answerIds": [55]
        },
        {
            "question_id": 31,
            "answerIds": [58]
        }
    ]
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Pillar module answers submitted successfully",
    "data": [
        {
            "id": 4,
            "user_id": 1,
            "context_type": "module",
            "context_id": 4,
            "question": {
                "id": 30,
                "title": "How often do you conduct security audits?",
                "type": "multiple_choice",
                "tags": ["audit", "frequency"]
            },
            "answer": {
                "id": 55,
                "title": "Quarterly",
                "description": "We conduct security audits every quarter"
            },
            "created_at": "2024-01-15T10:45:00.000000Z",
            "updated_at": "2024-01-15T10:45:00.000000Z"
        }
    ]
}
```

### 5. Get Methodology Answers

Retrieve user answers for methodology-level questions.

**Endpoint:** `GET /api/user-answers/methodology/{methodologyId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "user_id": 1,
            "context_type": "methodology",
            "context_id": 1,
            "question": {
                "id": 1,
                "title": "What is your organization size?",
                "type": "MCQSingle",
                "tags": ["organization", "size"]
            },
            "answers": [
                {
                    "id": 3,
                    "title": "Medium (50-200 employees)",
                    "description": "Organization with 50-200 employees"
                }
            ],
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        },
        {
            "user_id": 1,
            "context_type": "methodology",
            "context_id": 1,
            "question": {
                "id": 2,
                "title": "Which security measures do you have?",
                "type": "MCQMultiple",
                "tags": ["security", "measures"]
            },
            "answers": [
                {
                    "id": 5,
                    "title": "Firewall",
                    "description": "Network firewall protection"
                },
                {
                    "id": 6,
                    "title": "Antivirus",
                    "description": "Antivirus software"
                },
                {
                    "id": 7,
                    "title": "Encryption",
                    "description": "Data encryption"
                }
            ],
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    ]
}
```

### 6. Get Pillar Answers

Retrieve user answers for pillar-level questions within a methodology.

**Endpoint:** `GET /api/user-answers/methodology/{methodologyId}/pillar/{pillarId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology
- `pillarId` (path): The ID of the pillar

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "user_id": 1,
            "context_type": "pillar",
            "context_id": 2,
            "question": {
                "id": 10,
                "title": "How mature is your security framework?",
                "type": "multiple_choice",
                "tags": ["security", "maturity"]
            },
            "answer": {
                "id": 25,
                "title": "Advanced",
                "description": "Well-established security practices"
            },
            "created_at": "2024-01-15T10:35:00.000000Z",
            "updated_at": "2024-01-15T10:35:00.000000Z"
        }
    ]
}
```

### 7. Get Module Answers

Retrieve user answers for module-level questions within a methodology.

**Endpoint:** `GET /api/user-answers/methodology/{methodologyId}/module/{moduleId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology
- `moduleId` (path): The ID of the module

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 3,
            "user_id": 1,
            "context_type": "module",
            "context_id": 3,
            "question": {
                "id": 20,
                "title": "Do you have incident response procedures?",
                "type": "yes_no",
                "tags": ["incident", "response"]
            },
            "answer": {
                "id": 45,
                "title": "Yes",
                "description": "We have documented incident response procedures"
            },
            "created_at": "2024-01-15T10:40:00.000000Z",
            "updated_at": "2024-01-15T10:40:00.000000Z"
        }
    ]
}
```

### 8. Get Pillar Module Answers

Retrieve user answers for module-level questions within a specific pillar of a methodology.

**Endpoint:** `GET /api/user-answers/methodology/{methodologyId}/pillar/{pillarId}/module/{moduleId}`

**Parameters:**
- `methodologyId` (path): The ID of the methodology
- `pillarId` (path): The ID of the pillar
- `moduleId` (path): The ID of the module

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 4,
            "user_id": 1,
            "context_type": "module",
            "context_id": 4,
            "question": {
                "id": 30,
                "title": "How often do you conduct security audits?",
                "type": "multiple_choice",
                "tags": ["audit", "frequency"]
            },
            "answer": {
                "id": 55,
                "title": "Quarterly",
                "description": "We conduct security audits every quarter"
            },
            "created_at": "2024-01-15T10:45:00.000000Z",
            "updated_at": "2024-01-15T10:45:00.000000Z"
        }
    ]
}
```

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "answers": ["The answers field is required."],
        "answers.0.question_id": ["The answers.0.question_id field is required."],
        "answers.0.answerIds": ["The answers.0.answerIds field is required."],
        "answers.0.answerIds.0": ["The answers.0.answerIds.0 field must be an integer."]
    }
}
```

### Bad Request (400)
```json
{
    "success": false,
    "message": "Methodology with ID 999 not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Error submitting methodology answers",
    "error": "Database connection failed"
}
```

## Notes

1. **Authentication Required**: All endpoints require valid JWT authentication.
2. **User Context**: Answers are automatically associated with the authenticated user.
3. **Idempotent**: Submitting answers for the same context will replace existing answers.
4. **Validation**: All question and answer IDs are validated against the database.
5. **Context Hierarchy**: The API supports the methodology → pillar → module hierarchy.
6. **Response Format**: All responses include question and answer details for easy consumption.
7. **Consistent Answer Format**: All questions use the `answerIds` array format for consistency, even for single answers.
8. **Methodology-Specific Relationships**: Pillar-module relationships are specific to methodologies, allowing the same pillar to be used in different methodologies with different modules.

## Answer Format

All questions use the `answerIds` array format for consistency:

### Request Format
```json
{
    "answers": [
        {
            "question_id": 1,
            "answerIds": [3]
        },
        {
            "question_id": 2,
            "answerIds": [5, 6, 7]
        }
    ]
}
```

### Supported Question Types
- **MCQSingle**: Use `answerIds` array with single answer `[3]`
- **MCQMultiple**: Use `answerIds` array with multiple answers `[5, 6, 7]`
- **YesNo, TrueFalse, Rating1to5, Rating1to10, ScaleAgreeDisagree**: Use `answerIds` array with single answer `[3]`

### Response Format
GET endpoints return answers grouped by question, with answers shown in an `answers` array:

```json
{
    "question": {
        "id": 1,
        "title": "What is your organization size?",
        "type": "MCQSingle"
    },
    "answers": [
        {"id": 3, "title": "Medium (50-200 employees)"}
    ]
}
```

For multiple answers:
```json
{
    "question": {
        "id": 2,
        "title": "Which security measures do you have?",
        "type": "MCQMultiple"
    },
    "answers": [
        {"id": 5, "title": "Firewall"},
        {"id": 6, "title": "Antivirus"},
        {"id": 7, "title": "Encryption"}
    ]
}
```

## Usage Examples

### Submit answers for a methodology
```bash
curl -X POST "https://api.example.com/api/user-answers/methodology/1" \
  -H "Authorization: Bearer your-jwt-token" \
  -H "Content-Type: application/json" \
  -d '{
    "answers": [
      {"question_id": 1, "answerIds": [3]},
      {"question_id": 2, "answerIds": [5, 6, 7]}
    ]
  }'
```

### Get answers for a pillar
```bash
curl -X GET "https://api.example.com/api/user-answers/methodology/1/pillar/2" \
  -H "Authorization: Bearer your-jwt-token"
``` 