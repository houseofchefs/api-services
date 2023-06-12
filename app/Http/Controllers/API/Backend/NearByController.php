<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\Categories;
use App\Models\Menu;
use App\Models\MenuAvailableDay;
use App\Models\Vendor;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NearByController extends Controller
{
    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    ## Service Code Started

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

        // Do something with the response data
    }

    /**
     * Pre-Booking List and Distance Calculate
     */
    public function preBookingList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $origin = $latitude . ',' . $longitude;
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description;
        $data =  $this->slotBasedMenus($latitude, $longitude, $radius, 0, $status)
            ->when($request->slot_id != 0, function ($q) use ($request) {
                $q->where('categories_has_slot.slot_id', $request->slot_id);
            })
            ->where('menus.isPreOrder', 1)->paginate(10);
        foreach ($data as $subData) {
            $destination = $subData->latitude . ',' . $subData->longitude;
            $google = $this->getDistance($origin, $destination);
            $subData->distance = $google['distance']['text'];
            $subData->time = $google['duration']['text'];
            if (!$subData->isDaily) {
                $subData->day = DB::table('menu_available_days')
                    ->where('menu_id', $subData->id)
                    ->pluck('day')
                    ->toArray();
            }
        }
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * Today Offer Menus by Vendor or Category
     */
    public function todayOfferMenus(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $origin = $latitude . ',' . $longitude;
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $approved = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);
        $customerId = auth('customer')->user()->id;

        $today = Carbon::now();
        $vendorIds = DB::table('discounts')
            ->where('status', $status)
            ->where('expire_at', '>=', $today)
            ->pluck('vendor_id');

        $data = DB::table('vendors')
            ->select('vendors.id', 'vendors.name', 'vendors.latitude', 'vendors.longitude', 'menus.name as menu_name', 'menus.image as menu_image', 'menus.rating as rating', 'menus.ucount as count', 'menus.id as menu_id', DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist'))
            ->whereIn('vendors.id', $vendorIds)
            ->where('menus.status', $approved)
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?)) + sin( radians(?) ) * sin( radians( latitude ) ) )) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'menus.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'menu');
            })
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'ASC')
            ->paginate(10);

        foreach ($data as $subData) {
            $destination = $subData->latitude . ',' . $subData->longitude;
            $google = $this->getDistance($origin, $destination);
            $subData->distance = $google['distance']['text'];
            $subData->time = $google['duration']['text'];
        }

        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * Slot Based menu list
     */
    public function slotBasedMenuItemsList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $origin = $latitude . ',' . $longitude;
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);

        $data =  $this->slotBasedMenus($latitude, $longitude, $radius, $request->slot, $status)->paginate(10);
        foreach ($data as $subData) {
            $destination = $subData->latitude . ',' . $subData->longitude;
            $google = $this->getDistance($origin, $destination);
            $subData->distance = $google['distance']['text'];
            $subData->time = $google['duration']['text'];
        }
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * slot and category based menu list
     */
    public function slotAndCategoryMenuItemsList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $origin = $latitude . ',' . $longitude;
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);

        $data =  $this->slotBasedMenus($latitude, $longitude, $radius, $request->slot, $status)->when($request->get('search') != null, function ($subQ) use ($request) {
            $subQ->where('menus.name', $request->get('search'));
        })->when($request->get('categoryId') != 0, function ($q) use ($request) {
            $q->where('menus.category_id', $request->categoryId);
        })->paginate(10);
        foreach ($data as $subData) {
            $destination = $subData->latitude . ',' . $subData->longitude;
            $google = $this->getDistance($origin, $destination);
            $subData->distance = $google['distance']['text'];
            $subData->time = $google['duration']['text'];
        }
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * Vendor List
     */
    public function vendorList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $origin = $latitude . ',' . $longitude;
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $customerId = auth('customer')->user()->id;

        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = DB::table('vendors')
            ->selectRaw('vendors.id as id,vendors.name as name, vendors.latitude as latitude, vendors.longitude as longitude, vendors.rating as rating, vendors.ucount as count,IF(wishlists.id IS NULL, false, true) AS wishlist, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->whereRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) <= ?', [$latitude, $longitude, $latitude, $radius])
            ->where('status', $status)
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'vendors.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'vendor');
            })
            ->paginate(10);
        foreach ($data as $subData) {
            $destination = $subData->latitude . ',' . $subData->longitude;
            $google = $this->getDistance($origin, $destination);
            $subData->distance = $google['distance']['text'];
            $subData->time = $google['duration']['text'];
        }
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * Product List
     */
    public function productList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $origin = $latitude . ',' . $longitude;
        $customerId = auth('customer')->user()->id;
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers

        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = DB::table('menus')
            ->selectRaw('vendors.id as vendor_id,menus.description as description,menus.name as name,menus.id as id,menus.price as price,menus.image as image,vendors.name as vendor_name,menus.units as units,vendors.latitude as latitude, vendors.longitude as longitude, vendors.rating as rating, vendors.ucount as count,IF(wishlists.id IS NULL, false, true) AS wishlist, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->join('vendors', 'menus.vendor_id', '=', 'vendors.id')
            ->where('menus.status', $status)
            ->where('menus.menu_type', 'product')
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'vendors.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'product');
            })
            ->whereRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) <= ?', [$latitude, $longitude, $latitude, $radius])
            ->distinct()->paginate(10);
        foreach ($data as $subData) {
            $destination = $subData->latitude . ',' . $subData->longitude;
            $google = $this->getDistance($origin, $destination);
            $subData->distance = $google['distance']['text'];
            $subData->time = $google['duration']['text'];
        }
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * Global Search
     */
    public function globalSearch(Request $request)
    {
        $vendors = Vendor::get();
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $withinVendor = [];

        foreach ($vendors as $vendor) {
            if ($this->checkWithinRadius($request->get('latitude'), $request->get('longitude'), $vendor->latitude, $vendor->longitude, $radius)) {
                array_push($withinVendor, $vendor->id);
            }
        }

        if ($request->get('search') != null && $request->get('search') != "") {
            // Category
            $category = Categories::where('name', 'like', '%' . $request->get('search') . '%')->whereIn('vendor_id', array_merge($withinVendor, [0]))->get();
            // Menu
            $menu = Menu::where('name', 'like', '%' . $request->get('search') . '%')->whereIn('vendor_id', $withinVendor)->get();
            // Vendor
            $vendorData = Vendor::where('name', 'like', '%' . $request->get('search') . '%')->whereIn('id', $withinVendor)->get();
        }

        $data = ["category" => $category, "menu" => $menu, "vendor" => $vendorData];
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * Vendor List Based on Lat,Lng
     */
    public function vendorListDropdown(Request $request)
    {
        $vendors = Vendor::get();
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $withinVendor = [];

        foreach ($vendors as $vendor) {
            if ($this->checkWithinRadius($request->get('latitude'), $request->get('longitude'), $vendor->latitude, $vendor->longitude, $radius)) {
                array_push($withinVendor, $vendor->id);
            }
        }

        $vendor = Vendor::where('name', 'like', '%' . $request->get('search') . '%')->whereIn('id', $withinVendor)->get();
        return $this->successResponse(true, $vendor, Constants::GET_SUCCESS);
    }

    /**
     * Category Based menu
     */
    public function categoryBasedMenu(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);

        $vendors = Vendor::get();
        $withinVendor = [];

        foreach ($vendors as $vendor) {
            if ($this->calculateDistance($request->get('latitude'), $request->get('longitude'), $vendor->latitude, $vendor->longitude, $radius)) {
                array_push($withinVendor, $vendor->id);
            }
        }

        $data = DB::table('vendors')
            ->selectRaw('menus.id as id, vendors.open_time as open_time,vendors.close_time as close_time,vendors.order_accept_time as order_accept_time, menus.price as price,vendors.id as vendor_id,menus.description as description,menus.name as name,menus.isDaily as isDaily,vendors.name as vendorName,categories.name as categoryName,menus.rating as rating, menus.ucount as ratingCount,menus.image as image,vendors.latitude as latitude,vendors.longitude as longitude,modules.module_name as type, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->join('categories', 'categories.id', '=', 'menus.category_id')
            ->join('categories_has_slot', 'categories_has_slot.category_id', '=', 'menus.category_id')
            ->join('modules', 'modules.id', '=', 'menus.type')
            ->where('menus.isApproved', 1)
            ->where('menus.menu_type', 'menu')
            ->where('menus.status', $status)
            ->whereIn('menus.vendor_id', $withinVendor)
            ->when($request->get('search') != null, function ($subQ) use ($request) {
                $subQ->where('menus.name', $request->get('search'));
            })
            ->distinct()->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function vendorAndCategoryBasedMenu(Request $request, $id)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $this->getModuleBasedOnCode($this->constant::RADIUS)->description; // in kilometers
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);

        $vendors = Vendor::get();
        $withinVendor = [];

        foreach ($vendors as $vendor) {
            if ($this->calculateDistance($request->get('latitude'), $request->get('longitude'), $vendor->latitude, $vendor->longitude, $radius)) {
                array_push($withinVendor, $vendor->id);
            }
        }

        $data = DB::table('vendors')
            ->selectRaw('menus.id as id, vendors.open_time as open_time,vendors.close_time as close_time,vendors.order_accept_time as order_accept_time, menus.price as price,vendors.id as vendor_id,menus.description as description,menus.name as name,menus.isDaily as isDaily,vendors.name as vendorName,categories.name as categoryName,menus.rating as rating, menus.ucount as ratingCount,menus.image as image,vendors.latitude as latitude,vendors.longitude as longitude,modules.module_name as type, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->join('categories', 'categories.id', '=', 'menus.category_id')
            ->join('categories_has_slot', 'categories_has_slot.category_id', '=', 'menus.category_id')
            ->join('modules', 'modules.id', '=', 'menus.type')
            ->where('menus.isApproved', 1)
            ->where('menus.menu_type', 'menu')
            ->where('menus.status', $status)
            ->where('vendors.id', $id)
            ->whereIn('menus.vendor_id', $withinVendor)
            ->when($request->get('search') != null, function ($subQ) use ($request) {
                $subQ->where('menus.name', $request->get('search'));
            })
            ->distinct()->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    function calculateDistance($lat1, $lng1, $lat2, $lng2, $distanceThreshold)
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
}
