<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * API Schemas for Hijrah App
 *
 * This file contains all OpenAPI schema definitions for requests and responses
 * used throughout the Hijrah App API documentation.
 *
 * // ==================== COMMON SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error message"),
 *     @OA\Property(property="error", type="string", example="Detailed error information")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *
 *         @OA\AdditionalProperties(
 *             type="array",
 *
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="total_pages", type="integer", example=5),
 *     @OA\Property(property="total_items", type="integer", example=100),
 *     @OA\Property(property="per_page", type="integer", example=20)
 * )
 *
 * // ==================== USER SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="UserList",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(
 *         property="users",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/User")
 *     ),
 *
 *     @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta")
 * )
 *
 * // ==================== AUTHENTICATION SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="SignupRequest",
 *     type="object",
 *     required={"name", "email", "password", "gender", "birthdate"},
 *
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *     @OA\Property(property="birthdate", type="string", format="date", example="1990-01-01")
 * )
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123")
 * )
 *
 * @OA\Schema(
 *     schema="FirebaseLoginRequest",
 *     type="object",
 *     required={"firebase_token"},
 *
 *     @OA\Property(property="firebase_token", type="string", example="firebase_id_token_here")
 * )
 *
 * @OA\Schema(
 *     schema="GoogleLoginRequest",
 *     type="object",
 *     required={"access_token"},
 *
 *     @OA\Property(property="access_token", type="string", example="google_access_token_here")
 * )
 *
 * @OA\Schema(
 *     schema="OTPVerifyRequest",
 *     type="object",
 *     required={"otp"},
 *
 *     @OA\Property(property="otp", type="string", example="123456")
 * )
 *
 * @OA\Schema(
 *     schema="CompleteSignupRequest",
 *     type="object",
 *
 *     @OA\Property(property="additional_info", type="string", example="Any additional information")
 * )
 *
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *     @OA\Property(property="token_type", type="string", example="bearer"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *     schema="SignupResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Registration successful. Please verify your account."),
 *     @OA\Property(property="access_token", type="string"),
 *     @OA\Property(property="token_type", type="string", example="bearer")
 * )
 *
 * // ==================== PASSWORD MANAGEMENT SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="ForgetPasswordRequest",
 *     type="object",
 *     required={"email"},
 *
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com")
 * )
 *
 * @OA\Schema(
 *     schema="PasswordOTPRequest",
 *     type="object",
 *     required={"email", "otp"},
 *
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="otp", type="string", example="123456")
 * )
 *
 * @OA\Schema(
 *     schema="ResetPasswordRequest",
 *     type="object",
 *     required={"password"},
 *
 *     @OA\Property(property="password", type="string", format="password", example="newpassword123")
 * )
 *
 * // ==================== USER MANAGEMENT SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="CreateUserRequest",
 *     type="object",
 *     required={"name", "email", "password", "role", "gender", "birthdate"},
 *
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123"),
 *     @OA\Property(property="role", type="string", enum={"SuperAdmin", "Admin", "Expert", "Customer"}, example="Customer"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *     @OA\Property(property="birthdate", type="string", format="date", example="1990-01-01")
 * )
 *
 * @OA\Schema(
 *     schema="DeleteUserRequest",
 *     type="object",
 *     required={"user_id"},
 *
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="reason", type="string", example="Account termination requested by user")
 * )
 *
 * @OA\Schema(
 *     schema="CreateUserResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="User created successfully"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 *
 * // ==================== METHODOLOGY SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="Methodology",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Digital Transformation Methodology"),
 *     @OA\Property(property="description", type="string", example="Comprehensive digital transformation assessment"),
 *     @OA\Property(property="definition", type="string", example="A structured approach to evaluating digital maturity"),
 *     @OA\Property(property="objectives", type="string", example="Assess current state and identify improvement opportunities"),
 *     @OA\Property(property="type", type="string", enum={"standard", "twoSection"}, example="standard"),
 *     @OA\Property(property="imgUrl", type="string", nullable=true, example="https://example.com/methodology.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="MethodologyDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Methodology"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="pillars",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/Pillar")
 *             ),
 *
 *             @OA\Property(
 *                 property="modules",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/Module")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="Pillar",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Technology Infrastructure"),
 *     @OA\Property(property="description", type="string", example="Assessment of current technology infrastructure"),
 *     @OA\Property(property="section", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="PillarDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Pillar"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="modules",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/Module")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="Module",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Cloud Infrastructure"),
 *     @OA\Property(property="description", type="string", example="Cloud infrastructure assessment module"),
 *     @OA\Property(property="definition", type="string", example="Evaluates cloud adoption and management capabilities"),
 *     @OA\Property(property="objectives", type="string", example="Assess cloud readiness and optimization opportunities"),
 *     @OA\Property(property="imgUrl", type="string", nullable=true, example="https://example.com/module.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * // ==================== PROGRAM SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="Program",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Digital Excellence Program"),
 *     @OA\Property(property="description", type="string", example="Comprehensive digital transformation program"),
 *     @OA\Property(property="definition", type="string", example="A structured learning and assessment program"),
 *     @OA\Property(property="objectives", type="string", example="Develop digital capabilities and assess progress"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ProgramDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Program"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="objectives",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/Objective"),
 *                 description="Program objectives as structured data"
 *             ),
 *
 *             @OA\Property(property="objectives_count", type="integer", example=8),
 *             @OA\Property(
 *                 property="objectives_count_by_type",
 *                 type="object",
 *                 description="Count of objectives grouped by type",
 *                 @OA\Property(property="journal", type="integer", example=2),
 *                 @OA\Property(property="article", type="integer", example=1),
 *                 @OA\Property(property="advice", type="integer", example=1),
 *                 @OA\Property(property="daily_mission", type="integer", example=1),
 *                 @OA\Property(property="quiz", type="integer", example=2),
 *                 @OA\Property(property="video", type="integer", example=1),
 *                 @OA\Property(property="audio", type="integer", example=0),
 *                 @OA\Property(property="book", type="integer", example=0),
 *                 @OA\Property(property="challenge", type="integer", example=0)
 *             ),
 *             @OA\Property(
 *                 property="modules",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/LinkedModule")
 *             ),
 *
 *             @OA\Property(property="modules_count", type="integer", example=5)
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LinkedModule",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Module"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="pillar_id", type="integer", nullable=true, example=2),
 *             @OA\Property(property="min_score", type="number", format="float", example=25.50),
 *             @OA\Property(property="max_score", type="number", format="float", example=85.75),
 *             @OA\Property(property="linked_at", type="string", format="date-time")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="AvailableModule",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Module"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="pillar_id", type="integer", nullable=true, example=2),
 *             @OA\Property(property="pillar_name", type="string", nullable=true, example="Technology Infrastructure")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CreateProgramRequest",
 *     type="object",
 *     required={"name", "description", "definition", "objectives"},
 *
 *     @OA\Property(property="name", type="string", example="New Program"),
 *     @OA\Property(property="description", type="string", example="Detailed program description"),
 *     @OA\Property(property="definition", type="string", example="Program definition and scope"),
 *     @OA\Property(property="objectives", type="string", example="Program learning objectives")
 * )
 *
 * @OA\Schema(
 *     schema="AttachModuleRequest",
 *     type="object",
 *     required={"module_id", "methodology_id"},
 *
 *     @OA\Property(property="module_id", type="integer", example=1),
 *     @OA\Property(property="methodology_id", type="integer", example=1),
 *     @OA\Property(property="pillar_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="min_score", type="number", format="float", example=25.50),
 *     @OA\Property(property="max_score", type="number", format="float", example=85.75)
 * )
 *
 * @OA\Schema(
 *     schema="DetachModuleRequest",
 *     type="object",
 *     required={"module_id", "methodology_id"},
 *
 *     @OA\Property(property="module_id", type="integer", example=1),
 *     @OA\Property(property="methodology_id", type="integer", example=1),
 *     @OA\Property(property="pillar_id", type="integer", nullable=true, example=2)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateModuleScoresRequest",
 *     type="object",
 *     required={"module_id", "methodology_id", "min_score", "max_score"},
 *
 *     @OA\Property(property="module_id", type="integer", example=1),
 *     @OA\Property(property="methodology_id", type="integer", example=1),
 *     @OA\Property(property="pillar_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="min_score", type="number", format="float", example=30.00),
 *     @OA\Property(property="max_score", type="number", format="float", example=90.00)
 * )
 *
 * @OA\Schema(
 *     schema="AvailableModulesResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/AvailableModule")
 *     )
 * )
 *
 * // ==================== QUESTION SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="Question",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="text", type="string", example="How would you rate your current cloud infrastructure?"),
 *     @OA\Property(property="type", type="string", enum={"multiple_choice", "rating", "text"}, example="multiple_choice"),
 *     @OA\Property(
 *         property="answers",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Answer")
 *     ),
 *
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Answer",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="text", type="string", example="Excellent"),
 *     @OA\Property(property="value", type="integer", example=5),
 *     @OA\Property(property="order", type="integer", example=1)
 * )
 *
 * // ==================== USER ANSWER SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="SubmitAnswersRequest",
 *     type="object",
 *     required={"answers"},
 *
 *     @OA\Property(
 *         property="answers",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/UserAnswerSubmission")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserAnswerSubmission",
 *     type="object",
 *     required={"question_id", "answer_id"},
 *
 *     @OA\Property(property="question_id", type="integer", example=1),
 *     @OA\Property(property="answer_id", type="integer", example=1),
 *     @OA\Property(property="value", type="integer", example=5)
 * )
 *
 * @OA\Schema(
 *     schema="SubmitAnswersResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Answers submitted successfully"),
 *     @OA\Property(property="score", type="number", format="float", example=85.5),
 *     @OA\Property(property="total_questions", type="integer", example=10),
 *     @OA\Property(property="answered_questions", type="integer", example=10)
 * )
 *
 * @OA\Schema(
 *     schema="UserAnswer",
 *     type="object",
 *
 *     @OA\Property(property="question_id", type="integer", example=1),
 *     @OA\Property(property="answer_id", type="integer", example=1),
 *     @OA\Property(property="value", type="integer", example=5),
 *     @OA\Property(property="question_text", type="string", example="How would you rate...?"),
 *     @OA\Property(property="answer_text", type="string", example="Excellent"),
 *     @OA\Property(property="submitted_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="UserAnswersResponse",
 *     type="object",
 *
 *     @OA\Property(property="methodology_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="pillar_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="module_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="score", type="number", format="float", example=85.5),
 *     @OA\Property(property="completion_percentage", type="number", format="float", example=100.0),
 *     @OA\Property(
 *         property="answers",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/UserAnswer")
 *     ),
 *
 *     @OA\Property(property="submitted_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * // ==================== OBJECTIVE SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="Objective",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="program_id", type="integer", example=1),
 *     @OA\Property(property="program_name", type="string", example="Digital Transformation Program"),
 *     @OA\Property(property="name", type="string", example="Complete Module Assessment"),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"journal", "article", "advice", "daily_mission", "quiz", "video", "audio", "book", "challenge"},
 *         example="quiz"
 *     ),
 *     @OA\Property(property="type_display", type="string", example="Quiz"),
 *     @OA\Property(property="time_to_finish", type="integer", example=30),
 *     @OA\Property(
 *         property="time_type",
 *         type="string",
 *         enum={"hours", "days", "weeks", "months"},
 *         example="minutes"
 *     ),
 *     @OA\Property(property="time_type_display", type="string", example="Minutes"),
 *     @OA\Property(property="formatted_duration", type="string", example="30 Minutes"),
 *     @OA\Property(property="header", type="string", nullable=true, example="Journal Entry Header"),
 *     @OA\Property(property="content", type="string", nullable=true, example="Article content or mission description"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Detailed description of the objective"),
 *     @OA\Property(property="content_url", type="string", nullable=true, format="url", example="https://example.com/video.mp4"),
 *     @OA\Property(property="content_image", type="string", nullable=true, format="url", example="https://example.com/book-cover.jpg"),
 *     @OA\Property(
 *         property="advices",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(type="string"),
 *         example={"Follow daily routine", "Practice mindfulness", "Set clear goals"}
 *     ),
 *
 *     @OA\Property(
 *         property="challenges",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(type="string"),
 *         example={"30-day challenge", "Weekly reflection", "Goal achievement"}
 *     ),
 *
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ObjectiveDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Objective"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(property="program", ref="#/components/schemas/Program")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CreateObjectiveRequest",
 *     type="object",
 *     required={"program_id", "name", "type", "time_to_finish", "time_type"},
 *
 *     @OA\Property(property="program_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Complete Module Assessment"),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"journal", "article", "advice", "daily_mission", "quiz", "video", "audio", "book", "challenge"},
 *         example="quiz"
 *     ),
 *     @OA\Property(property="time_to_finish", type="integer", minimum=1, example=30),
 *     @OA\Property(
 *         property="time_type",
 *         type="string",
 *         enum={"hours", "days", "weeks", "months"},
 *         example="minutes"
 *     ),
 *     @OA\Property(property="header", type="string", nullable=true, example="Journal Entry Header"),
 *     @OA\Property(property="content", type="string", nullable=true, example="Article content or mission description"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Detailed description of the objective"),
 *     @OA\Property(property="content_url", type="string", nullable=true, format="url", example="https://example.com/video.mp4"),
 *     @OA\Property(property="content_image", type="string", nullable=true, format="url", example="https://example.com/book-cover.jpg"),
 *     @OA\Property(
 *         property="advices",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(type="string"),
 *         example={"Follow daily routine", "Practice mindfulness", "Set clear goals"}
 *     ),
 *
 *     @OA\Property(
 *         property="challenges",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(type="string"),
 *         example={"30-day challenge", "Weekly reflection", "Goal achievement"}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UpdateObjectiveRequest",
 *     type="object",
 *
 *     @OA\Property(property="program_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Updated Module Assessment"),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"journal", "article", "advice", "daily_mission", "quiz", "video", "audio", "book", "challenge"},
 *         example="quiz"
 *     ),
 *     @OA\Property(property="time_to_finish", type="integer", minimum=1, example=45),
 *     @OA\Property(
 *         property="time_type",
 *         type="string",
 *         enum={"hours", "days", "weeks", "months"},
 *         example="minutes"
 *     ),
 *     @OA\Property(property="header", type="string", nullable=true, example="Updated Journal Entry Header"),
 *     @OA\Property(property="content", type="string", nullable=true, example="Updated article content or mission description"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Updated detailed description of the objective"),
 *     @OA\Property(property="content_url", type="string", nullable=true, format="url", example="https://example.com/updated-video.mp4"),
 *     @OA\Property(property="content_image", type="string", nullable=true, format="url", example="https://example.com/updated-book-cover.jpg"),
 *     @OA\Property(
 *         property="advices",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(type="string"),
 *         example={"Updated daily routine", "Enhanced mindfulness practice", "Refined goal setting"}
 *     ),
 *
 *     @OA\Property(
 *         property="challenges",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(type="string"),
 *         example={"Extended 30-day challenge", "Bi-weekly reflection", "Advanced goal achievement"}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="JournalData",
 *     type="object",
 *     required={"header"},
 *
 *     @OA\Property(property="header", type="string", example="Daily Reflection Journal")
 * )
 *
 * @OA\Schema(
 *     schema="ArticleData",
 *     type="object",
 *     required={"content"},
 *
 *     @OA\Property(property="content", type="string", example="Article content in HTML or Markdown format...")
 * )
 *
 * @OA\Schema(
 *     schema="AdviceData",
 *     type="object",
 *     required={"header", "advices"},
 *
 *     @OA\Property(property="header", type="string", example="Best Practices for Success"),
 *     @OA\Property(
 *         property="advices",
 *         type="array",
 *
 *         @OA\Items(type="string"),
 *         example="array of advice strings"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="DailyMissionData",
 *     type="object",
 *     required={"header", "content"},
 *
 *     @OA\Property(property="header", type="string", example="Today's Challenge"),
 *     @OA\Property(property="content", type="string", example="Complete your morning routine and reflect on your goals")
 * )
 *
 * @OA\Schema(
 *     schema="QuizData",
 *     type="object",
 *     required={"questions"},
 *
 *     @OA\Property(
 *         property="questions",
 *         type="array",
 *
 *         @OA\Items(type="integer"),
 *         example="array of question IDs",
 *         description="Array of question IDs from the question bank"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="VideoData",
 *     type="object",
 *     required={"content_url", "description"},
 *
 *     @OA\Property(property="content_url", type="string", format="url", example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"),
 *     @OA\Property(property="description", type="string", example="Educational video about the topic")
 * )
 *
 * @OA\Schema(
 *     schema="AudioData",
 *     type="object",
 *     required={"content_url", "description"},
 *
 *     @OA\Property(property="content_url", type="string", format="url", example="https://soundcloud.com/example/audio-lesson"),
 *     @OA\Property(property="description", type="string", example="Audio lesson covering key concepts")
 * )
 *
 * @OA\Schema(
 *     schema="BookData",
 *     type="object",
 *     required={"content_url", "description"},
 *
 *     @OA\Property(property="content_url", type="string", format="url", example="https://example.com/book.pdf"),
 *     @OA\Property(property="cover_image", type="string", format="url", example="https://example.com/book-cover.jpg"),
 *     @OA\Property(property="description", type="string", example="Comprehensive guide to the subject matter")
 * )
 *
 * @OA\Schema(
 *     schema="ChallengeData",
 *     type="object",
 *     required={"description", "challenges"},
 *
 *     @OA\Property(property="description", type="string", example="30-day improvement challenge"),
 *     @OA\Property(
 *         property="challenges",
 *         type="array",
 *
 *         @OA\Items(type="string"),
 *         example="array of challenge strings"
 *     )
 * )
 *
 * // ==================== SCORE SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="ScoreBreakdown",
 *     type="object",
 *
 *     @OA\Property(property="methodology_score", type="number", format="float", nullable=true, example=82.5),
 *     @OA\Property(property="pillar_score", type="number", format="float", nullable=true, example=78.0),
 *     @OA\Property(property="module_score", type="number", format="float", nullable=true, example=85.0),
 *     @OA\Property(property="total_possible", type="number", format="float", example=100.0),
 *     @OA\Property(property="questions_answered", type="integer", example=15),
 *     @OA\Property(property="total_questions", type="integer", example=15)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateProgramRequest",
 *     type="object",
 *     description="Request schema for updating a program",
 *
 *     @OA\Property(property="name", type="string", example="Updated Program Name"),
 *     @OA\Property(property="description", type="string", example="Updated program description"),
 *     @OA\Property(property="definition", type="string", example="Updated program definition"),
 *     @OA\Property(property="objectives", type="string", example="Updated program objectives")
 * )
 *
 * @OA\Schema(
 *     schema="ReorderObjectivesRequest",
 *     type="object",
 *     required={"objective_ids"},
 *
 *     @OA\Property(
 *         property="objective_ids",
 *         type="array",
 *
 *         @OA\Items(type="integer"),
 *         example={1, 3, 2, 4},
 *         description="Array of objective IDs in the desired order"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserProgram",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Program"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 enum={"in_progress", "completed"},
 *                 example="in_progress",
 *                 description="User's progress status for this program"
 *             ),
 *             @OA\Property(
 *                 property="started_at",
 *                 type="string",
 *                 format="date-time",
 *                 nullable=true,
 *                 example="2024-01-15T10:30:00Z",
 *                 description="When the user started this program"
 *             ),
 *             @OA\Property(
 *                 property="completed_at",
 *                 type="string",
 *                 format="date-time",
 *                 nullable=true,
 *                 example="2024-01-25T14:45:00Z",
 *                 description="When the user completed this program"
 *             ),
 *             @OA\Property(
 *                 property="objectives",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/Objective"),
 *                 description="Program objectives"
 *             )
 *         )
 *     }
 * )
 *
 * // ==================== STEP SCHEMAS ====================
 *
 * @OA\Schema(
 *     schema="Step",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="program_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Daily Reflection"),
 *     @OA\Property(property="description", type="string", example="Write about your day and experiences"),
 *     @OA\Property(property="type", type="string", enum={"journal", "quiz", "challenge"}, example="journal"),
 *     @OA\Property(property="order", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="StepResource",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Step"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(property="program", ref="#/components/schemas/Program")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="StepDetailedResource",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/StepResource"),
 *         @OA\Schema(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="user_progress",
 *                 ref="#/components/schemas/UserStepProgress",
 *                 nullable=true,
 *                 description="User's progress for this step"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserStepProgress",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="program_id", type="integer", example=1),
 *     @OA\Property(property="step_id", type="integer", example=1),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"not_started", "in_progress", "completed"},
 *         example="in_progress"
 *     ),
 *     @OA\Property(
 *         property="thought",
 *         type="string",
 *         nullable=true,
 *         example="Today I learned about patience and resilience",
 *         description="For journal type steps"
 *     ),
 *     @OA\Property(
 *         property="score",
 *         type="integer",
 *         nullable=true,
 *         example=85,
 *         description="For quiz type steps"
 *     ),
 *     @OA\Property(
 *         property="challenges_done",
 *         type="integer",
 *         nullable=true,
 *         example=7,
 *         description="For challenge type steps"
 *     ),
 *     @OA\Property(
 *         property="percentage",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         example=87.5,
 *         description="For challenge type steps"
 *     ),
 *     @OA\Property(
 *         property="started_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2024-01-15T10:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="completed_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2024-01-15T11:00:00Z"
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="JournalStepCompletionRequest",
 *     type="object",
 *     required={"thought"},
 *
 *     @OA\Property(
 *         property="thought",
 *         type="string",
 *         maxLength=2000,
 *         example="Today I reflected on my spiritual journey and realized the importance of consistency in my daily practices.",
 *         description="Reflection text for journal step completion"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="QuizStepCompletionRequest",
 *     type="object",
 *     required={"score"},
 *
 *     @OA\Property(
 *         property="score",
 *         type="integer",
 *         minimum=0,
 *         maximum=100,
 *         example=85,
 *         description="Quiz score between 0-100"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ChallengeStepCompletionRequest",
 *     type="object",
 *     required={"challenges_done", "percentage"},
 *
 *     @OA\Property(
 *         property="challenges_done",
 *         type="integer",
 *         minimum=0,
 *         example=7,
 *         description="Number of challenges completed"
 *     ),
 *     @OA\Property(
 *         property="percentage",
 *         type="number",
 *         format="float",
 *         minimum=0,
 *         maximum=100,
 *         example=87.5,
 *         description="Completion percentage"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ChallengeProgressRequest",
 *     type="object",
 *     required={"challenges_done"},
 *
 *     @OA\Property(
 *         property="challenges_done",
 *         type="integer",
 *         minimum=0,
 *         example=5,
 *         description="Number of challenges completed so far"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ChallengeProgressResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Challenge progress updated successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="challenges_done", type="integer", example=5),
 *         @OA\Property(property="total_challenges", type="integer", example=10),
 *         @OA\Property(property="percentage", type="number", format="float", example=50.0),
 *         @OA\Property(property="status", type="string", enum={"not_started", "in_progress", "completed"}, example="in_progress")
 *     )
 * )
 */
class Schemas {}
