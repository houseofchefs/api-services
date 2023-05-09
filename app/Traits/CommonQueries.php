<?php

namespace App\Traits;

use App\Models\Modules;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\TwilioService;

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
        $otp = random_int(100000, 999999);
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
            ->select('categories.name', 'users.name as created', 'modules.module_name as status');
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
        return DB::table('menu')
            ->join('modules as m1', 'menu.status', '=', 'm1.id')
            ->join('modules as m2', 'menu.type', '=', 'm2.id')
            ->join('cooks', 'menu.created_by', '=', 'cooks.id')
            ->leftJoin('ingredients', 'menu.id', '=', 'ingredients.menu_id')
            ->leftJoin('categories', 'menu.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'menu.sub_category_id', '=', 'sub_categories.id')
            ->select(
                'menu.id as id',
                'menu.name as name',
                'menu.menu_image as image',
                'menu.price',
                'm1.module_name as status',
                'm2.module_name as food_type',
                'cooks.name as created',
                'categories.name as category',
                'sub_categories.name as sub_category'
            )
            ->when($id != null, function ($subQ, $id) {
                $subQ->where('menu.created_by', $id);
            })
            ->groupBy('menu.id')
            ->selectRaw('JSON_ARRAYAGG(JSON_OBJECT("name", ingredients.name,"calories", ingredients.calories,"fat", ingredients.fat,"carbohydrates", ingredients.carbohydrates, "protein", ingredients.protein)) as ingredients_array');
    }

    protected function slotBasedMenus($lat, $long, $rad, $slot, $status)
    {
        $latitude = $lat;
        $longitude = $long;
        $radius = $rad; // in kilometers
        $slotId = $slot;
        $customerId = auth('customer')->user()->id;

        return DB::table('vendors')
            ->selectRaw('menus.id as id,menus.name as name,menus.isDaily as isDaily,vendors.name as vendorName,categories.name as categoryName,menus.rating as rating, menus.ucount as ratingCount,menus.image as image,vendors.latitude as latitude,vendors.longitude as longitude,IF(wishlists.id IS NULL, false, true) AS wishlist,modules.module_name as type, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->join('categories', 'categories.id', '=', 'menus.category_id')
            ->join('categories_has_slot', 'categories_has_slot.category_id', '=', 'menus.category_id')
            ->join('modules', 'modules.id', '=', 'menus.type')
            ->where('menus.isApproved', 1)
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
}
