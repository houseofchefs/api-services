<?php

namespace App\Traits;

use Illuminate\Validation\Rule;

trait ValidationTraits
{
    /**
     * @admin get-otp validator
     */
    protected function adminOtpValidator(): array
    {
        return [
            'mobile' => 'required|min:8|max:16|exists:users'
        ];
    }

    /**
     * @admin login verify
     */
    protected function adminLoginVerify(): array
    {
        return [
            'email'     => 'required',
            'password'  => 'required'
        ];
    }

    /**
     * @admin signup validator
     */
    protected function adminSignUpValidator(): array
    {
        return [
            'mobile'    => 'required|numeric|min:8|max:16|unique:users,mobile',
            "name"      => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            "password"  => 'required',
            "role"      => 'required',
            "email"     => 'required|email|unique:users,email'
        ];
    }

    protected function updateAdminValidator($id): array
    {
        return [
            'mobile'    => [
                'required',
                'numeric',
                'min:8',
                'max:16', Rule::unique('users')->ignore($id),
            ],
            "name"      => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            "email"     => ['required', 'email', Rule::unique('users')->ignore($id)]
        ];
    }

    /**
     * @cook get-otp validator
     */
    protected function cookOtpValidator(): array
    {
        return [
            'mobile' => 'required|min:8|max:16|exists:cooks'
        ];
    }

    /**
     * @cook signup validator
     */
    protected function cookSignupValidator(): array
    {
        return [
            'mobile'    => 'required|min:8|max:16|unique:cooks',
            "name"      => 'required|max:30'
        ];
    }

    /**
     * @customer get-otp validator
     */
    protected function customerOtpValidator(): array
    {
        return [
            'mobile' => 'required|min:8|max:16'
        ];
    }

    /**
     * @rider signup validator
     */
    protected function riderSignupValidator(): array
    {
        return [
            'mobile'    => 'required|min:8|max:16|unique:riders',
            "name"      => 'required|max:30'
        ];
    }

    protected function riderStoreValidator(): array
    {
        return [
            'name'              => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            'mobile'            => 'required|unique:riders,mobile',
            'email'             => 'required|unique:riders,email',
            'door_no'           => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'account_number'    => 'required|max:16',
            'account_type'      => 'required',
            'bank_name'         => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'holder_name'       => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'ifsc_code'         => 'required|max:14||regex:/^[a-zA-Z0-9]+$/',
            'address_line'      => 'required',
            'insurance_number'  => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'registration_number' => 'required',
            'password'          => 'required'
        ];
    }

    protected function riderUpdateValidator($id): array
    {
        return [
            'name'              => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            'mobile'            => ['required', Rule::unique('riders')->ignore($id)],
            'email'             => ['required', Rule::unique('riders')->ignore($id)],
            'door_no'           => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'account_number'    => 'required|max:16',
            'account_type'      => 'required',
            'bank_name'         => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'holder_name'       => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'ifsc_code'         => 'required|max:14|regex:/^[a-zA-Z0-9]+$/',
            'address_line'      => 'required',
            'insurance_number'  => 'required',
            'registration_number' => 'required',
            'status'            => 'required'
        ];
    }

    /**
     * @rider get-otp validator
     */
    protected function riderOtpValidator(): array
    {
        return [
            'mobile' => 'required|min:8|max:16|exists:riders'
        ];
    }

    /**
     * @customer signup validator
     */
    protected function customerSignupValidator(): array
    {
        return [
            'mobile'    => 'required|min:8|max:16|unique:customers,mobile',
            "name"      => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            "dob"       => "required|date",
            "email"     => "required|email",
        ];
    }

    /**
     * @customer update signup validator
     */
    protected function updateCustomerSignupValidator($id): array
    {
        return [
            'mobile'    => [
                'required',
                'min:8',
                'max:16', Rule::unique('customers')->ignore($id),
            ],
            "name"      => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            "dob"       => "required|date",
            "email"     => "required|email",
        ];
    }

    protected function categoryValidator(): array
    {
        return [
            'name'          => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            'image'         => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'slot'          => 'required',
            'vendor_id'     => 'required'
        ];
    }

    protected function subCategoryValidator(): array
    {
        return [
            'name'          => 'required|max:30',
            'category_id'   => 'required',
        ];
    }

    protected function categoryUpdateValidator(): array
    {
        return [
            'name'          => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            'slot'          => 'required',
            'status'        => 'required',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * @menu create body validation
     */
    protected function menuValidator($request): array
    {
        return [
            'name'          => 'required|max:50|regex:/^[a-zA-Z0-9\s]+$/',
            'type'          => 'required',
            'vendor_id'     => 'required|numeric',
            'category_id'   => 'required|numeric',
            'price'         => 'required|numeric|regex:/^[0-9]+$/',
            'image'         => 'required|image|mimes:jpeg,png,jpg|max:2048', //|max:2048
            'isDaily'       => 'required|numeric',
            'isPreOrder'    => 'required|numeric',
            'description'   => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'min_quantity'  => 'required|numeric',
            'ingredient_id' => 'required|array',
            "days" => [
                Rule::when(function () use ($request) {
                    return $request->input('isPreOrder') == 1 && $request->input('isDaily') == 0;
                }, ['required']),
            ]
        ];
    }

    /**
     * @menu update body validation
     */
    protected function updateMenuValidator($request): array
    {
        return [
            'name'          => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            'type'          => 'required',
            'vendor_id'     => 'required|numeric',
            'category_id'   => 'required|numeric',
            'image'         => 'required',
            'isDaily'       => 'required|numeric',
            'isPreOrder'    => 'required|numeric',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description'   => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'price'         => 'required|numeric',
            'min_quantity'  => 'required|numeric',
            'status'        => 'required',
            "ingredient_id" => 'required|array',
            "days" => [
                Rule::when(function () use ($request) {
                    return $request->input('isPreOrder') == 1 && $request->input('isDaily') == 0;
                }, ['required']),
            ]
        ];
    }

    /**
     * @menu update body validation
     */
    protected function menuUpdateValidator(): array
    {
        return [
            'status'    => 'required'
        ];
    }

    protected function createOrderValidator(): array
    {
        return [
            "price"         => 'required|numeric',
            "customer_id"   => 'required|numeric',
            "vendor_id"     => 'required|numeric',
            "product_id"    => 'required|numeric',
            "address_id"    => 'required_unless:latitude,""',
            "longtitude"    => 'required_without:address_id',
            "latitude"      => 'required_without:address_id',
            "cod"           => 'required',
            "expected_delivery" => "required|date",
            "instructions"  => '|regex:/^[a-zA-Z0-9\s]+$/'
        ];
    }

    protected function addressValidator(): array
    {
        return [
            "user_id"       => 'required',
            "door_no"       => "required|regex:/^[a-zA-Z0-9\s]+$/",
            "address_line"  => "required",
            "address_type"  => "required",
            "pincode"       => "required|numeric",
            "latitude"      => "required",
            "longitude"     => "required",
            "guard"         => "required"
        ];
    }

    protected function bankValidator(): array
    {
        return [
            "user_id"       => 'required',
            "guard"         => "required",
            "bank_name"     => 'required',
            "holder_name"   => 'required',
            "account_number" => 'required',
            "ifsc_code"     => 'required'
        ];
    }

    protected function discountValidator(): array
    {
        return [
            "name"          => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            "description"   => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'image'         => 'required|image|mimes:jpeg,png,jpg|max:2048',
            "type"          => 'required',
            "category_id"   => 'required|numeric',
            "vendor_id"     => 'required|numeric',
            'percentage'    => 'required|numeric',
            'expire_at'     => 'required|date'
        ];
    }

    protected function discountUpdateValidator(): array
    {
        return [
            "name"          => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            "description"   => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            "type"          => 'required',
            "status"        => 'required'
        ];
    }

    protected function paymentValidator(): array
    {
        return [
            "order_id"          => 'required',
            'payment_method'    => 'required',
            'amount'            => 'required'
        ];
    }

    protected function cartValidator(): array
    {
        return [
            'menu_id'   => 'required',
            'quantity'  => 'required',
            'vendor_id'   => 'required'
        ];
    }

    protected function cartUpdateValidator(): array
    {
        return [
            'quantity'  => 'required'
        ];
    }

    /**
     * @vendor create validation
     */
    protected function createVendorValidator(): array
    {
        return [
            'name'              => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            'mobile'            => 'required|unique:vendors,mobile|numeric',
            'email'             => 'required|unique:vendors,email|email',
            'image'             => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'door_no'           => 'required|regex:/^[a-zA-Z0-9]+$/',
            'account_number'    => 'required|numeric',
            'account_type'      => 'required',
            'bank_name'         => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'holder_name'       => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'ifsc_code'         => 'required:max:14|regex:/^[a-zA-Z0-9]+$/',
            'address_line'      => 'required',
            "open_time"         => 'required|date_format:H:i:s',
            "close_time"        => 'required|date_format:H:i:s',
            'order_accept_time' => 'required|date_format:H:i:s'
        ];
    }

    /**
     * @vendor update validation
     */
    protected function updateVendorValidator($id): array
    {
        return [
            'name'              => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            'mobile'            => ['required', 'numeric', Rule::unique('vendors')->ignore($id)],
            'email'             => ['required', 'email', Rule::unique('vendors')->ignore($id)],
            'door_no'           => 'required|regex:/^[a-zA-Z0-9]+$/',
            'account_number'    => 'required|numeric',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'account_type'      => 'required',
            'bank_name'         => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'holder_name'       => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'ifsc_code'         => 'required:max:14|regex:/^[a-zA-Z0-9]+$/',
            'address_line'      => 'required',
            "open_time"         => 'required|date_format:H:i:s',
            "close_time"        => 'required|date_format:H:i:s',
            'order_accept_time' => 'required|date_format:H:i:s'
        ];
    }

    /**
     * @rider signup validator
     */
    protected function staffSignupValidator(): array
    {
        return [
            'mobile'    => 'required|min:8|max:16|unique:staff',
            "name"      => 'required|max:30|regex:/^[a-zA-Z0-9\s]+$/',
            "email"     => 'required|email|unique:staff',
            "password"  => 'required',
            "vendor_id" => 'required|numeric',
            "role"      => 'required'
        ];
    }

    /**
     * @wishlist
     */
    protected function wishlistValidation(): array
    {
        return [
            'type'          => 'required',
            'menu_id'       => 'required'
        ];
    }

    /**
     * @create products
     */
    protected function createProductValidation(): array
    {
        return [
            "name"          => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            "description"   => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            "vendor_id"     => "required|numeric",
            'image'         => 'required|image|mimes:jpeg,png,jpg|max:2048',
            "units"         => 'required',
            "price"         => 'required|numeric'
        ];
    }

    /**
     * @update products
     */
    protected function updateProductValidation(): array
    {
        return [
            "name"          => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            "description"   => 'required|regex:/^[a-zA-Z0-9\s]+$/',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            "units"         => 'required',
            "price"         => 'required|numeric',
            "status"        => 'required',
            "price"         => 'required|numeric'
        ];
    }

    /**
     * @pre_booking
     */
    protected function createPreBookingValidation(): array
    {
        return [
            "vendor_id"     => 'required',
            "address_id"    => 'required',
            "price"         => 'required',
            "items"         => 'required',
            "latitude"      => 'required',
            "longitude"     => 'required',
            "slot_id"       => 'required',
            "cod"           => 'required'
        ];
    }

    protected function updatePaymentValidator(): array
    {
        return [
            "payment_method"        => "required",
            "razorpay_signature"    => "required",
            "capture"               => "required",
            "razorpay_order_id"     => "required",
            "razorpay_payment_id"   => "required"

        ];
    }
}
