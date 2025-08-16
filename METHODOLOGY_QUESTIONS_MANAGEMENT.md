# Methodology Questions Management

This document describes the new methodology questions management functionality that allows administrators to manage questions within methodologies.

## Overview

The methodology questions management page provides a comprehensive interface for managing questions within a specific methodology. It includes features for viewing, adding, editing, deleting, and reordering questions.

## Features

### 1. View Questions
- **Question List**: Displays all questions associated with the methodology in a table format
- **Question Details**: Shows question text, type, answers, pillar/module assignment, weight, and status
- **Search & Filter**: Search by question title, filter by question type, and filter by tags
- **Pagination**: Supports pagination for large question sets

### 2. Add New Question
- **Question Selection**: Search and select from existing questions in the system
- **Weight Configuration**: Set question weight (must be 100% for single question)
- **Pillar/Module Assignment**: Assign question to a specific pillar or module within the methodology
- **Answer Weights**: Configure weights for each answer option (must sum to 100%)
- **Validation**: Ensures weights are properly configured before saving

### 3. Edit Question
- **Weight Updates**: Modify question weight and answer weights
- **Assignment Changes**: Change pillar/module assignment
- **Validation**: Maintains weight validation rules

### 4. Delete Question
- **Confirmation Modal**: Requires confirmation before deletion
- **Safe Deletion**: Only removes question from methodology, doesn't delete the question itself
- **Cleanup**: Removes all associations with pillars and modules

### 5. Question Ordering
- **Drag & Drop**: Reorder questions using drag and drop interface
- **Visual Feedback**: Shows drop zones and visual indicators during reordering
- **Persistent Order**: Saves new order to database

## Technical Implementation

### Components
- **Livewire Component**: `App\Livewire\Homepage\MethodologyQuestions`
- **View**: `resources/views/livewire/homepage/methodology-questions.blade.php`
- **Route**: `/app/methodology/{methodologyId}/questions`

### Database Changes
- Added `sequence` column to `methodology_question` pivot table for ordering
- Uses existing weight columns in pivot tables

### Key Methods

#### Component Methods
- `methodologyQuestions()`: Computed property for paginated questions
- `loadAvailableQuestions()`: Loads questions not yet assigned to methodology
- `selectQuestion()`: Handles question selection in add modal
- `saveQuestion()`: Validates and saves question with weights
- `reorderQuestions()`: Handles drag and drop reordering
- `deleteQuestion()`: Safely removes question from methodology

#### Validation Rules
- Question weight must be 100% (for single question management)
- Answer weights must sum to 100%
- Question must be selected before saving
- Methodology must exist

### JavaScript Features
- **Drag & Drop**: Custom implementation for question reordering
- **Livewire Integration**: Seamless integration with Livewire events
- **Visual Feedback**: Drop zones and opacity changes during drag

## Usage

### Accessing the Page
1. Navigate to the methodologies table
2. Click the "Manage General Questions" action for any methodology
3. This will redirect to the questions management page

### Adding a Question
1. Click "Add Question" button
2. Search and select an existing question
3. Configure question weight (100%)
4. Assign to pillar/module or leave as general
5. Set answer weights (must sum to 100%)
6. Click "Add Question" to save

### Editing a Question
1. Click the actions menu (three dots) on any question row
2. Select "Edit Question"
3. Modify weights and assignments as needed
4. Click "Update Question" to save changes

### Reordering Questions
1. Drag any question row by the move icon
2. Drop it in the desired position
3. The new order is automatically saved

### Deleting a Question
1. Click the actions menu on any question row
2. Select "Delete Question"
3. Confirm deletion in the modal
4. Question is removed from methodology

## File Structure

```
app/Livewire/Homepage/MethodologyQuestions.php          # Main component
resources/views/livewire/homepage/methodology-questions.blade.php  # View
routes/web.php                                          # Route definition
database/migrations/2025_08_15_192435_add_sequence_to_methodology_question_table.php  # DB migration
```

## Dependencies

- Laravel Livewire
- Existing Question, Answer, Methodology, Pillar, and Module models
- KTUI CSS framework for styling
- Custom JavaScript for drag and drop functionality

## Future Enhancements

1. **Bulk Operations**: Add/remove multiple questions at once
2. **Question Dependencies**: Support for dependent questions
3. **Advanced Weighting**: More sophisticated weight calculation algorithms
4. **Question Templates**: Pre-configured question sets
5. **Import/Export**: CSV import/export functionality
6. **Question Preview**: Preview questions as they would appear to users
