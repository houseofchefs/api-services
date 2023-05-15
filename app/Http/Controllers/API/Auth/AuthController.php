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
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    use ResponseTraits, ValidationTraits, CommonQueries;
    /**
     * @param $request
     */
    public function adminSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->adminSignUpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Create Admin User
        $user = User::create(array_merge($request->only([$this->constant::NAME, $this->constant::MOBILE, $this->constant::PASSWORD, $this->constant::EMAIL])));

        // assign the role to the created user #roles
        $user->assignRole($request->role);

        return $this->successResponse(true, "", $this->constant::CREATED_SUCCESS, 201);
    }

    /**
     * @param $request
     */
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), $this->adminLoginVerify());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        $crendentials = $request->only('email', 'password');
        $token = auth()->attempt($crendentials);

        // Token Generation Failed it will #returns
        if ($token == null) return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);

        // Logged User Details Framing
        $user = User::with('roles')->where('email', $request->get('email'))->first();
        if ($user) return $this->tokenResponse(true, $user, $token, $this->constant::LOGIN_SUCCESS, 200);
        else {
            auth()->logout();
            return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);
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
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Existing OTP Check
        if ($this->existingOtp($request->get($this->constant::MOBILE), $this->constant::ADMIN_GUARD)) return $this->errorResponse(false, "", $this->constant::OTP_ALREADY_SENT, $this->http::UNPROCESS_ENTITY_CODE);

        // send otp common function
        $this->sendOtp($request->get($this->constant::MOBILE), $this->constant::ADMIN_GUARD);
        return $this->successResponse(true, "", $this->constant::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function adminVerifyOTP(Request $request)
    {
        # code...
        $verified = $this->verifyOtp($request->get($this->constant::MOBILE), $request->get($this->constant::OTP), $this->constant::ADMIN_GUARD);
        if ($verified) {
            $crendentials = array_merge($request->only([$this->constant::MOBILE]), array($this->constant::PASSWORD => env($this->constant::ADMIN_PASSWORD)));
            $token = auth()->attempt($crendentials);

            // Token Generation Failed it will #returns
            if (!$token) return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);

            // Logged User Details Framing
            $user = User::role([$this->constant::SUPER_ADMIN_ROLE, $this->constant::ADMIN])->with('roles')->where('mobile', $request->get($this->constant::MOBILE))->first();
            if ($user) return $this->tokenResponse(true, $user, $token, $this->constant::LOGIN_SUCCESS, 200);
            else {
                auth()->logout();
                return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);
            }
        }
        return $this->errorResponse(false, "", $this->constant::OTP_EXPIRED, $this->http::UNPROCESS_ENTITY_CODE);
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
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        //Modules #id
        $module = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        // Create Cook User
        $user = Cook::create(array_merge($request->only([$this->constant::NAME, $this->constant::MOBILE]), array($this->constant::PASSWORD => env($this->constant::COOK_PASSWORD), $this->constant::STATUS => $module)));

        // assign the role to the created user #roles
        $user->assignRole($this->constant::COOK_ROLE);

        // send otp common function
        $this->sendOtp($request->get($this->constant::MOBILE), $this->constant::COOK_GUARD);
        return $this->successResponse(true, "", $this->constant::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * validate mobile number and generate otp
     */
    public function cookGetOTP(Request $request)
    {
        $validator = Validator::make($request->all(), $this->cookOtpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        if ($this->existingOtp($request->get($this->constant::MOBILE), $this->constant::COOK_GUARD)) return $this->errorResponse(false, "", $this->constant::OTP_ALREADY_SENT, $this->http::UNPROCESS_ENTITY_CODE);

        // send otp common function
        $this->sendOtp($request->get($this->constant::MOBILE), $this->constant::COOK_GUARD);
        return $this->successResponse(true, "", $this->constant::OTP_SENT_SUCCESS, 200);
    }
    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function cookVerifyOTP(Request $request)
    {
        # code...
        $verified = $this->verifyOtp($request->get($this->constant::MOBILE), $request->get($this->constant::OTP), $this->constant::COOK_GUARD);
        if ($verified) {
            $crendentials = array_merge($request->only([$this->constant::MOBILE]), array($this->constant::PASSWORD => env($this->constant::COOK_PASSWORD)));
            $token = auth()->guard($this->constant::COOK_GUARD)->attempt($crendentials);

            // Token Generation Failed it will #returns
            if (!$token) return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);

            // Logged User Details Framing
            $user = Cook::role($this->constant::COOK_ROLE)->with('roles')->where('mobile', $request->get($this->constant::MOBILE))->first();
            if ($user) return $this->tokenResponse(true, $user, $token, $this->constant::LOGIN_SUCCESS, 200);
            else {
                auth()->logout();
                return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);
            }
        }
        return $this->errorResponse(false, "", $this->constant::OTP_EXPIRED, $this->http::UNPROCESS_ENTITY_CODE);
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
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        $user = Customers::where('mobile', $request->mobile)->update(array_merge($request->only(['name', 'dob', 'email']), [
            'referral_code' => $this->generateRandomString(),
            'signup_with'   => $request->get('referral')
        ]));

        // Referral Points add #section
        if ($request->get('referral') != '') {
            $refer = Customers::where('referral_code', $request->get('referral'))->first();
            if ($refer != null) {
                $refer->points += 20;
                $refer->save();
            }
        }
        return $this->successResponse(true, "", $this->constant::UPDATED_SUCCESS, 200);
    }

    /**
     * @param $request
     * validate mobile number and generate otp
     */
    public function customerGetOTP(Request $request)
    {
        $validator = Validator::make($request->all(), $this->customerOtpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        $user = Customers::where('mobile', $request->mobile)->first();
        //Modules #id
        $module = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        if ($user) {
            if ($this->existingOtp($request->get($this->constant::MOBILE), $this->constant::CUSTOMER_GUARD)) return $this->errorResponse(false, "", $this->constant::OTP_ALREADY_SENT, $this->http::UNPROCESS_ENTITY_CODE);
        } else {
            $user = Customers::create(['mobile' => $request->mobile, $this->constant::PASSWORD => env($this->constant::CUSTOMER_PASSWORD), 'status' => $module]);
            // assign the role to the created user #roles
            $user->assignRole($this->constant::CUSTOMER_ROLE);
        }
        // send otp common function
        $this->sendOtp($request->get($this->constant::MOBILE), $this->constant::CUSTOMER_GUARD);
        return $this->successResponse(true, "", $this->constant::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function customerVerifyOTP(Request $request)
    {
        # code...
        $verified = $this->verifyOtp($request->get($this->constant::MOBILE), $request->get($this->constant::OTP), $this->constant::CUSTOMER_GUARD);
        if ($verified) {
            $crendentials = array_merge($request->only([$this->constant::MOBILE]), array($this->constant::PASSWORD => env($this->constant::CUSTOMER_PASSWORD)));
            $token = auth()->guard($this->constant::CUSTOMER_GUARD)->attempt($crendentials);

            // Token Generation Failed it will #returns
            if (!$token) return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);

            // Logged User Details Framing
            $user = Customers::role($this->constant::CUSTOMER_ROLE)->with(['roles','address'])->where('mobile', $request->get($this->constant::MOBILE))->first();
            if ($user) return $this->tokenResponse(true, $user, $token, $this->constant::LOGIN_SUCCESS, 200);
            else {
                auth()->logout();
                return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);
            }
        }
        return $this->errorResponse(false, "", $this->constant::OTP_EXPIRED, $this->http::UNPROCESS_ENTITY_CODE);
    }

    ###### Customer Authentication Section of Code Started
    /**
     * @param $request
     * create riders
     */
    public function riderSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->riderSignupValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        //Modules #id
        $module = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        // Create Cook User
        $user = Riders::create(array_merge($request->only([$this->constant::NAME, $this->constant::MOBILE]), array($this->constant::PASSWORD => env($this->constant::RIDER_PASSWORD), $this->constant::STATUS => $module)));

        // assign the role to the created user #roles
        $user->assignRole($this->constant::RIDER_ROLE);

        // send otp common function
        $this->sendOtp($request->get($this->constant::MOBILE), $this->constant::RIDER_GUARD);
        return $this->successResponse(true, "", $this->constant::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * validate mobile number and generate otp
     */
    public function riderGetOTP(Request $request)
    {
        $validator = Validator::make($request->all(), $this->riderOtpValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        if ($this->existingOtp($request->get($this->constant::MOBILE), $this->constant::RIDER_GUARD)) return $this->errorResponse(false, "", $this->constant::OTP_ALREADY_SENT, $this->http::UNPROCESS_ENTITY_CODE);

        // send otp common function
        $this->sendOtp($request->get($this->constant::MOBILE), $this->constant::RIDER_GUARD);
        return $this->successResponse(true, "", $this->constant::OTP_SENT_SUCCESS, 200);
    }

    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function riderVerifyOTP(Request $request)
    {
        # code...
        $verified = $this->verifyOtp($request->get($this->constant::MOBILE), $request->get($this->constant::OTP), $this->constant::RIDER_GUARD);
        if ($verified) {
            $crendentials = array_merge($request->only([$this->constant::MOBILE]), array($this->constant::PASSWORD => env($this->constant::RIDER_PASSWORD)));
            $token = auth()->guard($this->constant::RIDER_GUARD)->attempt($crendentials);

            // Token Generation Failed it will #returns
            if (!$token) return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);

            // Logged User Details Framing
            $user = Riders::role($this->constant::RIDER_ROLE)->with('roles')->where('mobile', $request->get($this->constant::MOBILE))->first();
            if ($user) return $this->tokenResponse(true, $user, $token, $this->constant::LOGIN_SUCCESS, 200);
            else {
                auth()->logout();
                return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);
            }
        }
        return $this->errorResponse(false, "", $this->constant::OTP_EXPIRED, $this->http::UNPROCESS_ENTITY_CODE);
    }

    /**
     * @param $request
     * create vendor
     */
    public function vendorSignup(Request $request)
    {
        $validator = Validator::make($request->all(), $this->createVendorValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        DB::transaction(function () use ($request) {
            $vendors = $request->only(['name', 'email', 'mobile', 'gst_no', 'latitude', 'longitude']);
            $address = $request->only(['door_no', 'lanmark', 'address_line', 'latitude', 'longitude', 'pincode', 'place_id']);
            $bank = $request->only(['bank_name', 'account_number', 'account_type', 'ifsc_code', 'holder_name']);

            $bankDetail = Bank::create(array_merge($bank, array('guard' => "cook")));
            $addressDetail = Address::create(array_merge($address, array('guard' => "cook")));
            $vendor = Vendor::create(array_merge($vendors, array('bank_id' => $bankDetail->id, 'address_id' => $addressDetail->id, 'created_by' => auth($this->constant::ADMIN_GUARD)->user()->id)));
            $bankDetail->user_id = $vendor->id;
            $addressDetail->user_id = $vendor->id;
            $bankDetail->save();
            $addressDetail->save();
        });

        return $this->successResponse(true, "", $this->constant::CREATED_SUCCESS, 201);
    }

    /**
     * @param $request
     * create riders
     */
    public function staffSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->staffSignupValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        //Modules #id
        $module = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        // Create Cook User
        $user = Staff::create(array_merge($request->only(['name', 'mobile', 'password', 'email', 'vendor_id']), array($this->constant::STATUS => $module)));

        // assign the role to the created user #roles
        $user->assignRole($request->role);

        return $this->successResponse(true, "", $this->constant::CREATED_SUCCESS, 201);
    }

    /**
     * @param $request
     * verify otp and authorize the user
     */
    public function staffLogin(Request $request)
    {
        $crendentials = $request->only('email', 'password');
        $token = auth()->guard($this->constant::COOK_GUARD)->attempt($crendentials);

        // Token Generation Failed it will #returns
        if ($token == null) return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);

        // Logged User Details Framing
        $user = Staff::with('roles')->where('email', $request->get('email'))->first();
        if ($user) return $this->tokenResponse(true, $user, $token, $this->constant::LOGIN_SUCCESS, 200);
        else {
            auth()->logout();
            return $this->errorResponse(false, "", $this->constant::UNAUTHORIZED, $this->http::UNPROCESS_ENTITY_CODE);
        }
    }

    #### Logout
    public function logout()
    {
        auth()->logout();
        return $this->successResponse(true, "", $this->constant::LOGOUT_SUCCESS);
    }

    #### GET OTP
    public function getOtp(Request $request)
    {
        $otp = VerificationCode::where([['mobile', $request->mobile], ['guard',  $request->guard]])->latest()->pluck('otp')->first();
        return $this->successResponse(true, $otp, $this->constant::GET_SUCCESS);
    }
}
