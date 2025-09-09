<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Hijrah App API",
 *     version="1.0.0",
 *     description="Complete API documentation for Hijrah App - A comprehensive assessment and program management system"
 * )
 *
 * @OA\SecurityScheme(
 *      type="http",
 *      description="Use a bearer token to access this endpoint",
 *      name="Authorization",
 *      in="header",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      securityScheme="bearerAuth"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and registration endpoints"
 * )
 * @OA\Tag(
 *     name="Password Management",
 *     description="Password reset and recovery endpoints"
 * )
 * @OA\Tag(
 *     name="Methodologies",
 *     description="Methodology management and navigation endpoints"
 * )
 * @OA\Tag(
 *     name="Questions",
 *     description="Question retrieval endpoints for assessments"
 * )
 * @OA\Tag(
 *     name="User Answers",
 *     description="Submit and retrieve user answers for assessments"
 * )
 * @OA\Tag(
 *     name="Programs",
 *     description="Program progress and step management endpoints"
 * )
 * @OA\Tag(
 *     name="User Management",
 *     description="User management endpoints (Admin only)"
 * )

 *
 * @OA\Post(
 *     path="/api/auth/signup",
 *     summary="User registration",
 *     description="Register a new user account",
 *     operationId="userSignup",
 *     tags={"Authentication"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User registration data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"name", "email", "password", "gender", "birthdate"},
 *
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *             @OA\Property(property="birthdate", type="string", format="date", example="1990-01-01")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully, verification required",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Registration successful. Please verify your account."),
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer")
 *         )
 *     ),
 *
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/auth/login",
 *     summary="User login",
 *     description="Authenticate user with email and password",
 *     operationId="userLogin",
 *     tags={"Authentication"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User login credentials",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email", "password"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="email", type="string"),
 *                 @OA\Property(property="role", type="string")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Invalid credentials"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/auth/login/firebase",
 *     summary="Firebase login",
 *     description="Authenticate user using Firebase ID token",
 *     operationId="firebaseLogin",
 *     tags={"Authentication"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Firebase authentication data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"firebase_token"},
 *
 *             @OA\Property(property="firebase_token", type="string", example="firebase_id_token_here")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Firebase login successful",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Invalid Firebase token"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/auth/login/google",
 *     summary="Google OAuth login",
 *     description="Authenticate user using Google OAuth access token",
 *     operationId="googleLogin",
 *     tags={"Authentication"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Google OAuth data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"access_token"},
 *
 *             @OA\Property(property="access_token", type="string", example="google_access_token_here")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Google login successful",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Invalid Google token"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/auth/signup/complete",
 *     summary="Complete user registration",
 *     description="Complete user registration after initial signup",
 *     operationId="completeSignup",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Additional registration data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="additional_info", type="string", example="Any additional information")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Registration completed successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Registration completed successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/auth/otp/verify",
 *     summary="Verify OTP",
 *     description="Verify OTP code for account verification",
 *     operationId="verifyOTP",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="OTP verification data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"otp"},
 *
 *             @OA\Property(property="otp", type="string", example="123456")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OTP verified successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Account verified successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=400, description="Invalid or expired OTP"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/auth/otp/resend",
 *     summary="Resend OTP",
 *     description="Resend OTP code for account verification",
 *     operationId="resendOTP",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="OTP resent successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="OTP sent successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== PASSWORD MANAGEMENT ENDPOINTS ====================
 *
 * @OA\Post(
 *     path="/api/password/forget",
 *     summary="Request password reset",
 *     description="Send OTP email for password reset",
 *     operationId="forgetPassword",
 *     tags={"Password Management"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Email for password reset",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Successful OTP email sent",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Reset code sent to your email")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Email not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/password/otp/verify",
 *     summary="Verify password reset OTP",
 *     description="Verify OTP code for password reset",
 *     operationId="verifyPasswordOTP",
 *     tags={"Password Management"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Email and OTP for verification",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email", "otp"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="otp", type="string", example="123456")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OTP verified successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Invalid or expired OTP"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/password/reset",
 *     summary="Reset password",
 *     description="Set new password after OTP verification",
 *     operationId="resetPassword",
 *     tags={"Password Management"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="New password",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"password"},
 *
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Password reset successful",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== USER MANAGEMENT ENDPOINTS ====================
 *
 * @OA\Post(
 *     path="/api/user",
 *     summary="Create new user",
 *     description="Create a new user account (Admin only)",
 *     operationId="createUser",
 *     tags={"User Management"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User creation data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"name", "email", "password", "role", "gender", "birthdate"},
 *
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="role", type="string", enum={"SuperAdmin", "Admin", "Expert", "Customer"}, example="Customer"),
 *             @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *             @OA\Property(property="birthdate", type="string", format="date", example="1990-01-01")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User created successfully"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="email", type="string"),
 *                 @OA\Property(property="role", type="string"),
 *                 @OA\Property(property="created_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=403, description="Insufficient permissions (Admin role required)"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/user/all",
 *     summary="Get all users",
 *     description="Retrieve a list of all users (Admin only)",
 *     operationId="getAllUsers",
 *     tags={"User Management"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number for pagination",
 *         required=false,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         description="Number of users per page",
 *         required=false,
 *
 *         @OA\Schema(type="integer", example=20)
 *     ),
 *
 *     @OA\Parameter(
 *         name="role",
 *         in="query",
 *         description="Filter by user role",
 *         required=false,
 *
 *         @OA\Schema(type="string", enum={"SuperAdmin", "Admin", "Expert", "Customer"})
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="users",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com"),
 *                     @OA\Property(property="role", type="string", example="Customer"),
 *                     @OA\Property(property="gender", type="string", example="male"),
 *                     @OA\Property(property="birthdate", type="string", format="date", example="1990-01-01"),
 *                     @OA\Property(property="is_verified", type="boolean", example=true),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="pagination",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="total_pages", type="integer", example=5),
 *                 @OA\Property(property="total_users", type="integer", example=100),
 *                 @OA\Property(property="per_page", type="integer", example=20)
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=403, description="Insufficient permissions (Admin role required)"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Delete(
 *     path="/api/user",
 *     summary="Delete user",
 *     description="Delete a user account (SuperAdmin only)",
 *     operationId="deleteUser",
 *     tags={"User Management"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User deletion data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"user_id"},
 *
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="reason", type="string", example="Account termination requested by user")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="User deleted successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User deleted successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=403, description="Insufficient permissions (SuperAdmin role required)"),
 *     @OA\Response(response=404, description="User not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== PROGRAMS ENDPOINTS ====================
 *
 * @OA\Get(
 *     path="/api/program/suggested",
 *     summary="Get suggested programs",
 *     description="Retrieve programs suggested for the authenticated user",
 *     operationId="getSuggestedPrograms",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/Program")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/program/my",
 *     summary="Get user's programs",
 *     description="Retrieve programs that the authenticated user has interacted with, along with their status",
 *     operationId="getMyPrograms",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/Program")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/program/{programId}",
 *     summary="Get program details",
 *     description="Retrieve detailed information about a specific program",
 *     operationId="getProgram",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ProgramDetailed")
 *     ),
 *
 *     @OA\Response(response=404, description="Program not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/program/{programId}/start",
 *     summary="Start a program",
 *     description="Start a program for the authenticated user. Creates a user-program relationship with 'in_progress' status.",
 *     operationId="startProgram",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Program started successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Program started successfully")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Program not found or already started",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Program not found or already started")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/program/{programId}/complete",
 *     summary="Complete a program",
 *     description="Mark a program as completed for the authenticated user. Updates the user-program relationship status to 'completed'.",
 *     operationId="completeProgram",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Program completed successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Program completed successfully")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Program not found or not in progress",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Program not found or not in progress")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== STEP PROGRESS ENDPOINTS ====================
 *
 * @OA\Get(
 *     path="/api/program/{programId}/steps",
 *     summary="Get program steps",
 *     description="Retrieve all steps for a specific program",
 *     operationId="getProgramSteps",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/StepResource")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/program/{programId}/step/{stepId}",
 *     summary="Get step details",
 *     description="Retrieve detailed information about a specific step",
 *     operationId="getStep",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         description="Step ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/StepDetailedResource")
 *     ),
 *
 *     @OA\Response(response=404, description="Step not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/program/{programId}/step/{stepId}/start",
 *     summary="Start a step",
 *     description="Start a step for the authenticated user. Creates step progress tracking with 'in_progress' status.",
 *     operationId="startStep",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         description="Step ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Step started successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Step started successfully")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Error starting step",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Error starting step")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/program/{programId}/step/{stepId}/complete",
 *     summary="Complete a step",
 *     description="Complete a step with type-specific data. Journal steps require 'thought', Quiz steps require 'score', Challenge steps require 'challenges_done' and 'percentage'.",
 *     operationId="completeStep",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         description="Step ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=false,
 *         description="Step completion data (varies by step type)",
 *
 *         @OA\JsonContent(
 *             oneOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/JournalStepCompletionRequest"),
 *                 @OA\Schema(ref="#/components/schemas/QuizStepCompletionRequest"),
 *                 @OA\Schema(ref="#/components/schemas/ChallengeStepCompletionRequest")
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Step completed successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Step completed successfully")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Error completing step",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Error completing step")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Step not found",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Step not found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation failed"),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 example={"thought": {"The thought field is required."}}
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/program/{programId}/step/{stepId}/challenge-progress",
 *     summary="Update challenge progress",
 *     description="Update the progress of challenge-type steps by tracking individual challenges completed. Automatically calculates percentage based on total challenges and sets step to 'in_progress' status.",
 *     operationId="updateChallengeProgress",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         description="Step ID (must be a challenge-type step)",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Challenge progress data",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ChallengeProgressRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Challenge progress updated successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ChallengeProgressResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Invalid step type or other error",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Invalid step type for challenge progress")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation failed"),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 example={"challenges_done": {"The challenges done field is required."}}
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== METHODOLOGIES ENDPOINTS ====================
 *
 * @OA\Get(
 *     path="/api/methodology/all",
 *     summary="Get all methodologies",
 *     description="Retrieve a list of all active methodologies",
 *     operationId="getAllMethodologies",
 *     tags={"Methodologies"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/Methodology")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}",
 *     summary="Get methodology details",
 *     description="Retrieve detailed information about a specific methodology",
 *     operationId="getMethodology",
 *     tags={"Methodologies"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/MethodologyDetailed")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/section/{sectionNumber}",
 *     summary="Get methodology by section",
 *     description="Retrieve methodology information for a specific section (for two-section methodologies)",
 *     operationId="getMethodologyBySection",
 *     tags={"Methodologies"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="sectionNumber",
 *         in="path",
 *         description="Section number (1 or 2)",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Methodology Name"),
 *             @OA\Property(property="section", type="integer", example=1),
 *             @OA\Property(property="pillars", type="array", @OA\Items(ref="#/components/schemas/Pillar")),
 *             @OA\Property(property="modules", type="array", @OA\Items(ref="#/components/schemas/Module"))
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology or section not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}",
 *     summary="Get pillar details",
 *     description="Retrieve detailed information about a specific pillar within a methodology",
 *     operationId="getPillar",
 *     tags={"Methodologies"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/PillarDetailed")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology or pillar not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/module/{moduleId}",
 *     summary="Get module details",
 *     description="Retrieve detailed information about a specific module within a methodology",
 *     operationId="getModule",
 *     tags={"Methodologies"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Module")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology or module not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}/module/{moduleId}",
 *     summary="Get pillar module details",
 *     description="Retrieve detailed information about a specific module within a pillar and methodology",
 *     operationId="getPillarModule",
 *     tags={"Methodologies"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Module")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology, pillar, or module not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== QUESTIONS ENDPOINTS ====================
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/questions",
 *     summary="Get methodology questions",
 *     description="Retrieve all questions for a specific methodology",
 *     operationId="getMethodologyQuestions",
 *     tags={"Questions"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/Question")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}/questions",
 *     summary="Get pillar questions",
 *     description="Retrieve all questions for a specific pillar within a methodology",
 *     operationId="getPillarQuestions",
 *     tags={"Questions"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/Question")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology or pillar not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/module/{moduleId}/questions",
 *     summary="Get module questions",
 *     description="Retrieve all questions for a specific module within a methodology",
 *     operationId="getModuleQuestions",
 *     tags={"Questions"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/Question")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology or module not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}/module/{moduleId}/questions",
 *     summary="Get pillar module questions",
 *     description="Retrieve all questions for a specific module within a pillar and methodology",
 *     operationId="getPillarModuleQuestions",
 *     tags={"Questions"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(ref="#/components/schemas/Question")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology, pillar, or module not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== USER ANSWERS ENDPOINTS ====================
 *
 * @OA\Post(
 *     path="/api/methodology/{methodologyId}/answers",
 *     summary="Submit methodology answers",
 *     description="Submit user answers for a methodology assessment",
 *     operationId="submitMethodologyAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User answers data",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Answers submitted successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/answers",
 *     summary="Get methodology answers",
 *     description="Retrieve user answers for a methodology assessment",
 *     operationId="getMethodologyAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UserAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology not found or no answers submitted"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}/answers",
 *     summary="Submit pillar answers",
 *     description="Submit user answers for a pillar assessment",
 *     operationId="submitPillarAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User answers data",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Pillar answers submitted successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology or pillar not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}/answers",
 *     summary="Get pillar answers",
 *     description="Retrieve user answers for a pillar assessment",
 *     operationId="getPillarAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UserAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology, pillar not found or no answers submitted"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/methodology/{methodologyId}/module/{moduleId}/answers",
 *     summary="Submit module answers",
 *     description="Submit user answers for a module assessment",
 *     operationId="submitModuleAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User answers data",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Module answers submitted successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology or module not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/module/{moduleId}/answers",
 *     summary="Get module answers",
 *     description="Retrieve user answers for a module assessment",
 *     operationId="getModuleAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UserAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology, module not found or no answers submitted"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}/module/{moduleId}/answers",
 *     summary="Submit pillar module answers",
 *     description="Submit user answers for a module within a pillar assessment",
 *     operationId="submitPillarModuleAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="User answers data",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Pillar module answers submitted successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SubmitAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology, pillar, or module not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/methodology/{methodologyId}/pillar/{pillarId}/module/{moduleId}/answers",
 *     summary="Get pillar module answers",
 *     description="Retrieve user answers for a module within a pillar assessment",
 *     operationId="getPillarModuleAnswers",
 *     tags={"User Answers"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="methodologyId",
 *         in="path",
 *         description="Methodology ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="pillarId",
 *         in="path",
 *         description="Pillar ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="moduleId",
 *         in="path",
 *         description="Module ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UserAnswersResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology, pillar, module not found or no answers submitted"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 */
class SwaggerInfo {}
