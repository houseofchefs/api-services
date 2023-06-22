<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Constants\Constants;
use App\Models\Categories;
use App\Models\Menu;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NearByController extends Controller
{

    use ResponseTraits, ValidationTraits, CommonQueries;

    ## Service Code Started

    /**
     * Pre-Booking List and Distance Calculate
     */
    public function preBookingList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $origin = $latitude . ',' . $longitude;
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description;
        $data =  $this->slotBasedMenus($latitude, $longitude, $radius, 0, $status)
            ->when($request->vendorId != 0, function ($q) use ($request) {
                $q->where('menus.vendor_id', $request->vendorId);
            })
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
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Today Offer Menus by Vendor or Category
     */
    public function todayOfferMenus(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $origin = $latitude . ',' . $longitude;
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $approved = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
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

        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Slot Based menu list
     */
    public function slotBasedMenuItemsList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $origin = $latitude . ',' . $longitude;
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $currentTime = Carbon::now()->format('H:i:s');

        $data =  $this->slotBasedMenus($latitude, $longitude, $radius, $request->slot, $status)
            ->where('vendors.order_accept_time', '>', $currentTime)->paginate(10);
        foreach ($data as $subData) {
            $destination = $subData->latitude . ',' . $subData->longitude;
            $google = $this->getDistance($origin, $destination);
            $subData->distance = $google['distance']['text'];
            $subData->time = $google['duration']['text'];
        }
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * slot and category based menu list
     */
    public function slotAndCategoryMenuItemsList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $origin = $latitude . ',' . $longitude;
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $currentTime = Carbon::now()->format('H:i:s');

        $data =  $this->slotBasedMenus($latitude, $longitude, $radius, $request->slot, $status)
            ->where('vendors.order_accept_time', '>', $currentTime)
            ->when($request->get('search') != null, function ($subQ) use ($request) {
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
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Vendor List
     */
    public function vendorList(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $origin = $latitude . ',' . $longitude;
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $customerId = auth('customer')->user()->id;
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);

        $data = DB::table('vendors')
            ->selectRaw('vendors.id as id,vendors.name as name, vendors.image as image, vendors.latitude as latitude, vendors.longitude as longitude, vendors.rating as rating, vendors.ucount as count,IF(wishlists.id IS NULL, false, true) AS wishlist, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->whereRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) <= ?', [$latitude, $longitude, $latitude, $radius])
            ->where('status', $status)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('menus')
                    ->whereRaw('menus.vendor_id = vendors.id');
            })
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
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
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
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers

        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
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
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Global Search
     */
    public function globalSearch(Request $request)
    {
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($request->get('latitude'), $request->get('longitude'), $radius);
        $customerId = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $origin = $request->get('latitude') . ',' . $request->get('longitude');
        $category = [];
        $vendorData = [];
        $menu = [];

        if ($request->get('search') != null && $request->get('search') != "") {
            // Category
            $category = Categories::where('name', 'like', '%' . $request->get('search') . '%')->whereIn('vendor_id', array_merge($withinVendor, [0]))->get();
            // Menu
            $menu = DB::table('menus')
                ->where('menus.name', 'like', '%' . $request->get('search') . '%')
                ->whereIn('menus.vendor_id', $withinVendor)
                ->selectRaw('vendors.id as vendor_id,menus.description as description,menus.name as name,menus.id as id,menus.price as price,menus.image as image,vendors.name as vendor_name,menus.units as units,vendors.latitude as latitude, vendors.longitude as longitude, vendors.rating as rating, vendors.ucount as count,IF(wishlists.id IS NULL, false, true) AS wishlist')
                ->join('vendors', 'menus.vendor_id', '=', 'vendors.id')
                ->where('menus.status', $status)
                ->where('menus.menu_type', 'product')
                ->leftJoin('wishlists', function ($join) use ($customerId) {
                    $join->on('wishlists.menu_id', '=', 'vendors.id')
                        ->where('wishlists.customer_id', '=', $customerId)
                        ->where('wishlists.type', '=', 'product');
                })->get();
            if (count($menu) > 0) {
                foreach ($menu as $subData) {
                    $destination = $subData->latitude . ',' . $subData->longitude;
                    $google = $this->getDistance($origin, $destination);
                    $subData->distance = $google['distance']['text'];
                    $subData->time = $google['duration']['text'];
                }
            }
            // Vendor
            $vendorData = DB::table('vendors')
                ->selectRaw('vendors.id as id,vendors.name as name, vendors.image as image, vendors.latitude as latitude, vendors.longitude as longitude, vendors.rating as rating, vendors.ucount as count,IF(wishlists.id IS NULL, false, true) AS wishlist')
                ->whereIn('vendors.id', $withinVendor)
                ->where('name', 'like', '%' . $request->get('search') . '%')
                ->where('status', $status)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('menus')
                        ->whereRaw('menus.vendor_id = vendors.id');
                })
                ->leftJoin('wishlists', function ($join) use ($customerId) {
                    $join->on('wishlists.menu_id', '=', 'vendors.id')
                        ->where('wishlists.customer_id', '=', $customerId)
                        ->where('wishlists.type', '=', 'vendor');
                })
                ->get();
            if (count($vendorData) > 0) {
                foreach ($vendorData as $subData) {
                    $destination = $subData->latitude . ',' . $subData->longitude;
                    $google = $this->getDistance($origin, $destination);
                    $subData->distance = $google['distance']['text'];
                    $subData->time = $google['duration']['text'];
                }
            }
        }

        $data = ["category" => $category, "menu" => $menu, "vendor" => $vendorData];
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Vendor List Based on Lat,Lng
     */
    public function vendorListDropdown(Request $request)
    {
        $vendors = Vendor::get();
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
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
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);

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
            ->when($request->get('categoryId') != 0, function ($q) use ($request) {
                $q->where('menus.category_id', $request->categoryId);
            })
            ->whereIn('menus.vendor_id', $withinVendor)
            ->when($request->get('search') != null, function ($subQ) use ($request) {
                $subQ->where('menus.name', $request->get('search'));
            })
            ->distinct()->get();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function vendorAndCategoryBasedMenu(Request $request, $id)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);

        $withinVendor = $this->vendorWithInTheRadius($latitude, $longitude, $radius);

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
            ->when($request->get('categoryId') != 0, function ($q) use ($request) {
                $q->where('menus.category_id', $request->categoryId);
            })
            ->when($request->slot != 0, function ($q) use ($request) {
                return $q->where('categories_has_slot.slot_id', '=', $request->slot);
            })
            ->when($request->get('search') != null, function ($subQ) use ($request) {
                $subQ->where('menus.name', $request->get('search'));
            })
            ->distinct()->get();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }
}
