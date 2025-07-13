<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Utils\JWTUtils;
use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $firebaseAuth;
    protected $userRepo;
    protected $roleRepo;

    public function __construct(Factory $firebaseAuth, UserRepository $userRepo, RoleRepository $roleRepo)
    {
        $this->firebaseAuth = $firebaseAuth->createAuth();
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
    }

    public function login($request)
    {
        $firebaseToken = $request['token'];

        if (empty($firebaseToken)) {
            return response()->json(['error' => 'ID token is missing.'], 400);
        }

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($firebaseToken);
            $uid = $verifiedIdToken->claims()->get('sub');
            $firebaseUser = $this->firebaseAuth->getUser($uid);
            $email = $firebaseUser->email;
            $displayName = $firebaseUser->displayName;

            $user = $this->userRepo->findByEmail($email);

            if (!$user) {
                $customerRole = $this->roleRepo->findByRoleName(RoleName::Customer);
                $user = $this->userRepo->create([
                    'name' => $displayName ?? explode('@', $email)[0],
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => null,
                    'gender' => $request['gender'],
                    'birthDate' => $request['birthDate'],
                    'active' => true,
                    'roleId' => $customerRole->id
                ]);
            }

            return response()->json(JWTUtils::generateTokenResponse($user));

        } catch (FailedToVerifyToken $e) {
            return response()->json(['error' => 'Invalid or expired ID token: ' . $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
