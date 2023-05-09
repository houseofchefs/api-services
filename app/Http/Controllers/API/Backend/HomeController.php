<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\Discount;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
        // $this->middleware( 'role:admin');
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    public function discountList()
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $discount = Discount::where([['status', $status], ['expire_at', '>', Carbon::now()]])->orderBy('percentage', 'desc')->limit(3)->get();
        return $this->successResponse(true, $discount, $this->constant::GET_SUCCESS);
    }

    public function slotBasedMenuItemsList(Request $request)
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);
        $data =  $this->slotBasedMenus($request->lat, $request->long, $request->distance, $request->slot, $status)->where('menus.isPreOrder', 0)->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function slotAndCategoryMenuItemsList(Request $request)
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);
        $data =  $this->slotBasedMenus($request->lat, $request->long, $request->distance, $request->slot, $status)->where('menus.category_id', $request->categoryId)->where('menus.isPreOrder', 0)->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function vendorList(Request $request)
    {
        $latitude = $request->lat;
        $longitude = $request->long;
        $radius = $request->distance; // in kilometers

        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = DB::table('vendors')
            ->selectRaw('vendors.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->whereRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) <= ?', [$latitude, $longitude, $latitude, $radius])
            ->where('status', $status)
            ->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function productList(Request $request)
    {
        $latitude = $request->lat;
        $longitude = $request->long;
        $radius = $request->distance; // in kilometers
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = DB::table('products')
            ->selectRaw('products.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
            ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->where('products.status', $status)
            ->whereRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( vendors.latitude ) ) * cos( radians( vendors.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( vendors.latitude ) ) ) ) <= ?', [$latitude, $longitude, $latitude, $radius])
            ->distinct()->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }




    public function todaysOffer(Request $request)
    {
        $latitude = $request->lat;
        $longitude = $request->long;
        $radius = $request->distance; // in kilometers

        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        $today = Carbon::now();
        $vendorIds = DB::table('discounts')
            ->where('expire_at', '>=', $today)
            ->pluck('vendor_id');

        $nearestVendors = DB::table('vendors')
            ->select('vendors.id', 'vendors.name', 'vendors.latitude', 'vendors.longitude', 'menus.name as menu_name', 'menus.image as menu_image','menus.rating as rating','menus.ucount as count','menus.id as menu_id')
            ->whereIn('vendors.id', $vendorIds)
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) *
          cos( radians( latitude ) )
          * cos( radians( longitude ) - radians(?)
          ) + sin( radians(?) ) *
          sin( radians( latitude ) ) )
        ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'ASC')
            ->get();

            return $nearestVendors;
    }
}
