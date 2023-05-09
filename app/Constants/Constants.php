<?php

namespace App\Constants;

final class Constants
{
    ## Role
    const SUPER_ADMIN_ROLE = 'super-admin';
    const CUSTOMER_ROLE = 'customer';
    const RIDER_ROLE = 'rider';
    const COOK_ROLE = 'cook';
    const ADMIN = "admin";

    ## Guard Name
    const ADMIN_GUARD = "admin";
    const COOK_GUARD = "cook";
    const CUSTOMER_GUARD = "customer";
    const RIDER_GUARD = "rider";

    ## ENV Password
    const ADMIN_PASSWORD = 'ADMIN_PASSWORD';
    const COOK_PASSWORD = "COOK_PASSWORD";
    const RIDER_PASSWORD = "RIDER_PASSWORD";
    const CUSTOMER_PASSWORD = "CUSTOMER_PASSWORD";

    ## Request Body
    const MOBILE = 'mobile';
    const NAME = 'name';
    const OTP = 'otp';
    const EMAIL = 'email';
    const PASSWORD = "password";
    const STATUS = "status";
    const VENDORID = "vendor_id";

    ## Response Message
    const ERROR = "Error";
    const GET_SUCCESS = "Success";
    const INTERNAL_SERVER_ERROR = "Internal Server Error";
    const CREATED_SUCCESS = "Created Successfully!";
    const UPDATED_SUCCESS = "Updated Successfully!";
    const BAD_REQUEST = "Bad Request!";
    const UNAUTHORIZED = "Unauthorized";
    const UNPROCESS_ENTITY = "Unprocessable Entity";
    const MENU_CREATED = "Menu Created Successfully!";
    const MENU_UPDATED = "Menu Updated Successfully!";
    const CATEGORY_CREATED = "Category Created Successfully!";
    const CATEGORY_UPDATED = "Category Updated Successfully!";
    const ORDER_CREATED = "Order Placed Successfully!";
    const ADDRESS_CREATED = "Address Created Successfully!";
    const ADDRESS_UPDATED = "Address Updated Successfully!";
    const BANK_CREATED = "Bank Created Successfully!";
    const BANK_UPDATED = "Bank Updated Successfully!";
    const DISCOUNT_CREATED = "Discount Created Successfully!";
    const DISCOUNT_UPDATED = "Discount Updated Successfully!";
    const SUB_CATEGORY_CREATED = "Sub-Category Created Successfully!";
    const SUB_CATEGORY_UPDATED = "Sub-Category Updated Successfully!";

    ## Validation Message
    const OTP_ALREADY_SENT = 'OTP is Already sent';
    const OTP_SENT_SUCCESS = 'OTP Sent Successfully!';
    const LOGIN_SUCCESS = "Logged-in Successfully!";
    const LOGOUT_SUCCESS = "Logout Successfully!";
    const OTP_EXPIRED = 'OTP is Expired';

    #API's
    const PAGINATE = "paginate";
    const DROPDOWN = "dropdown";

    ## Module Code
    # Status
    const ACTIVE = "CS01";
    const INACTIVE = "CS02";
    # Menu
    const MENU_HOLD = "MS01";
    const MENU_APPROVED = "MS02";
    const MENU_DELETE = "MS03";
    const MENU_OFF = "MS04";
    # Order
    const ORDER_SUCCESS = "OS01";
    const ORDER_PROGRESS = "OS02";
    const ORDER_CANCELED = "OS03";
    const ORDER_DELIVERED = "OS04";
    # Payment #staus
    const PAYMENT_INITIATE = "PS01";
    const PAYMENT_FAILED = "PS02";
    const PAYMENT_SUCCESS = "PS03";
    # Payment #method
    const PAYMENT_METHOD_UPI = "PM01";
    const PAYMENT_METHOD_CC = "PM02";
    const PAYMENT_METHOD_DC = "PM03";
    const PAYMENT_METHOD_CASH = "PM04";
    # Radius
    const RADIUS = "MT10";
}
