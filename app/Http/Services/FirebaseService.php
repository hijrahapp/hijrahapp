<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Utils\JWTUtils;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Http\JsonResponse;

class FirebaseService
{
    protected $userRepo;
    protected $roleRepo;

    public function __construct(UserRepository $userRepo, RoleRepository $roleRepo)
    {
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
    }

    /**
     * Authenticate user with Firebase token and create/update user in database
     *
     * @param array $request
     * @return JsonResponse
     */
    public function login($request): JsonResponse
    {
        $firebaseToken = $request['token'] ?? null;

        if (empty($firebaseToken)) {
            return response()->json([
                'error' => __('messages.firebase_token_missing'),
                'message' => __('messages.provide_valid_firebase_token')
            ], 404);
        }

        try {
            // Verify the Firebase ID token
            $verifiedIdToken = Firebase::auth()->verifyIdToken($firebaseToken);
            $uid = $verifiedIdToken->claims()->get('sub');

            // Get user details from Firebase
            $firebaseUser = Firebase::auth()->getUser($uid);
            $email = $firebaseUser->email;
            $displayName = $firebaseUser->displayName;
            $photoUrl = $firebaseUser->photoUrl;

            // Check if user exists in our database
            $user = $this->userRepo->findByEmail($email);

            $isNewUser = $user == null;

            if (!$user) {
                // Create new user if not exists
                $customerRole = $this->roleRepo->findByRoleName(RoleName::Customer);

                $userData = [
                    'name' => $displayName ?? explode('@', $email)[0],
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => null, // Firebase users don't have passwords
                    'active' => true,
                    'roleId' => $customerRole->id,
                    'firebase_uid' => $uid,
                ];

                // Add optional fields if provided
                if (isset($request['gender'])) {
                    $userData['gender'] = $request['gender'];
                }

                if (isset($request['birthDate'])) {
                    $userData['birthDate'] = $request['birthDate'];
                }

                if ($photoUrl) {
                    $userData['profile_picture'] = $photoUrl;
                }

                $user = $this->userRepo->create($userData);
            } else {
                // Update existing user's Firebase UID and other details
                $updateData = [
                    'firebase_uid' => $uid,
                    'email_verified_at' => now(),
                    'active' => true,
                ];

                if ($photoUrl) {
                    $updateData['profile_picture'] = $photoUrl;
                }

                if ($displayName && $user->name !== $displayName) {
                    $updateData['name'] = $displayName;
                }

                $this->userRepo->update($user->id, $updateData);
                $user->refresh();
            }

            $response = JWTUtils::generateTokenResponse($user);
            $response['isNewUser'] = $isNewUser;

            // Generate JWT token for the user
            return response()->json($response);

        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'error' => __('messages.invalid_firebase_token'),
                'message' => __('messages.invalid_or_expired_firebase_token'),
                'details' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.authentication_failed'),
                'message' => __('messages.error_firebase_authentication'),
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user details from Firebase UID
     *
     * @param string $uid
     * @return JsonResponse
     */
    public function getUserByUid(string $uid): JsonResponse
    {
        try {
            $firebaseUser = Firebase::auth()->getUser($uid);

            return response()->json([
                'uid' => $firebaseUser->uid,
                'email' => $firebaseUser->email,
                'displayName' => $firebaseUser->displayName,
                'photoUrl' => $firebaseUser->photoUrl,
                'emailVerified' => $firebaseUser->emailVerified,
                'disabled' => $firebaseUser->disabled,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.user_not_found'),
                'message' => __('messages.firebase_user_not_found'),
                'details' => $e->getMessage()
            ], 404);
        }
    }
}
