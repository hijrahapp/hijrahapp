# Firebase Authentication Setup

## Overview
This Laravel application now supports Firebase Authentication. Users can authenticate using Firebase ID tokens, and the system will automatically create or update user records in the database.

## Setup Instructions

### 1. Firebase Project Setup
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or select existing project
3. Enable Authentication in the Firebase Console
4. Add authentication providers (Google, Email/Password, etc.)

### 2. Get Firebase Credentials
1. In Firebase Console, go to Project Settings
2. Go to Service Accounts tab
3. Click "Generate new private key"
4. Download the JSON file
5. Place it in `storage/firebase-credentials.json`

### 3. Environment Configuration
Copy the environment variables from `env-template.txt` to your `.env` file:

```bash
# Firebase Configuration
FIREBASE_PROJECT_ID=your-firebase-project-id
FIREBASE_CREDENTIALS_FILE=storage/firebase-credentials.json
FIREBASE_DATABASE_URL=https://your-project-id.firebaseio.com
FIREBASE_STORAGE_BUCKET=your-project-id.appspot.com

# JWT Configuration
JWT_SECRET=your-jwt-secret-key-here
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

### 4. Run Database Migration
```bash
php artisan migrate
```

### 5. Clear Configuration Cache
```bash
php artisan config:clear
php artisan config:cache
```

## API Usage

### Firebase Login Endpoint
```
POST /api/auth/login/firebase
```

**Request Body:**
```json
{
  "token": "firebase-id-token-here",
  "gender": "male", // optional
  "birthDate": "1990-01-01" // optional
}
```

**Response:**
```json
{
  "access_token": "jwt-token-here",
  "token_type": "bearer",
  "user": {
    "id": "user-uuid",
    "name": "User Name",
    "email": "user@example.com",
    "role": "Customer",
    "firebase_uid": "firebase-user-uid",
    "profile_picture": "https://example.com/photo.jpg"
  }
}
```

## How It Works

1. **Token Verification**: The system verifies the Firebase ID token
2. **User Lookup**: Checks if user exists in database by email
3. **User Creation**: If user doesn't exist, creates new user with Firebase UID
4. **User Update**: If user exists, updates Firebase UID and other details
5. **JWT Generation**: Generates Laravel JWT token for API access

## Security Features

- Firebase ID token verification
- Automatic user creation/update
- JWT token generation for API access
- Email verification status sync
- Profile picture sync from Firebase

## Error Handling

- Invalid/expired Firebase tokens return 401
- Missing tokens return 400
- Server errors return 500
- User not found returns 404

## Testing

You can test the Firebase authentication using:

1. **Firebase SDK** in your frontend
2. **Postman** with Firebase ID tokens
3. **Firebase Auth Emulator** for local development

## Troubleshooting

1. **Invalid Credentials**: Check Firebase credentials file path and content
2. **Token Verification Failed**: Ensure Firebase project ID matches
3. **Database Errors**: Run migrations and check database connection
4. **JWT Errors**: Verify JWT_SECRET is set in environment 