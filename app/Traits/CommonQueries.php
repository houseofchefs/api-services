<?php

namespace App\Traits;

use App\Models\Modules;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Storage;

trait CommonQueries
{
    /**
     * @param #code
     * @return #id
     */
    protected function getModuleIdBasedOnCode(String $code)
    {
        return  Modules::where([['module_code', $code], ['status', 1]])->first()->id;
    }

    /**
     * @param #code
     * @return #module
     */
    protected function getModuleBasedOnCode(String $code)
    {
        return  Modules::where([['module_code', $code], ['status', 1]])->first();
    }

    /**
     * @param $mobile
     * @param $guard
     */
    protected function sendOtp($mobile, $guard)
    {
        // $otp = random_int(100000, 999999);
        $otp = 123456;
        $code = [
            "mobile"    => $mobile,
            "otp"       => $otp,
            "guard"     => $guard,
            "expired_at" => Carbon::now()->addMinutes(5)
        ];
        VerificationCode::create($code);
        $service = new TwilioService();
        $code = $service->sendOTP($mobile, $otp);
        return true;
    }

    /**
     * @param $mobile
     * @param $otp
     * @param $guard
     */
    protected function verifyOtp($mobile, $otp, $guard)
    {
        return VerificationCode::where([['mobile', $mobile], ['otp', $otp], ['guard', $guard], ['expired_at', '>', Carbon::now()]])->exists();
    }

    /**
     * @param $mobile
     * @param $guard
     */
    protected function existingOtp($mobile, $guard)
    {
        return VerificationCode::where([['mobile', $mobile], ['guard', $guard], ['expired_at', '>', Carbon::now()]])->exists();
    }

    /**
     * @Referral Code Generate
     */
    protected function generateRandomString($length = 6)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function categoriesCommonQuery()
    {
        return DB::table('categories')
            ->join('users', 'categories.created_by', '=', 'users.id')
            ->join('modules', 'categories.status', '=', 'modules.id')
            ->leftJoin('vendors', 'categories.vendor_id', '=', 'vendors.id')
            ->select('categories.name', 'users.name as created', 'modules.module_name as status', 'categories.id', 'categories.image', 'vendors.name as vendor_name', 'categories.vendor_id as vendor_id')
            ->addSelect(DB::raw('(SELECT GROUP_CONCAT(modules.module_name SEPARATOR ", ") FROM categories_has_slot
                                JOIN modules ON categories_has_slot.slot_id = modules.id
                                WHERE categories_has_slot.category_id = categories.id) as slots'));
    }

    protected function subCategoriesCommonQuery()
    {
        return DB::table('sub_category')
            ->join('users', 'sub_category.created_by', '=', 'users.id')
            ->join('modules', 'sub_category.status', '=', 'modules.id')
            ->join('categories', 'sub_category.category_id', '=', 'categories.id')
            ->select('sub_category.id', 'sub_category.name', 'categories.name as category', 'users.name as created', 'modules.module_name as status');
    }

    protected function menuListQuery($id = null)
    {
        $customerId = auth('customer')->user()->id;
        return DB::table('menus')
            ->join('modules as m1', 'menus.status', '=', 'm1.id')
            ->join('modules as m2', 'menus.type', '=', 'm2.id')
            ->leftJoin('categories', 'menus.category_id', '=', 'categories.id')
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'menus.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'menu');
            })
            ->where('menus.menu_type', 'menu')
            ->selectRaw(
                'menus.id as id,
                menus.rating as rating,
                menus.name as name,
                menus.image as image,
                menus.description as description,
                menus.price,
                m1.module_name as status,
                m2.module_name as food_type,
                menus.isApproved as approved,
                categories.name as category,
                categories.id as category_id,
                IF(wishlists.id IS NULL, false, true) AS wishlist'
            )
            ->when($id != null, function ($subQ, $id) {
                $subQ->where('menus.created_by', $id);
            });
    }

    protected function slotBasedMenus($lat, $long, $rad, $slot, $status)
    {
        $latitude = $lat;
        $longitude = $long;
        $radius = $rad; // in kilometers
        $slotId = $slot;
        $customerId = auth('customer')->user()->id;

        return DB::table('vendors')
            ->selectRaw('menus.id as id, vendors.open_time as open_time,vendors.close_time as close_time,vendors.order_accept_time as order_accept_time, menus.price as price,vendors.id as vendor_id,menus.description as description,menus.name as name,menus.isDaily as isDaily,vendors.name as vendorName,categories.name as categoryName,menus.rating as rating, menus.ucount as ratingCount,menus.image as image,vendors.latitude as latitude,vendors.longitude as longitude,IF(wishlists.id IS NULL, false, true) AS wishlist,modules.module_name as type, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->join('categories', 'categories.id', '=', 'menus.category_id')
            ->join('categories_has_slot', 'categories_has_slot.category_id', '=', 'menus.category_id')
            ->join('modules', 'modules.id', '=', 'menus.type')
            ->where('menus.isApproved', 1)
            ->where('menus.menu_type', 'menu')
            ->when(!$slotId == 0, function ($q) use ($slotId) {
                return $q->where('categories_has_slot.slot_id', '=', $slotId);
            })
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'menus.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'menu');
            })
            ->where('menus.status', $status)
            ->whereRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) <= ?', [$latitude, $longitude, $latitude, $radius])
            ->distinct();
    }

    function checkWithinRadius($latitude, $longitude, $centerLat, $centerLong, $radius)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        // Convert all latitudes and longitudes from degrees to radians
        $lat1 = deg2rad($latitude);
        $lon1 = deg2rad($longitude);
        $lat2 = deg2rad($centerLat);
        $lon2 = deg2rad($centerLong);

        // Calculate the distance between the two points using the haversine formula
        $deltaLon = $lon2 - $lon1;
        $deltaLat = $lat2 - $lat1;
        $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Calculate the distance in kilometers
        $distance = $earthRadius * $c;

        // Check if the distance is within the radius
        return $distance <= $radius;
    }

    public function uploadImage($image, $path, $imageName)
    {
        $path = $image->storeAs($path, $imageName, 's3');
        return Storage::disk('s3')->url($path);
    }
}
