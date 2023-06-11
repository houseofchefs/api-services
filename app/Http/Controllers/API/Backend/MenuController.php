<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Ingredients;
use App\Models\Menu;
use App\Models\MenuAvailableDay;
use App\Models\MenuHasIngredient;
use App\Models\Wishlist;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    ## Methods Started
    /**
     * @menu list for admin
     */
    public function menuListForAdmin(Request $request)
    {
        # code...
        $data = $this->menuListQuery($request->id)->paginate(10);
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * @menu approve
     */
    public function menuApprove(Request $request, string $id)
    {
        # code...
        $validator = Validator::make($request->all(), $this->menuUpdateValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Menu  #status
        $modules = $this->getModuleIdBasedOnCode($request->status);

        // Update menu
        $menu = Menu::where('id', $id)->first();
        $menu->status = $modules;
        if ($request->status == $this->constant::MENU_APPROVED) $menu->isApproved = true;
        else $menu->isApproved = false;
        $menu->save();
        return $this->successResponse(true, $menu, $this->constant::MENU_UPDATED);
    }

    /**
     * @menu list for cook
     */
    public function menuListForCook()
    {
        # code...
        $data = $this->menuListQuery(auth()->guard($this->constant::COOK_GUARD)->user()->id)->paginate();
        // $data = Menu::with(['status', 'createdBy', 'ingredients'])->where("created_by", auth()->guard($this->constant::COOK_GUARD)->user()->id)->paginate();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * create a new menu for cook's
     */
    public function menuCreateForCook(Request $request)
    {
        // $id = auth()->guard($this->constant::COOK_GUARD)->user()->id;
        $validator = Validator::make($request->all(), $this->menuValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Status #active
        $status = $this->getModuleIdBasedOnCode($this->constant::MENU_HOLD);

        // Food Type
        $type = $this->getModuleIdBasedOnCode($request->type);

        DB::transaction(function () use ($request, $status, $type) {
            // Create Menu
            $price = $request->admin_price + $request->vendor_price;
            $menu = Menu::create(array_merge($request->only(['name', 'category_id', 'vendor_id', 'vendor_price', 'menu_type', 'admin_price', 'isPreOrder', 'isDaily', 'description', 'min_quantity']), array('status' => $status, 'type' => $type, 'price' => $price)));
            if ($request->ingredient_id && count($request->ingredient_id) > 0) {
                foreach ($request->ingredient_id as $ingredients) {
                    MenuHasIngredient::create(["menu_id" => $menu->id, "ingredient_id" => $ingredients]);
                }
            }

            if ($request->isPreOrder && !$request->isDaily) {
                if ($request->days != null && count($request->days) > 0) {
                    foreach ($request->days as $day) {
                        MenuAvailableDay::create(["menu_id" => $menu->id, "day" => $day]);
                    }
                }
            }

            $path = $this->uploadImage($request->file('image'), 'vendor/' . $request->get('vendor_id') . '/menu', $menu->id . '.' . $request->file('image')->getClientOriginalExtension());
            $menu->image = $path;
            $menu->save();
        });

        return $this->successResponse(true, "", $this->constant::MENU_CREATED, $this->http::CREATED);
    }

    /**
     * @menu list for customer
     */
    public function menuListForCustomer()
    {
        $modules = $this->getModuleIdBasedOnCode($this->constant::MENU_APPROVED);
        $data = Menu::where('status', $modules)->with(['status', 'createdBy', 'ingredients'])->paginate(10);
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    /**
     * set the menu as wishlist
     */
    public function createWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), $this->wishlistValidation());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        $id = auth($this->constant::CUSTOMER_GUARD)->user()->id;
        $wishlist = Wishlist::where('type', $request->type)->where('customer_id', $id)->where('menu_id', $request->menu_id)->exists();
        if ($wishlist && !$request->flag) {
            Wishlist::where('type', $request->type)->where('customer_id', $id)->where('menu_id', $request->menu_id)->delete();
            return $this->successResponse(true, "", $this->constant::UPDATED_SUCCESS);
        } else if (!$wishlist && $request->flag) {
            Wishlist::create(array_merge($request->only(['menu_id', 'type']), array('customer_id' => $id)));
            return $this->successResponse(true, "", $this->constant::CREATED_SUCCESS, $this->http::CREATED);
        }
    }

    /**
     * wishlist menu
     */
    public function getWishListMenu(Request $request)
    {
        $id = auth($this->constant::CUSTOMER_GUARD)->user()->id;
        if ($request->type == "menu") {
            $wishlist = DB::table('wishlists')
                ->join('menus', 'menus.id', '=', 'wishlists.menu_id')
                ->join('vendors', 'menus.vendor_id', '=', 'vendors.id')
                ->select('menus.id as menu_id', 'menus.name as name', 'menus.description as description', 'menus.image as image', 'menus.price as price', 'menus.rating as rating', 'menus.ucount as ratingCount', 'vendors.name as vendorName')
                ->where('wishlists.customer_id', '=', $id)
                ->paginate(10);
        } elseif ($request->type == "product") {
            $wishlist = DB::table('wishlists')
                ->join('products', 'products.id', '=', 'wishlists.menu_id')
                ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
                ->select('products.id as product_id', 'products.name as name', 'products.description as description', 'products.image as image', 'products.price as price', 'products.rating as rating', 'products.ucount as ratingCount', 'vendors.name as vendorName')
                ->where('wishlists.customer_id', '=', $id)
                ->paginate(10);
        } elseif ($request->type == "vendor") {
            $wishlist = DB::table('wishlists')
                ->join('vendors', 'vendors.id', '=', 'wishlists.menu_id')
                ->select('vendors.id as id', 'vendors.name as name',  'vendors.rating as rating', 'vendors.ucount as ratingCount', 'vendors.latitude as latitude', 'vendors.longitude as longitude')
                ->where('wishlists.customer_id', '=', $id)
                ->paginate(10);
        }
        return $wishlist;
    }

    public function menuDetail(String $id)
    {
        # code...
        $data = $this->menuListQuery()->where('id', $id)->first();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function vendorBasedMenuList($id)
    {
        $data = DB::table('menus')
            ->where('menus.vendor_id', $id)
            ->join('modules as m1', 'menus.status', '=', 'm1.id')
            ->join('modules as m2', 'menus.type', '=', 'm2.id')
            ->leftJoin('categories', 'menus.category_id', '=', 'categories.id')
            ->selectRaw(
                'menus.id as id,
            menus.rating as rating,
            menus.name as name,
            menus.image as image,
            menus.description as description,
            menus.price,
            menus.status as mstatus,
            menus.min_quantity as min_quantity,
            m1.module_name as status,
            m2.module_name as food_type,
            categories.name as category,
            categories.id as category_id,
            menus.isApproved as approved'
            )->orderBy("menus.id", 'desc')->paginate(12);
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function ingradiantsDropDown()
    {
        $data = DB::table('ingredients')->select('id as value', 'name as label')->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function menuDetails($id)
    {
        $data = $this->menuListQuery()->where('menus.id', $id)->first();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function separateMenuDetails($id)
    {
        $data = Menu::with(['status', 'available', 'category', 'hasIngrediants.ingrediants', 'type'])->where('id', $id)->first();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function updateMenu(Request $request, $id)
    {
        // $id = auth()->guard($this->constant::COOK_GUARD)->user()->id;
        $validator = Validator::make($request->all(), $this->updateMenuValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Food Type
        $type = $this->getModuleIdBasedOnCode($request->type);


        DB::transaction(function () use ($request, $type, $id) {
            $data = Menu::where('id', $id)->first();
            if ($data->count() > 0) {
                $price = $request->vendor_price + $request->admin_price;
                Menu::where('id', $id)->update(array_merge($request->only(['name', 'category_id', 'vendor_id', 'vendor_price', 'admin_price', 'isPreOrder', 'isDaily', 'description', 'min_quantity', 'status', 'isApproved']), array('type' => $type, 'price' => $price)));
            }
            MenuHasIngredient::where('menu_id', $id)->delete();
            MenuAvailableDay::where('menu_id', $id)->delete();
            if ($request->ingredient_id && count($request->ingredient_id) > 0) {
                foreach ($request->ingredient_id as $ingredients) {
                    MenuHasIngredient::create(["menu_id" => $id, "ingredient_id" => $ingredients]);
                }
            }

            if ($request->isPreOrder && !$request->isDaily) {
                if (count($request->days) > 0) {
                    foreach ($request->days as $day) {
                        MenuAvailableDay::create(["menu_id" => $id, "day" => $day]);
                    }
                }
            }
            if (gettype($request->get('image')) != 'string') {
                $path = $this->uploadImage($request->file('image'), 'vendor/' . $request->get('vendor_id') . '/menu', $data->id . '.' . $request->file('image')->getClientOriginalExtension());
                $data->image = $path;
                $data->save();
            }
        });

        return $this->successResponse(true, "", $this->constant::MENU_UPDATED, $this->http::OK);
    }
}
