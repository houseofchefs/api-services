<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\MenuAvailableDay;
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

        $data =  $this->slotBasedMenus($latitude, $longitude, $radius, $request->slot, $status)->where('menus.category_id', $request->categoryId)->paginate(10);
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
        $data = DB::table('products')
            ->selectRaw('vendors.id as vendor_id,products.description as description,products.name as name,products.id as id,products.price as price,products.image as image,vendors.name as vendor_name,products.units as units,vendors.latitude as latitude, vendors.longitude as longitude, vendors.rating as rating, vendors.ucount as count,IF(wishlists.id IS NULL, false, true) AS wishlist, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->where('products.status', $status)
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
}
