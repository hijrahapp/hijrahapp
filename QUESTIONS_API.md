# Questions API Endpoints

This document describes the API endpoints for managing questions in the Hijrah application.

## Endpoints

### Get Questions by Context

**GET** `/api/questions/by-context`

Returns a list of questions and their answers based on the specified context and context ID.

**Parameters:**
- `context` (string, required): The type of context. Must be one of: `methodology`, `pillar`, `module`
- `context_id` (integer, required): The ID of the specific context (methodology, pillar, or module)

**Example Request:**
```
GET /api/questions/by-context?context=methodology&context_id=1
```

**Example Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Methodology Question 1",
            "type": "YesNo",
            "tags": ["Security", "Compliance"],
            "answers": [
                {
                    "id": 1,
                    "title": "Yes"
                },
                {
                    "id": 2,
                    "title": "No"
                }
            ]
        },
        {
            "id": 2,
            "title": "Methodology Question 2",
            "type": "TrueFalse",
            "tags": ["Privacy"],
            "answers": [
                {
                    "id": 3,
                    "title": "True"
                },
                {
                    "id": 4,
                    "title": "False"
                }
            ]
        }
    ],
    "message": "Questions fetched successfully",
    "context": {
        "type": "methodology",
        "id": 1
    }
}
```

**Error Responses:**

**Validation Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "context": ["The context field is required."],
        "context_id": ["The context id field is required."]
    }
}
```

**Invalid Context Type (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "context": ["The selected context is invalid."]
    }
}
```

**Context Not Found (500):**
```json
{
    "success": false,
    "message": "Error fetching questions",
    "error": "Methodology with ID 999 not found"
}
```

## Context Types

### Methodology Context
- **Context Type**: `methodology`
- **Context ID**: The ID of the methodology
- **Returns**: All questions directly attached to the methodology

### Pillar Context
- **Context Type**: `pillar`
- **Context ID**: The ID of the pillar
- **Returns**: All questions directly attached to the pillar

### Module Context
- **Context Type**: `module`
- **Context ID**: The ID of the module
- **Returns**: All questions directly attached to the module

## Question Types

The questions can have one of the following types:
- `YesNo`: Yes/No questions
- `TrueFalse`: True/False questions
- `MCQSingle`: Multiple choice questions with single answer
- `MCQMultiple`: Multiple choice questions with multiple answers
- `Rating1to5`: Rating questions from 1 to 5
- `Rating1to10`: Rating questions from 1 to 10
- `ScaleAgreeDisagree`: Scale questions with agree/disagree options

## Data Structure

### Question Fields
- `id`: Unique identifier
- `title`: Question title
- `type`: Question type (see types above)
- `tags`: Array of tag titles (not IDs)
- `answers`: Array of possible answers

### Answer Fields
- `id`: Unique identifier
- `title`: Answer title

## Usage Examples

### Get Questions for a Specific Methodology
```
GET /api/questions/by-context?context=methodology&context_id=1
```

### Get Questions for a Specific Pillar
```
GET /api/questions/by-context?context=pillar&context_id=5
```

### Get Questions for a Specific Module
```
GET /api/questions/by-context?context=module&context_id=10
```

## Notes

- The endpoint returns questions with their associated answers
- Tag titles are returned instead of tag IDs for better readability
- If no questions are found for the specified context, an empty array is returned
- The endpoint validates both the context type and context ID
- Invalid context types or non-existent context IDs will return appropriate error responses 