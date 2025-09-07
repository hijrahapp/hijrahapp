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
 *     description="Program management and module linking endpoints"
 * )
 * @OA\Tag(
 *     name="Objectives",
 *     description="Program objectives management endpoints"
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
 *     path="/api/programs/all",
 *     summary="Get all programs",
 *     description="Retrieve a list of all programs",
 *     operationId="getAllPrograms",
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
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Sample Program"),
 *                 @OA\Property(property="description", type="string", example="Program description"),
 *                 @OA\Property(property="definition", type="string", example="Program definition"),
 *                 @OA\Property(property="objectives", type="string", example="Program objectives"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/programs/{programId}",
 *     summary="Get program details",
 *     description="Retrieve detailed information about a specific program including linked modules",
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Sample Program"),
 *             @OA\Property(property="description", type="string", example="Program description"),
 *             @OA\Property(property="definition", type="string", example="Program definition"),
 *             @OA\Property(property="objectives", type="string", example="Program objectives"),
 *             @OA\Property(
 *                 property="modules",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Module Name"),
 *                     @OA\Property(property="methodology_id", type="integer", example=1),
 *                     @OA\Property(property="pillar_id", type="integer", nullable=true, example=null),
 *                     @OA\Property(property="min_score", type="number", format="float", example=25.50),
 *                     @OA\Property(property="max_score", type="number", format="float", example=85.75),
 *                     @OA\Property(property="linked_at", type="string", format="date-time")
 *                 )
 *             ),
 *             @OA\Property(property="modules_count", type="integer", example=3),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Program not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/programs",
 *     summary="Create a new program (Admin only)",
 *     description="Create a new program with basic information. Requires Admin role.",
 *     operationId="createProgram",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Program data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"name", "description", "definition", "objectives"},
 *
 *             @OA\Property(property="name", type="string", example="New Program"),
 *             @OA\Property(property="description", type="string", example="Detailed program description"),
 *             @OA\Property(property="definition", type="string", example="Program definition and scope"),
 *             @OA\Property(property="objectives", type="string", example="Program learning objectives")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Program created successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="New Program"),
 *             @OA\Property(property="description", type="string", example="Detailed program description"),
 *             @OA\Property(property="definition", type="string", example="Program definition and scope"),
 *             @OA\Property(property="objectives", type="string", example="Program learning objectives"),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
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
 *
 *                 @OA\AdditionalProperties(
 *                     type="array",
 *
 *                     @OA\Items(type="string")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - Admin role required"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Put(
 *     path="/api/programs/{programId}",
 *     summary="Update program (Admin only)",
 *     description="Update an existing program. Requires Admin role.",
 *     operationId="updateProgram",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Program data to update",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="name", type="string", example="Updated Program Name"),
 *             @OA\Property(property="description", type="string", example="Updated program description"),
 *             @OA\Property(property="definition", type="string", example="Updated program definition"),
 *             @OA\Property(property="objectives", type="string", example="Updated program objectives")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Program updated successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Updated Program Name"),
 *             @OA\Property(property="description", type="string", example="Updated program description"),
 *             @OA\Property(property="definition", type="string", example="Updated program definition"),
 *             @OA\Property(property="objectives", type="string", example="Updated program objectives"),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - Admin role required"),
 *     @OA\Response(response=404, description="Program not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Delete(
 *     path="/api/programs/{programId}",
 *     summary="Delete program (Admin only)",
 *     description="Delete a program and all its associated data. Requires Admin role.",
 *     operationId="deleteProgram",
 *     tags={"Programs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="programId",
 *         in="path",
 *         required=true,
 *         description="Program ID",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Program deleted successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Program deleted successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - Admin role required"),
 *     @OA\Response(response=404, description="Program not found"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/programs/{programId}/modules/attach",
 *     summary="Attach module to program (Admin only)",
 *     description="Link a module to a program with score configuration. Requires Admin role.",
 *     operationId="attachModuleToProgram",
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
 *     @OA\RequestBody(
 *         required=true,
 *         description="Module attachment data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"module_id", "methodology_id"},
 *
 *             @OA\Property(property="module_id", type="integer", example=1),
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="pillar_id", type="integer", nullable=true, example=null),
 *             @OA\Property(property="min_score", type="number", format="float", example=25.50),
 *             @OA\Property(property="max_score", type="number", format="float", example=85.75)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Module attached successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Module attached successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=400, description="Relationship already exists or program not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Delete(
 *     path="/api/programs/{programId}/modules/detach",
 *     summary="Detach module from program (Admin only)",
 *     description="Remove a module link from a program. Requires Admin role.",
 *     operationId="detachModuleFromProgram",
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
 *     @OA\RequestBody(
 *         required=true,
 *         description="Module detachment data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"module_id", "methodology_id"},
 *
 *             @OA\Property(property="module_id", type="integer", example=1),
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="pillar_id", type="integer", nullable=true, example=null)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Module detached successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Module detached successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Program or module relationship not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Put(
 *     path="/api/programs/{programId}/modules/scores",
 *     summary="Update module score configuration (Admin only)",
 *     description="Update the minimum and maximum score configuration for a module linked to a program. Requires Admin role.",
 *     operationId="updateModuleScores",
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
 *     @OA\RequestBody(
 *         required=true,
 *         description="Module score update data",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"module_id", "methodology_id", "min_score", "max_score"},
 *
 *             @OA\Property(property="module_id", type="integer", example=1),
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="pillar_id", type="integer", nullable=true, example=null),
 *             @OA\Property(property="min_score", type="number", format="float", example=30.00),
 *             @OA\Property(property="max_score", type="number", format="float", example=90.00)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Module scores updated successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Module scores updated successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Program not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/programs/methodology/{methodologyId}/available-modules",
 *     summary="Get available modules for a methodology",
 *     description="Retrieve all modules available for linking to programs within a specific methodology",
 *     operationId="getAvailableModules",
 *     tags={"Programs"},
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
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Module Name"),
 *                     @OA\Property(property="description", type="string", example="Module description"),
 *                     @OA\Property(property="methodology_id", type="integer", example=1),
 *                     @OA\Property(property="pillar_id", type="integer", nullable=true, example=null),
 *                     @OA\Property(property="pillar_name", type="string", nullable=true, example="Pillar Name")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/programs/{programId}/methodology/{methodologyId}",
 *     summary="Get program with methodology-specific modules",
 *     description="Retrieve detailed program information with modules linked to a specific methodology",
 *     operationId="getProgramWithMethodologyModules",
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
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Sample Program"),
 *             @OA\Property(property="description", type="string", example="Program description"),
 *             @OA\Property(property="definition", type="string", example="Program definition"),
 *             @OA\Property(property="objectives", type="string", example="Program objectives"),
 *             @OA\Property(
 *                 property="modules",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Module Name"),
 *                     @OA\Property(property="methodology_id", type="integer", example=1),
 *                     @OA\Property(property="pillar_id", type="integer", nullable=true, example=null),
 *                     @OA\Property(property="min_score", type="number", format="float", example=25.50),
 *                     @OA\Property(property="max_score", type="number", format="float", example=85.75)
 *                 )
 *             ),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Program not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * // ==================== OBJECTIVES ENDPOINTS ====================
 *
 * @OA\Get(
 *     path="/api/programs/{programId}/objectives",
 *     summary="Get program objectives",
 *     description="Retrieve all objectives for a specific program",
 *     operationId="getProgramObjectives",
 *     tags={"Objectives"},
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
 *             @OA\Items(ref="#/components/schemas/Objective")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/programs/{programId}/objectives",
 *     summary="Create objective (Admin only)",
 *     description="Create a new objective for a program. Requires Admin role.",
 *     operationId="createObjective",
 *     tags={"Objectives"},
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
 *     @OA\RequestBody(
 *         required=true,
 *         description="Objective data",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CreateObjectiveRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Objective created successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Objective")
 *     ),
 *
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Get(
 *     path="/api/programs/{programId}/objectives/{objectiveId}",
 *     summary="Get objective details",
 *     description="Retrieve detailed information about a specific objective",
 *     operationId="getObjective",
 *     tags={"Objectives"},
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
 *         name="objectiveId",
 *         in="path",
 *         description="Objective ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ObjectiveDetailed")
 *     ),
 *
 *     @OA\Response(response=404, description="Objective not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Put(
 *     path="/api/programs/{programId}/objectives/{objectiveId}",
 *     summary="Update objective (Admin only)",
 *     description="Update an existing objective. Requires Admin role.",
 *     operationId="updateObjective",
 *     tags={"Objectives"},
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
 *         name="objectiveId",
 *         in="path",
 *         description="Objective ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Objective data to update",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdateObjectiveRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Objective updated successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Objective")
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - Admin role required"),
 *     @OA\Response(response=404, description="Objective not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Delete(
 *     path="/api/programs/{programId}/objectives/{objectiveId}",
 *     summary="Delete objective (Admin only)",
 *     description="Delete an objective from a program. Requires Admin role.",
 *     operationId="deleteObjective",
 *     tags={"Objectives"},
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
 *         name="objectiveId",
 *         in="path",
 *         description="Objective ID",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Objective deleted successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     ),
 *
 *     @OA\Response(response=404, description="Objective not found"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - Admin role required"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Post(
 *     path="/api/programs/{programId}/objectives/{objectiveId}/duplicate",
 *     summary="Duplicate objective (Admin only)",
 *     description="Create a copy of an existing objective. Requires Admin role.",
 *     operationId="duplicateObjective",
 *     tags={"Objectives"},
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
 *         name="objectiveId",
 *         in="path",
 *         description="Objective ID to duplicate",
 *         required=true,
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=false,
 *         description="Optional target program ID for the duplicate",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="program_id", type="integer", example=2, description="Target program ID (optional)")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Objective duplicated successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Objective")
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - Admin role required"),
 *     @OA\Response(response=404, description="Objective not found"),
 *     @OA\Response(response=422, description="Validation failed"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 *
 * @OA\Put(
 *     path="/api/programs/{programId}/objectives/reorder",
 *     summary="Reorder objectives (Admin only)",
 *     description="Change the order of objectives within a program. Requires Admin role.",
 *     operationId="reorderObjectives",
 *     tags={"Objectives"},
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
 *     @OA\RequestBody(
 *         required=true,
 *         description="New order of objectives",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ReorderObjectivesRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Objectives reordered successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Objectives reordered successfully")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - Admin role required"),
 *     @OA\Response(response=422, description="Validation failed"),
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
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Methodology Name"),
 *                 @OA\Property(property="description", type="string", example="Methodology description"),
 *                 @OA\Property(property="type", type="string", example="standard"),
 *                 @OA\Property(property="imgUrl", type="string", nullable=true),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Methodology Name"),
 *             @OA\Property(property="description", type="string", example="Detailed methodology description"),
 *             @OA\Property(property="definition", type="string", example="Methodology definition"),
 *             @OA\Property(property="objectives", type="string", example="Methodology objectives"),
 *             @OA\Property(property="type", type="string", example="standard"),
 *             @OA\Property(property="imgUrl", type="string", nullable=true),
 *             @OA\Property(
 *                 property="pillars",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="description", type="string")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="modules",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="description", type="string")
 *                 )
 *             ),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
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
 *             @OA\Property(property="pillars", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="modules", type="array", @OA\Items(type="object"))
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Pillar Name"),
 *             @OA\Property(property="description", type="string", example="Pillar description"),
 *             @OA\Property(
 *                 property="modules",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="description", type="string")
 *                 )
 *             )
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Module Name"),
 *             @OA\Property(property="description", type="string", example="Module description"),
 *             @OA\Property(property="definition", type="string", example="Module definition"),
 *             @OA\Property(property="objectives", type="string", example="Module objectives"),
 *             @OA\Property(property="imgUrl", type="string", nullable=true)
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Module Name"),
 *             @OA\Property(property="description", type="string", example="Module description"),
 *             @OA\Property(property="pillar_context", type="string", example="Pillar-specific context")
 *         )
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
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="text", type="string", example="What is your assessment of...?"),
 *                 @OA\Property(property="type", type="string", example="multiple_choice"),
 *                 @OA\Property(
 *                     property="answers",
 *                     type="array",
 *
 *                     @OA\Items(
 *                         type="object",
 *
 *                         @OA\Property(property="id", type="integer"),
 *                         @OA\Property(property="text", type="string"),
 *                         @OA\Property(property="value", type="integer")
 *                     )
 *                 ),
 *                 @OA\Property(property="order", type="integer", example=1)
 *             )
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
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="text", type="string", example="Pillar-specific question text"),
 *                 @OA\Property(property="type", type="string", example="multiple_choice"),
 *                 @OA\Property(property="answers", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="pillar_context", type="string")
 *             )
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
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="text", type="string", example="Module-specific question text"),
 *                 @OA\Property(property="type", type="string", example="multiple_choice"),
 *                 @OA\Property(property="answers", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="module_context", type="string")
 *             )
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
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="text", type="string", example="Pillar module question text"),
 *                 @OA\Property(property="type", type="string", example="multiple_choice"),
 *                 @OA\Property(property="answers", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="pillar_context", type="string"),
 *                 @OA\Property(property="module_context", type="string")
 *             )
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
 *         @OA\JsonContent(
 *             type="object",
 *             required={"answers"},
 *
 *             @OA\Property(
 *                 property="answers",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="question_id", type="integer", example=1),
 *                     @OA\Property(property="answer_id", type="integer", example=1),
 *                     @OA\Property(property="value", type="integer", example=3)
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Answers submitted successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Answers submitted successfully"),
 *             @OA\Property(property="score", type="number", format="float", example=85.5),
 *             @OA\Property(property="total_questions", type="integer", example=10),
 *             @OA\Property(property="answered_questions", type="integer", example=10)
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="score", type="number", format="float", example=85.5),
 *             @OA\Property(property="completion_percentage", type="number", format="float", example=100.0),
 *             @OA\Property(
 *                 property="answers",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="question_id", type="integer"),
 *                     @OA\Property(property="answer_id", type="integer"),
 *                     @OA\Property(property="value", type="integer"),
 *                     @OA\Property(property="submitted_at", type="string", format="date-time")
 *                 )
 *             ),
 *             @OA\Property(property="submitted_at", type="string", format="date-time")
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *             required={"answers"},
 *
 *             @OA\Property(
 *                 property="answers",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="question_id", type="integer"),
 *                     @OA\Property(property="answer_id", type="integer"),
 *                     @OA\Property(property="value", type="integer")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Pillar answers submitted successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Pillar answers submitted successfully"),
 *             @OA\Property(property="pillar_score", type="number", format="float", example=78.5)
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="pillar_id", type="integer", example=1),
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="pillar_score", type="number", format="float", example=78.5),
 *             @OA\Property(property="answers", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="submitted_at", type="string", format="date-time")
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *             required={"answers"},
 *
 *             @OA\Property(
 *                 property="answers",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="question_id", type="integer"),
 *                     @OA\Property(property="answer_id", type="integer"),
 *                     @OA\Property(property="value", type="integer")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Module answers submitted successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Module answers submitted successfully"),
 *             @OA\Property(property="module_score", type="number", format="float", example=92.0)
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="module_id", type="integer", example=1),
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="module_score", type="number", format="float", example=92.0),
 *             @OA\Property(property="answers", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="submitted_at", type="string", format="date-time")
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *             required={"answers"},
 *
 *             @OA\Property(
 *                 property="answers",
 *                 type="array",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="question_id", type="integer"),
 *                     @OA\Property(property="answer_id", type="integer"),
 *                     @OA\Property(property="value", type="integer")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Pillar module answers submitted successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Pillar module answers submitted successfully"),
 *             @OA\Property(property="module_score", type="number", format="float", example=88.5)
 *         )
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
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="module_id", type="integer", example=1),
 *             @OA\Property(property="pillar_id", type="integer", example=1),
 *             @OA\Property(property="methodology_id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="module_score", type="number", format="float", example=88.5),
 *             @OA\Property(property="answers", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="submitted_at", type="string", format="date-time")
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Methodology, pillar, module not found or no answers submitted"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 */
class SwaggerInfo {}
