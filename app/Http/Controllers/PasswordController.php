<?php

namespace App\Http\Controllers;

use App\Http\Services\OTPService;
use App\Http\Services\UserService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PasswordController
{
    public function __construct(private UserService $userService, private OTPService $otpService) {}

    /**
     * @OA\Post(
     *      path="/api/password/forget",
     *      summary="Send OTP email to reset password",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"email"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful OTP email sent",
     *      )
     *  )
     */
    public function forgetPassword(Request $request) {
        $this->otpService->resendPasswordOTP($request['email']);
        return response()->json(['message' => __('messages.otp_sent')], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/password/otp/verify",
     *      summary="Verifies OTP to reset password",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"email", "otp"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *              @OA\Property(property="otp", type="string", example="123456")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful OTP email sent",
     *          @OA\JsonContent(
     *               @OA\Property(property="access_token", type="string"),
     *               @OA\Property(property="token_type", type="string", example="bearer"),
     *               @OA\Property(property="user", ref="#/components/schemas/User")
     *          )
     *      ),
     *      @OA\Response(
     *           response=401,
     *           description="Unauthorized"
     *       ),
     *       @OA\Response(
     *           response=403,
     *           description="Forbidden"
     *       )
     *  )
     */
    public function verifyOTP(Request $request) {
        return $this->otpService->verifyPasswordOTP($request['email'], $request['otp']);
    }

    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     summary="Resets user password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               type="object",
     *               required={"password"},
     *               @OA\Property(property="password", type="string", example="password")
     *           )
     *     ),
     *     @OA\Response(
     *           response=200,
     *           description="Successful password reset",
     *           @OA\JsonContent(
     *                @OA\Property(property="access_token", type="string"),
     *                @OA\Property(property="token_type", type="string", example="bearer"),
     *                @OA\Property(property="user", ref="#/components/schemas/User")
     *           )
     *      ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function resetPassword(Request $request) {
        return response()->json($this->userService->resetPassword($request->authUser, $request->password));
    }
}
