<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Constants\Constants;
use App\Models\Categories;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NearByController extends Controller
{

    use ResponseTraits, ValidationTraits, CommonQueries;

    ## Service Code Started

    /**
     * preBooking menu list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preBookingList(Request $request): \Illuminate\Http\JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $origin = $latitude . ',' . $longitude;
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description;
        $withinVendor = $this->vendorWithInTheRadius($latitude, $longitude, $radius);
        $data =  $this->slotBasedMenus(0, $status, $withinVendor)
            ->when($request->get('vendorId') != 0, function ($q) use ($request) {
                $q->where('menus.vendor_id', $request->get('vendorId'));
            })
            ->when($request->get('slot_id') != 0, function ($q) use ($request) {
                $q->where('categories_has_slot.slot_id', $request->get('slot_id'));
            })
            ->where('menus.isPreOrder', 1)->paginate(10);
        $currentDateTime = Carbon::now();
        $discount = $this->getModuleIdBasedOnCode(Constants::DISCOUNT);
        $overAllDiscount = DB::table("discounts")->where('vendor_id', 0)->where('status', 2)->where('expire_at', '>=', $currentDateTime)->where('type', $discount)->first();
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

            // Discounts
            $particularDiscount = DB::table("discounts")->where('vendor_id', $subData->vendor_id)->where('status', 2)->where('expire_at', '>=', $currentDateTime)->where('type', $discount)->first();
            if ($particularDiscount) {
                if ($particularDiscount->category_id == 0) {
                    $subData->discount_percentage = $particularDiscount->percentage;
                } else if ($particularDiscount->category_id == $subData->category_id) {
                    $subData->discount_percentage = $particularDiscount->percentage;
                } else if ($overAllDiscount && $overAllDiscount->category_id == 0) {
                    $subData->discount_percentage = $overAllDiscount->percentage;
                } else if ($overAllDiscount && $overAllDiscount->category_id == $subData->category_id) {
                    $subData->discount_percentage = $overAllDiscount->percentage;
                } else {
                    $subData->discount_percentage = 0;
                }
            } else if ($overAllDiscount && $overAllDiscount->category_id == 0) {
                $subData->discount_percentage = $overAllDiscount->percentage;
            } else if ($overAllDiscount && $overAllDiscount->category_id == $subData->category_id) {
                $subData->discount_percentage = $overAllDiscount->percentage;
            } else {
                $subData->discount_percentage = 0;
            }

            if ($subData->discount_percentage != 0) {
                $percentage = $subData->discount_percentage / 100;
                $discountPrice = $subData->price * $percentage;
                $subData->discounted_price = $subData->price - $discountPrice;
            } else {
                $subData->discounted_price = 0;
            }
        }
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Today Offer Menus by Vendor or Category
     */
    public function todayOfferMenus(Request $request): \Illuminate\Http\JsonResponse
    {
        $today = Carbon::now();
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $origin = $latitude . ',' . $longitude;
        $customerId = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $approved = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($request->get('latitude'), $request->get('longitude'), $radius);

        $data = DB::table('vendors')
            ->select(
                'vendors.id as vendor_id',
                'vendors.name as vendor_name',
                'vendors.latitude',
                'vendors.longitude',
                'vendors.close_time',
                'vendors.open_time',
                'vendors.order_accept_time',
                'menus.name as name',
                'menus.image as menu_image',
                'menus.rating as rating',
                'menus.ucount as count',
                'menus.id as menu_id',
                'menus.id as id',
                'menus.isDaily as isDaily',
                'menus.description as description',
                'menus.category_id as category_id',
                'menus.price as price',
                DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist')
            )
            ->whereIn('vendors.id', $withinVendor)
            ->where('menus.status', $approved)
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'menus.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'menu');
            })
            ->paginate(10);

        $data = $this->addDistanceAndTime($data, $origin);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Slot Based menu list
     */
    public function slotBasedMenuItemsList(Request $request): \Illuminate\Http\JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $origin = $latitude . ',' . $longitude;
        $currentTime = Carbon::now()->format('H:i:s');
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($latitude, $longitude, $radius);

        $data =  $this->slotBasedMenus($request->get('slot'), $status, $withinVendor)
            ->where(function ($subQ) use ($request, $currentTime) {
                $subQ->where(function ($subQ2) use ($request) {
                    // Handle when isPreOrder is true (not checking order_accept_time)
                    $subQ2->where('menus.isPreOrder', 1);
                })->orWhere(function ($subQ3) use ($request, $currentTime) {
                    // Handle when isPreOrder is false (checking order_accept_time)
                    $subQ3->where('menus.isPreOrder', 0)
                        ->where('vendors.order_accept_time', '>', $currentTime)
                        ->where('vendors.open_time', '<', $currentTime);
                });
            })
            ->paginate(10);
        $data = $this->addDistanceAndTime($data, $origin);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * slot and category based menu list
     */
    public function slotAndCategoryMenuItemsList(Request $request): \Illuminate\Http\JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $origin = $latitude . ',' . $longitude;
        $currentTime = Carbon::now()->format('H:i:s');
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($latitude, $longitude, $radius);

        $data =  $this->slotBasedMenus($request->get('slot'), $status, $withinVendor)
            ->where(function ($subQ) use ($currentTime) {
                $subQ->where(function ($subQ2) {
                    // Handle when isPreOrder is true (not checking order_accept_time)
                    $subQ2->where('menus.isPreOrder', 1);
                })->orWhere(function ($subQ3) use ($currentTime) {
                    // Handle when isPreOrder is false (checking order_accept_time)
                    $subQ3->where('menus.isPreOrder', 0)
                        ->where('vendors.order_accept_time', '>', $currentTime)
                        ->where('vendors.open_time', '<', $currentTime);
                });
            })
            ->when($request->get('search') != null, function ($subQ) use ($request) {
                $subQ->where('menus.name', $request->get('search'));
            })->when($request->get('categoryId') != 0, function ($q) use ($request) {
                $q->where('menus.category_id', $request->get('categoryId'));
            })->paginate(10);
        $data = $this->addDistanceAndTime($data, $origin);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Vendor List
     */
    public function vendorList(Request $request): \Illuminate\Http\JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $origin = $latitude . ',' . $longitude;
        $customerId = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $approved = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($request->get('latitude'), $request->get('longitude'), $radius);

        $data = DB::table('vendors')
            ->select('vendors.id as id', 'vendors.id as vendor_id', 'vendors.name', 'vendors.image', 'vendors.latitude', 'vendors.longitude', 'vendors.rating', 'vendors.ucount as count', DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist'))
            ->whereIn('vendors.id', $withinVendor)
            ->where('status', $status)
            ->whereExists(function ($query) use ($approved) {
                $query->select(DB::raw(1))
                    ->from('menus')
                    ->where('menus.status', $approved)
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
    public function productList(Request $request): \Illuminate\Http\JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $origin = $latitude . ',' . $longitude;
        $customerId = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($request->get('latitude'), $request->get('longitude'), $radius);

        $data = DB::table('menus')
            ->select(
                'vendors.id as vendor_id',
                'menus.description',
                'menus.name',
                'menus.id',
                'menus.price',
                'menus.image',
                'vendors.name as vendor_name',
                'menus.units',
                'menus.isPreOrder',
                'vendors.latitude',
                'vendors.longitude',
                'vendors.rating',
                'vendors.ucount as count',
                DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist')
            )
            ->join('vendors', 'menus.vendor_id', '=', 'vendors.id')
            ->where('menus.status', $status)
            ->where('menus.isApproved', 1)
            ->where('menus.menu_type', 'product')
            ->whereIn('menus.vendor_id', $withinVendor)
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'vendors.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'product');
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
     * Global Search
     */
    public function globalSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $category = [];
        $vendorData = [];
        $menu = [];
        $customerId = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $menuStatus = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $origin = $request->get('latitude') . ',' . $request->get('longitude');
        $withinVendor = $this->vendorWithInTheRadius($request->get('latitude'), $request->get('longitude'), $radius);

        if ($request->get('search') != null && $request->get('search') != "") {
            // Category
            $category = Categories::where('name', 'like', '%' . $request->get('search') . '%')->whereIn('vendor_id', array_merge($withinVendor, [0]))->get();
            // Menu
            $menu = DB::table('menus')
                ->select(
                    'vendors.id as vendor_id',
                    'menus.description',
                    'menus.name',
                    'menus.id',
                    'menus.price',
                    'menus.image',
                    'vendors.name as vendor_name',
                    'menus.units',
                    'vendors.latitude',
                    'vendors.longitude',
                    'vendors.rating',
                    'vendors.ucount as count',
                    DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist')
                )
                ->join('vendors', 'menus.vendor_id', '=', 'vendors.id')
                ->leftJoin('wishlists', function ($join) use ($customerId) {
                    $join->on('wishlists.menu_id', '=', 'vendors.id')
                        ->where('wishlists.customer_id', '=', $customerId)
                        ->where(function ($query) {
                            $query->where('wishlists.type', 'product')
                                ->orWhere('wishlists.type', 'menu');
                        });
                })
                ->where('menus.name', 'like', '%' . $request->get('search') . '%')
                ->whereIn('menus.vendor_id', $withinVendor)
                ->where('menus.status', $menuStatus)
                ->where(function ($query) {
                    $query->where('menus.menu_type', 'product')
                        ->orWhere('menus.menu_type', 'menu');
                })
                ->get();
            if (count($menu) > 0) {
                $menu = $this->addDistanceAndTime($menu, $origin);
            }
            // Vendor
            $vendorData = DB::table('vendors')
                ->select(
                    'vendors.id as id',
                    'vendors.name',
                    'vendors.image as image',
                    'vendors.latitude',
                    'vendors.longitude',
                    'vendors.rating',
                    'vendors.ucount as count',
                    DB::raw('IF(wishlists.id IS NULL, false, true) AS wishlist')
                )
                ->whereIn('vendors.id', $withinVendor)
                ->where('vendors.name', 'like', '%' . $request->get('search') . '%')
                ->where('vendors.status', $status)
                ->join('menus', 'vendors.id', '=', 'menus.vendor_id')
                ->leftJoin('wishlists', function ($join) use ($customerId) {
                    $join->on('wishlists.menu_id', '=', 'vendors.id')
                        ->where('wishlists.customer_id', '=', $customerId)
                        ->where('wishlists.type', '=', 'vendor');
                })
                ->distinct()
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
    public function vendorListDropdown(Request $request): \Illuminate\Http\JsonResponse
    {
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($request->get('latitude'), $request->get('longitude'), $radius);
        $vendor = Vendor::where('name', 'like', '%' . $request->get('search') . '%')->whereIn('id', $withinVendor)->get();
        return $this->successResponse(true, $vendor, Constants::GET_SUCCESS);
    }

    /**
     * Category Based menu
     */
    public function categoryBasedMenu(Request $request): \Illuminate\Http\JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $currentTime = Carbon::now()->format('H:i:s');
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $withinVendor = $this->vendorWithInTheRadius($request->get('latitude'), $request->get('longitude'), $radius);

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
                $q->where('menus.category_id', $request->get('categoryId'));
            })
            ->where(function ($subQ) use ($request, $currentTime) {
                $subQ->where(function ($subQ2) use ($request) {
                    // Handle when isPreOrder is true (not checking order_accept_time)
                    $subQ2->where('menus.isPreOrder', 1);
                })->orWhere(function ($subQ3) use ($request, $currentTime) {
                    // Handle when isPreOrder is false (checking order_accept_time)
                    $subQ3->where('menus.isPreOrder', 0)
                        ->where('vendors.order_accept_time', '>', $currentTime)
                        ->where('vendors.open_time', '<', $currentTime);
                });
            })
            ->whereIn('menus.vendor_id', $withinVendor)
            ->when($request->get('search') != null, function ($subQ) use ($request) {
                $subQ->where('menus.name', $request->get('search'));
            })
            ->distinct()->get();
        foreach ($data as $subData) {
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
     * vendor based menus
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function vendorAndCategoryBasedMenu(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $currentTime = Carbon::now()->format('H:i:s');
        $active = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $status = $this->getModuleIdBasedOnCode(Constants::MENU_APPROVED);
        $radius = $this->getModuleBasedOnCode(Constants::RADIUS)->description; // in kilometers
        $origin = $request->get('latitude') . ',' . $request->get('longitude');
        $withinVendor = $this->vendorWithInTheRadius($latitude, $longitude, $radius);

        $data = DB::table('vendors')
            ->select(
                'menus.id as id',
                'vendors.open_time',
                'vendors.close_time',
                'vendors.order_accept_time',
                'menus.price',
                'vendors.id as vendor_id',
                'menus.description',
                'menus.name',
                'menus.isDaily',
                'vendors.name as vendorName',
                'categories.id as category_id',
                'categories.name as categoryName',
                'menus.rating',
                'menus.ucount as ratingCount',
                'menus.image',
                'vendors.latitude',
                'vendors.longitude',
                'modules.module_name as type'
            )
            ->join('menus', 'menus.vendor_id', '=', 'vendors.id')
            ->join('categories', 'categories.id', '=', 'menus.category_id')
            ->join('categories_has_slot', 'categories_has_slot.category_id', '=', 'menus.category_id')
            ->join('modules', 'modules.id', '=', 'menus.type')
            ->where('menus.isApproved', 1)
            ->where('categories.status', $active)
            ->where('menus.menu_type', 'menu')
            ->where('menus.status', $status)
            ->where('vendors.id', $id)
            ->whereIn('menus.vendor_id', $withinVendor)
            ->when($request->get('categoryId') != 0, function ($q) use ($request) {
                $q->where('menus.category_id', $request->get('categoryId'));
            })
            ->where(function ($subQ) use ($request, $currentTime) {
                $subQ->where(function ($subQ2) use ($request) {
                    // Handle when isPreOrder is true (not checking order_accept_time)
                    $subQ2->where('menus.isPreOrder', 1);
                })->orWhere(function ($subQ3) use ($request, $currentTime) {
                    // Handle when isPreOrder is false (checking order_accept_time)
                    $subQ3->where('menus.isPreOrder', 0)
                        ->where('vendors.order_accept_time', '>', $currentTime)
                        ->where('vendors.open_time', '<', $currentTime);
                });
            })
            ->when($request->get('slot') != 0, function ($q) use ($request) {
                return $q->where('categories_has_slot.slot_id', $request->get('slot'));
            })
            ->when($request->get('search') != null, function ($subQ) use ($request) {
                $subQ->where('menus.name', 'like', '%' . $request->get('search') . '%');
            })
            ->distinct()->get();
        $data = $this->addDistanceAndTime($data, $origin);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * @param $data
     * @param $origin
     * @return mixed
     */
    // private function addDistanceAndTime($data, $origin): mixed
    // {
    //     $currentDateTime = Carbon::now();
    //     $discount = $this->getModuleIdBasedOnCode(Constants::DISCOUNT);
    //     $overAllDiscount = DB::table("discounts")
    //         ->where('vendor_id', 0)
    //         ->where('status', 2)
    //         ->where('expire_at', '>=', $currentDateTime)
    //         ->where('type', $discount)
    //         ->first();

    //     $particularDiscounts = DB::table("discounts")
    //         ->whereIn('vendor_id', array_column($data, 'vendor_id'))
    //         ->where('status', 2)
    //         ->where('expire_at', '>=', $currentDateTime)
    //         ->where('type', $discount)
    //         ->get()
    //         ->keyBy('vendor_id');

    //     $overallDiscountCategoryId = $overAllDiscount ? $overAllDiscount->category_id : null;

    //     for ($i = 0; $i < count($data); $i++) {
    //         $subData = $data[$i];
    //         $destination = $subData->latitude . ',' . $subData->longitude;
    //         $google = $this->getDistance($origin, $destination);
    //         $subData->distance = $google['distance']['text'];
    //         $subData->time = $google['duration']['text'];

    //         $vendorId = $subData->vendor_id;
    //         $categoryDiscount = $particularDiscounts->get($vendorId);

    //         if ($categoryDiscount) {
    //             if ($categoryDiscount->category_id == 0 || $categoryDiscount->category_id == $subData->category_id) {
    //                 $subData->discount_percentage = $categoryDiscount->percentage;
    //             } else {
    //                 $subData->discount_percentage = 0;
    //             }
    //         } else if ($overAllDiscount) {
    //             if ($overAllDiscount->category_id == 0 || $overAllDiscount->category_id == $subData->category_id) {
    //                 $subData->discount_percentage = $overAllDiscount->percentage;
    //             } else {
    //                 $subData->discount_percentage = 0;
    //             }
    //         } else {
    //             $subData->discount_percentage = 0;
    //         }

    //         if ($subData->discount_percentage != 0) {
    //             $percentage = $subData->discount_percentage / 100;
    //             $discountPrice = $subData->price * $percentage;
    //             $subData->discounted_price = $subData->price - $discountPrice;
    //         } else {
    //             $subData->discounted_price = 0;
    //         }

    //         $data[$i] = $subData;
    //     }

    //     return $data;
    // }

    private function addDistanceAndTime($data, $origin): mixed
    {
        $currentDateTime = Carbon::now();
        $currentTime = Carbon::now()->format('H:i:s');
        $discount = $this->getModuleIdBasedOnCode(Constants::DISCOUNT);
        $overAllDiscount = DB::table("discounts")->where('vendor_id', 0)->where('status', 2)->where('expire_at', '>=', $currentDateTime)->where('type', $discount)->first();
        foreach ($data as $subData) {
            $subData->vendor_closed =  Carbon::parse($currentTime)->gt($subData->close_time);
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
            // Discounts
            $particularDiscount = DB::table("discounts")->where('vendor_id', $subData->vendor_id)->where('status', 2)->where('expire_at', '>=', $currentDateTime)->where('type', $discount)->first();
            if ($particularDiscount) {
                if ($particularDiscount->category_id == 0) {
                    $subData->discount_percentage = $particularDiscount->percentage;
                } else if ($particularDiscount->category_id == $subData->category_id) {
                    $subData->discount_percentage = $particularDiscount->percentage;
                } else if ($overAllDiscount && $overAllDiscount->category_id == 0) {
                    $subData->discount_percentage = $overAllDiscount->percentage;
                } else if ($overAllDiscount && $overAllDiscount->category_id == $subData->category_id) {
                    $subData->discount_percentage = $overAllDiscount->percentage;
                } else {
                    $subData->discount_percentage = 0;
                }
            } else if ($overAllDiscount && $overAllDiscount->category_id == 0) {
                $subData->discount_percentage = $overAllDiscount->percentage;
            } else if ($overAllDiscount && $overAllDiscount->category_id == $subData->category_id) {
                $subData->discount_percentage = $overAllDiscount->percentage;
            } else {
                $subData->discount_percentage = 0;
            }

            if ($subData->discount_percentage != 0) {
                $percentage = $subData->discount_percentage / 100;
                $discountPrice = $subData->price * $percentage;
                $subData->discounted_price = $subData->price - $discountPrice;
            } else {
                $subData->discounted_price = 0;
            }
        }
        return $data;
    }
}
