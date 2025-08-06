# Methodology API Endpoints

This document describes the API endpoints for managing methodologies in the Hijrah application.

## Endpoints

### Get All Methodologies (Basic)

**GET** `/api/methodology/all`

Returns a list of all methodologies without nested relations (pillars, modules, questions).

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Methodology Simple",
            "description": "A simple methodology description",
            "definition": "A simple methodology definition",
            "objectives": "The objectives of this methodology",
            "type": "simple",
            "first_section_name": null,
            "second_section_name": null,
            "pillars_definition": null,
            "modules_definition": null,
            "tags": [],
            "pillars": [],
            "modules": [],
            "questions": [],
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "message": "Methodologies fetched successfully"
}
```

### Get Single Methodology (Detailed)

**GET** `/api/methodology/{id}`

Returns a specific methodology by ID with full nested relations including all pillars, modules, questions, and their answers.

### Get Methodology by Section

**GET** `/api/methodology/{id}/section/{sectionNumber}`

Returns a specific methodology by ID with pillars from a specific section only. The section number must be 1 or 2. **This endpoint only works with methodologies of type 'twoSection'.**

**Parameters:**
- `id` (integer): The ID of the methodology
- `sectionNumber` (integer): The section number (1 or 2)

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Methodology Two Section",
        "description": "A two-section methodology description",
        "definition": "A two-section methodology definition",
        "objectives": "The objectives of this methodology",
        "type": "twoSection",
        "first_section_name": "Section 1",
        "second_section_name": "Section 2",
        "pillars_definition": null,
        "modules_definition": null,
        "tags": [],
        "pillars": [
            {
                "id": 1,
                "name": "Section 1 Pillar 1",
                "description": "Pillar description",
                "definition": "Pillar definition",
                "objectives": "Pillar objectives",
                "tags": [],
                "section": "first",
                "modules": [
                    {
                        "id": 1,
                        "name": "Module 1",
                        "description": "Module description",
                        "definition": "Module definition",
                        "objectives": "Module objectives",
                        "tags": [],
                        "questions": [
                            {
                                "id": 1,
                                "title": "Yes/No Question",
                                "type": "YesNo",
                                "tags": [],
                                "answers": [
                                    {
                                        "id": 1,
                                        "title": "Yes",
                                        "tags": []
                                    },
                                    {
                                        "id": 2,
                                        "title": "No",
                                        "tags": []
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "questions": [
                    {
                        "id": 2,
                        "title": "Pillar Question",
                        "type": "YesNo",
                        "tags": [],
                        "answers": [
                            {
                                "id": 1,
                                "title": "Yes",
                                "tags": []
                            },
                            {
                                "id": 2,
                                "title": "No",
                                "tags": []
                            }
                        ]
                    }
                ]
            }
        ],
        "modules": [],
        "questions": [],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "message": "Methodology section fetched successfully"
}
```

**Error Response (400 - Invalid Section):**
```json
{
    "success": false,
    "message": "Invalid section number. Must be 1 or 2"
}
```

**Error Response (400 - Not Two Section Type):**
```json
{
    "success": false,
    "message": "Methodology must be of two-section type to use section endpoints"
}
```

**Parameters:**
- `id` (integer): The ID of the methodology

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Methodology Simple",
        "description": "A simple methodology description",
        "definition": "A simple methodology definition",
        "objectives": "The objectives of this methodology",
        "type": "simple",
        "first_section_name": null,
        "second_section_name": null,
        "pillars_definition": null,
        "modules_definition": null,
        "tags": [],
        "pillars": [],
                    "modules": [
                {
                    "id": 1,
                    "name": "Module 1",
                    "description": "Module description",
                    "definition": "Module definition",
                    "objectives": "Module objectives",
                    "tags": [],
                    "questions": [
                        {
                            "id": 1,
                            "title": "Yes/No Question",
                            "type": "YesNo",
                            "tags": [],
                            "answers": [
                                {
                                    "id": 1,
                                    "title": "Yes",
                                    "tags": []
                                },
                                {
                                    "id": 2,
                                    "title": "No",
                                    "tags": []
                                }
                            ]
                        }
                    ]
                }
            ],
            "questions": [
                {
                    "id": 1,
                    "title": "Yes/No Question",
                    "type": "YesNo",
                    "tags": [],
                    "answers": [
                        {
                            "id": 1,
                            "title": "Yes",
                            "tags": []
                        },
                        {
                            "id": 2,
                            "title": "No",
                            "tags": []
                        }
                    ]
                }
            ],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "message": "Methodology fetched successfully"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Methodology not found"
}
```

## Response Levels

### Basic Response (List Endpoint)
The `/api/methodology/all` endpoint returns methodologies with basic information only:
- No nested relations (pillars, modules, questions)
- Faster response time
- Suitable for listing and overview purposes

### Detailed Response (Single Endpoint)
The `/api/methodology/{id}` endpoint returns full nested relations:
- All pillars with their modules and questions
- All modules with their questions
- All questions with their answers
- Complete hierarchy down to answer level
- Suitable for detailed view and form building

### Section-Specific Response (Section Endpoint)
The `/api/methodology/{id}/section/{sectionNumber}` endpoint returns methodology with pillars from a specific section only:
- **Only works with methodologies of type 'twoSection'**
- Only pillars from the specified section (1 or 2)
- Full nested relations for those pillars (modules, questions, answers)
- Other methodology data remains complete
- Suitable for section-specific workflows

## Methodology Types

The methodology can have one of the following types:
- `simple`: Basic methodology with modules
- `complex`: Methodology with pillars and modules
- `twoSection`: Methodology with two sections, each containing pillars

## Data Structure

### Methodology Fields
- `id`: Unique identifier
- `name`: Methodology name
- `description`: Detailed description
- `definition`: Methodology definition
- `objectives`: Methodology objectives
- `type`: Methodology type (simple/complex/twoSection)
- `first_section_name`: Name of the first section (for twoSection type)
- `second_section_name`: Name of the second section (for twoSection type)
- `pillars_definition`: Definition of pillars
- `modules_definition`: Definition of modules
- `tags`: Array of tags
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### Related Data
- `pillars`: Array of pillars associated with the methodology
- `modules`: Array of modules associated with the methodology
- `questions`: Array of questions associated with the methodology

## Error Handling

All endpoints return consistent error responses with the following structure:
```json
{
    "success": false,
    "message": "Error message",
    "error": "Detailed error information (in development)"
}
```

## Localization

The API supports localization through the `locale` middleware. Messages are available in both English and Arabic. 