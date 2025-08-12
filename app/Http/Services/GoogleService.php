<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Utils\JWTUtils;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Http\JsonResponse;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;

class GoogleService
{
    protected $userRepo;
    protected $roleRepo;

    public function __construct(UserRepository $userRepo, RoleRepository $roleRepo)
    {
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
    }

    /**
     * Authenticate user with Google access token and create/update user in database
     *
     * @param array $request
     * @return JsonResponse
     */
    public function login($request): JsonResponse
    {
        $accessToken = $request['token'] ?? null;
        if (empty($accessToken)) {
            return response()->json([
                'error' => __('messages.google_token_missing'),
                'message' => __('messages.provide_valid_google_token')
            ], 404);
        }

        try {
            // Sign in with Google access token using Kreait
            $signInResult = Firebase::auth()->signInWithIdpAccessToken('google.com', $accessToken);
            $idToken = $signInResult->idToken();
            $verifiedIdToken = Firebase::auth()->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');
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

            return response()->json($response);
        } catch (FailedToSignIn $e) {
            return response()->json([
                'error' => __('messages.failed_google_signin'),
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('messages.authentication_failed'),
                'message' => __('messages.error_google_authentication'),
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
