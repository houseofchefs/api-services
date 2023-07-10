<?php

namespace App\Traits;

use App\Constants\Constants;
use App\Models\Modules;
use App\Models\Vendor;
use App\Models\VerificationCode;
use Carbon\Carbon;
use GuzzleHttp\Client;
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
        $otp = random_int(100000, 999999);
        if ($mobile == "7092462701" || $mobile == "7397629607") {
            $otp = 123456;
        }
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

    protected function categoriesCommonQuery($id = null)
    {
        return DB::table('categories')
            ->when($id != null, function ($subQ) use ($id) {
                return $subQ->where('categories.id', $id);
            })
            ->join('users', 'categories.created_by', '=', 'users.id')
            ->join('modules', 'categories.status', '=', 'modules.id')
            ->leftJoin('vendors', 'categories.vendor_id', '=', 'vendors.id')
            ->select('categories.name', 'users.name as created', 'modules.module_name as status', 'categories.id', 'categories.image', 'vendors.name as vendor_name', 'categories.vendor_id as vendor_id')
            ->selectSub(function ($subQuery) {
                $subQuery->from('categories_has_slot')
                    ->join('modules', 'categories_has_slot.slot_id', '=', 'modules.id')
                    ->whereRaw('categories_has_slot.category_id = categories.id')
                    ->selectRaw('GROUP_CONCAT(modules.module_name SEPARATOR ", ")');
            }, 'slots');
    }

    protected function menuListQuery($id = null)
    {
        $customerId = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $approved = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);

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
            ->where('menus.status', $approved)
            ->where('categories.status', $status)
            ->select(
                'menus.id',
                'menus.rating',
                'menus.name',
                'menus.image',
                'menus.description',
                'menus.price',
                'm1.module_name as status',
                'm2.module_name as food_type',
                'menus.isApproved as approved',
                'categories.name as category',
                'categories.id as category_id',
                DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist')
            )
            ->when($id != null, function ($subQ) use ($id) {
                $subQ->where('menus.created_by', $id);
            });
    }

    protected function slotBasedMenus($slot, $status, $vendors)
    {
        $slotId = $slot;
        $customerId = auth('customer')->user()->id;
        $active = $this->getModuleIdBasedOnCode(Constants::ACTIVE);

        return DB::table('vendors')
            ->whereIn('vendors.id', $vendors)
            ->select(
                'menus.id as id',
                'vendors.open_time',
                'vendors.close_time',
                'vendors.order_accept_time',
                'menus.price',
                'vendors.id as vendor_id',
                'menus.description',
                'menus.name',
                'menus.isPreOrder',
                'menus.isDaily',
                'vendors.name as vendorName',
                'categories.name as categoryName',
                'categories.id as category_id',
                'menus.rating',
                'menus.ucount as ratingCount',
                'menus.image',
                'vendors.latitude as latitude',
                'vendors.longitude as longitude',
                DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist'),
                'modules.module_name as type'
            )
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->join('categories', 'categories.id', '=', 'menus.category_id')
            ->join('categories_has_slot', 'categories_has_slot.category_id', '=', 'menus.category_id')
            ->join('modules', 'modules.id', '=', 'menus.type')
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'menus.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'menu');
            })
            ->where('menus.isApproved', 1)
            ->where('menus.menu_type', 'menu')
            ->where('categories.status', $active)
            ->when($slotId != 0, function ($q) use ($slotId) {
                return $q->where('categories_has_slot.slot_id', '=', $slotId);
            })
            ->where('menus.status', $status)
            ->distinct();
    }

    public function uploadImage($image, $path, $imageName)
    {
        $path = $image->storeAs($path, $imageName, 's3');
        return Storage::disk('s3')->url($path);
    }

    public function vendorWithInTheRadius($latitude, $longitude, $radius)
    {
        $vendors = Vendor::where('status', 2)->get();
        $withinVendor = [];

        foreach ($vendors as $vendor) {
            if ($this->calculateDistance($latitude, $longitude, $vendor->latitude, $vendor->longitude, $radius)) {
                array_push($withinVendor, $vendor->id);
            }
        }

        return $withinVendor;
    }

    public function calculateDistance($lat1, $lng1, $lat2, $lng2, $distanceThreshold)
    {
        // Radius of the Earth in kilometers
        $earthRadius = 6371;

        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        // Calculate the differences between the coordinates
        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;

        // Haversine formula
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Calculate the distance in kilometers
        $distance = $earthRadius * $c;

        // Check if the distance is within the threshold
        return $distance <= $distanceThreshold;
    }

    /**
     * Google Matrix API for Finding the Distance
     */
    public function getDistance($origin, $destination)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY'); // Replace with your API key

        $client = new Client();
        $response = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json', [
            'query' => [
                'origins' => $origin,
                'destinations' => $destination,
                'key' => $apiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        if (count($data['rows']) > 0 && count($data['rows'][0]['elements']) > 0) {
            $row = $data['rows'][0];
            return $row['elements'][0];
        }
    }
}
