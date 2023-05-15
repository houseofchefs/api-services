<?php

namespace App\Traits;

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
            'mobile'    => 'required|min:8|max:16|unique:users',
            "name"      => 'required|max:30',
            "password"  => 'required',
            "role"      => 'required'
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
            'mobile'    => 'required|min:8|max:16',
            "name"      => 'required|max:30',
            "dob"       => "required|date",
            "email"     => "required|email",
        ];
    }

    protected function categoryValidator(): array
    {
        return [
            'name'          => 'required|max:30',
            'image'         => 'required',
            'slot'          => 'required',
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
            'name'          => 'required|max:30',
            'slot'          => 'required',
            'status'        => 'required'
        ];
    }

    /**
     * @menu create body validation
     */
    protected function menuValidator(): array
    {
        return [
            'name'          => 'required|max:30',
            'type'          => 'required',
            'vendor_id'     => 'required',
            'category_id'   => 'required',
            'price'         => 'required',
            'image'         => 'required',
            'isDaily'       => 'required',
            'isPreOrder'    => 'required',
            'description'   => 'required',
            'min_quantity'  => 'required'
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
            "price"         => 'required',
            "product_id"    => 'required',
            "address_id"    => 'required_unless:latitude,""',
            "longtitude"    => 'required_without:address_id',
            "latitude"      => 'required_without:address_id',
            "payment_method"=> 'required'
        ];
    }

    protected function addressValidator(): array
    {
        return [
            "user_id"       => 'required',
            "door_no"       => "required",
            "address_line"  => "required",
            "address_type"  => "required",
            "place_id"      => "required",
            "pincode"       => "required",
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
            "name"          => 'required',
            "description"   => 'required',
            "image"         => 'required',
            "type"          => 'required'
        ];
    }

    protected function discountUpdateValidator(): array
    {
        return [
            "name"          => 'required',
            "description"   => 'required',
            "image"         => 'required',
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
            'cook_id'   => 'required'
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
            'name'              => 'required|max:30',
            'mobile'            => 'required|unique:vendors,mobile',
            'email'             => 'required|unique:vendors,email',
            'door_no'           => 'required',
            'account_number'    => 'required|max:16',
            'account_type'      => 'required',
            'bank_name'         => 'required',
            'holder_name'       => 'required',
            'ifsc_code'         => 'required:max:14',
            'address_line'      => 'required'
        ];
    }

    /**
     * @rider signup validator
     */
    protected function staffSignupValidator(): array
    {
        return [
            'mobile'    => 'required|min:8|max:16|unique:staff',
            "name"      => 'required|max:30',
            "email"     => 'required|email|unique:staff',
            "password"  => 'required',
            "vendor_id" => 'required'
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
            "name"          => 'required',
            "description"   => 'required',
            "vendor_id"     => "required",
            "image"         => 'required',
            "units"         => 'required',
            "price"         => 'required',
        ];
    }

    /**
     * @update products
     */
    protected function updateProductValidation(): array
    {
        return [
            "name"          => 'required',
            "description"   => 'required',
            "image"         => 'required',
            "units"         => 'required',
            "price"         => 'required',
            "status"        => 'required'
        ];
    }

    /**
     * @pre_booking
     */
    protected function createPreBookingValidation(): array
    {
        return [
            "booking_date"  => 'required',
            "address_id"    => 'required',
            "price"         => 'required',
            "items"         => 'required',
            "latitude"      => 'required',
            "longitude"     => 'required'
        ];
    }
}
