<?php

namespace App\Http\Controllers\API\Auth;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Models\User;
use App\Constants\HTTPStatusCode;
use App\Models\Address;
use App\Models\Bank;
use App\Models\Cook;
use App\Models\Customers;
use App\Models\Riders;
use App\Models\Staff;
use App\Models\Vendor;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    use ResponseTraits, ValidationTraits, CommonQueries;
    /**
     * @param $request
     */
    public function adminSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->adminSignUpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // Create Admin User
        $user = User::create(array_merge($request->only([Constants::NAME, Constants::MOBILE, Constants::PASSWORD, Constants::EMAIL])));

        // assign the role to the created user #roles
        $user->assignRole($request->role);

        return $this->successResponse(true, "", Constants::CREATED_SUCCESS, 201);
    }

    /**
     * @param $request
     */
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), $this->adminLoginVerify());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        $crendentials = $request->only('email', 'password');
        $token = auth()->attempt($crendentials);

        // Token Generation Failed it will #returns
        if ($token == null) return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // Logged User Details Framing
        $user = User::with('roles')->where('email', $request->get('email'))->first();
        if ($user) return $this->tokenResponse(true, $user, $token, Constants::LOGIN_SUCCESS, 200);
        else {
            auth()->logout();
            return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        }
    }

    /**
     * @param $request
     * validate mobile number and generate otp
     */
    public function adminGetOTP(Request $request)
    {
        $validator = Validator::make($request->all(), $this->adminOtpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // Existing OTP Check
        if ($this->existingOtp($request->get(Constants::MOBILE), Constants::ADMIN_GUARD)) return $this->errorResponse(false, "", Constants::OTP_ALREADY_SENT, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // send otp common function
        $this->sendOtp($request->get(Constants::MOBILE), Constants::ADMIN_GUARD);
        return $this->successResponse(true, "", Constants::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function adminVerifyOTP(Request $request)
    {
        # code...
        $verified = $this->verifyOtp($request->get(Constants::MOBILE), $request->get(Constants::OTP), Constants::ADMIN_GUARD);
        if ($verified) {
            $crendentials = array_merge($request->only([Constants::MOBILE]), array(Constants::PASSWORD => env(Constants::ADMIN_PASSWORD)));
            $token = auth()->attempt($crendentials);

            // Token Generation Failed it will #returns
            if (!$token) return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

            // Logged User Details Framing
            $user = User::role([Constants::SUPER_ADMIN_ROLE, Constants::ADMIN])->with('roles')->where('mobile', $request->get(Constants::MOBILE))->first();
            if ($user) return $this->tokenResponse(true, $user, $token, Constants::LOGIN_SUCCESS, 200);
            else {
                auth()->logout();
                return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
            }
        }
        return $this->errorResponse(false, "", Constants::OTP_EXPIRED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
    }

    ###### Cook Authentication Section of Code Started
    /**
     * @param $request
     * create cooks
     */
    public function cookSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->cookSignupValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        //Modules #id
        $module = $this->getModuleIdBasedOnCode(Constants::ACTIVE);

        // Create Cook User
        $user = Cook::create(array_merge($request->only([Constants::NAME, Constants::MOBILE]), array(Constants::PASSWORD => env(Constants::COOK_PASSWORD), Constants::STATUS => $module)));

        // assign the role to the created user #roles
        $user->assignRole(Constants::COOK_ROLE);

        // send otp common function
        $this->sendOtp($request->get(Constants::MOBILE), Constants::COOK_GUARD);
        return $this->successResponse(true, "", Constants::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * validate mobile number and generate otp
     */
    public function cookGetOTP(Request $request)
    {
        $validator = Validator::make($request->all(), $this->cookOtpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        if ($this->existingOtp($request->get(Constants::MOBILE), Constants::COOK_GUARD)) return $this->errorResponse(false, "", Constants::OTP_ALREADY_SENT, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // send otp common function
        $this->sendOtp($request->get(Constants::MOBILE), Constants::COOK_GUARD);
        return $this->successResponse(true, "", Constants::OTP_SENT_SUCCESS, 200);
    }
    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function cookVerifyOTP(Request $request)
    {
        # code...
        $verified = $this->verifyOtp($request->get(Constants::MOBILE), $request->get(Constants::OTP), Constants::COOK_GUARD);
        if ($verified) {
            $crendentials = array_merge($request->only([Constants::MOBILE]), array(Constants::PASSWORD => env(Constants::COOK_PASSWORD)));
            $token = auth()->guard(Constants::COOK_GUARD)->attempt($crendentials);

            // Token Generation Failed it will #returns
            if (!$token) return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

            // Logged User Details Framing
            $user = Cook::role(Constants::COOK_ROLE)->with('roles')->where('mobile', $request->get(Constants::MOBILE))->first();
            if ($user) return $this->tokenResponse(true, $user, $token, Constants::LOGIN_SUCCESS, 200);
            else {
                auth()->logout();
                return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
            }
        }
        return $this->errorResponse(false, "", Constants::OTP_EXPIRED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
    }

    ###### Customer Authentication Section of Code Started
    /**
     * @param $request
     * create customers
     */
    public function customerSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->customerSignupValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        Customers::create(array_merge($request->only(['name', 'dob', 'email', 'mobile']), [
            'referral_code' => $this->generateRandomString(),
            'signup_with'   => $request->get('referral'),
            'password'      => env(Constants::CUSTOMER_PASSWORD)
        ]));

        // Referral Points add #section
        if ($request->get('referral') != '') {
            $refer = Customers::where('referral_code', $request->get('referral'))->first();
            if ($refer != null) {
                $refer->points += 20;
                $refer->save();
            }
        }
        return $this->successResponse(true, "", Constants::CREATED_SUCCESS, 201);
    }

    /**
     * @param $request
     * validate mobile number and generate otp
     */
    public function customerGetOTP(Request $request)
    {
        $validator = Validator::make($request->all(), $this->customerOtpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        $user = Customers::where('mobile', $request->mobile)->first();
        //Modules #id
        $module = $this->getModuleIdBasedOnCode(Constants::ACTIVE);

        if ($user) {
            if ($this->existingOtp($request->get(Constants::MOBILE), Constants::CUSTOMER_GUARD)) return $this->errorResponse(false, "", Constants::OTP_ALREADY_SENT, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        } else {
            $user = Customers::create(['mobile' => $request->mobile, Constants::PASSWORD => env(Constants::CUSTOMER_PASSWORD), 'status' => $module, 'referral_code' => $this->generateRandomString()]);
            // assign the role to the created user #roles
            $user->assignRole(Constants::CUSTOMER_ROLE);
        }
        // send otp common function
        $this->sendOtp($request->get(Constants::MOBILE), Constants::CUSTOMER_GUARD);
        return $this->successResponse(true, $user, Constants::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function customerVerifyOTP(Request $request)
    {
        # code...
        $verified = $this->verifyOtp($request->get(Constants::MOBILE), $request->get(Constants::OTP), Constants::CUSTOMER_GUARD);
        if ($verified) {
            $crendentials = array_merge($request->only([Constants::MOBILE]), array(Constants::PASSWORD => env(Constants::CUSTOMER_PASSWORD)));
            $token = auth()->guard(Constants::CUSTOMER_GUARD)->attempt($crendentials);

            // Token Generation Failed it will #returns
            if (!$token) return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

            // Logged User Details Framing
            $user = Customers::role(Constants::CUSTOMER_ROLE)->with(['roles', 'address'])->where('mobile', $request->get(Constants::MOBILE))->first();
            if ($user) {
                $user->fcm_token = $request->fcm_token;
                $user->ip_address = $request->ip_address;
                $user->device_name = $request->device_name;
                $user->save();
                return $this->tokenResponse(true, $user, $token, Constants::LOGIN_SUCCESS, 200);
            } else {
                auth()->logout();
                return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
            }
        }
        return $this->errorResponse(false, "", Constants::OTP_EXPIRED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
    }

    /**
     * @param $request
     * validate mobile number and generate otp
     */
    public function riderGetOTP(Request $request)
    {
        $validator = Validator::make($request->all(), $this->riderOtpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        if ($this->existingOtp($request->get(Constants::MOBILE), Constants::RIDER_GUARD)) return $this->errorResponse(false, "", Constants::OTP_ALREADY_SENT, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // send otp common function
        $this->sendOtp($request->get(Constants::MOBILE), Constants::RIDER_GUARD);
        return $this->successResponse(true, "", Constants::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function riderLogin(Request $request)
    {
        $validator = Validator::make($request->all(), $this->adminLoginVerify());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        $crendentials = $request->only('email', 'password');
        $token = auth(Constants::RIDER_GUARD)->attempt($crendentials);

        // Token Generation Failed it will #returns
        if ($token == null) return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // Logged User Details Framing
        $user = Riders::with('roles')->where('email', $request->get('email'))->first();
        if ($user) return $this->tokenResponse(true, $user, $token, Constants::LOGIN_SUCCESS, 200);
        else {
            auth()->logout();
            return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        }
    }


    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function staffLogin(Request $request)
    {
        $crendentials = $request->only('email', 'password');
        $token = auth()->guard(Constants::COOK_GUARD)->attempt($crendentials);

        // Token Generation Failed it will #returns
        if ($token == null) return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // Logged User Details Framing
        $user = Staff::with('roles')->where('email', $request->get('email'))->first();
        if ($user) return $this->tokenResponse(true, $user, $token, Constants::LOGIN_SUCCESS, 200);
        else {
            auth()->logout();
            return $this->errorResponse(false, "", Constants::UNAUTHORIZED, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        }
    }

    #### Logout
    public function logout()
    {
        auth()->logout();
        return $this->successResponse(true, "", Constants::LOGOUT_SUCCESS);
    }

    #### GET OTP
    public function getOtp(Request $request)
    {
        $otp = VerificationCode::where([['mobile', $request->mobile], ['guard',  $request->guard]])->latest()->pluck('otp')->first();
        return $this->successResponse(true, $otp, Constants::GET_SUCCESS);
    }
}
