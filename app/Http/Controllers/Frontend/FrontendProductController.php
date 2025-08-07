<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Adverisement;
use App\Models\reviews;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\ShippingRule;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class FrontendProductController extends Controller
{

    public function productsIndex(Request $request)
    {
        $products = [];
        // Filters products for category
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->firstOrFail();
            $products = Product::where([
                'category_id' => $category->id,
                'status' => 1,
                'is_approved' => 1
            ])
            ->when($request->has('range'), function ($query) use ($request) {
                $price = explode(';', $request->range);
                $from = $price[0];
                $to = $price[1];

                return $query->where('price', '>=', $from)->where('price', '<=', $to);
            })
            ->paginate(12);

            // Filters products for SubCategory
        }
        elseif ($request->has('subcategory')) {
            $category = Subcategory::where('slug', $request->subcategory)->firstOrFail();
            $products = Product::with(['category', 'productImageGalleries'])
                ->where([
                    'sub_category_id' => $category->id,
                    'status' => 1,
                    'is_approved' => 1
                ])
                ->when($request->has('range'), function ($query) use ($request) {
                    $price = explode(';', $request->range);
                    $from = $price[0];
                    $to = $price[1];

                    return $query->where('price', '>=', $from)->where('price', '<=', $to);
                })
                ->paginate(12);

                // Filter products for ChildCategory
        }
        elseif ($request->has('childcategory')) {
            $category = ChildCategory::where('slug', $request->childcategory)->firstOrFail();
            $products = Product::with(['category', 'productImageGalleries'])
                ->where([
                    'child_category_id' => $category->id,
                    'status' => 1,
                    'is_approved' => 1
                    ])
                    ->when($request->has('range'), function ($query) use ($request) {
                                    $price = explode(';', $request->range);
                                    $from = $price[0];
                                    $to = $price[1];

                                    return $query->where('price', '>=', $from)->where('price', '<=', $to);
                                })
                ->paginate(12);

                //Filters products for Brand
        }
        elseif ($request->has('brand')) {
            $brand = Brand::where('name', $request->brand)->firstOrFail();
            $products = Product::where([
                    'brand_id' => $brand->id,
                    'status' => 1,
                    'is_approved' => 1
                ])
                ->when($request->has('range'), function ($query) use ($request) {
                    $price = explode(';', $request->range);
                    $from = $price[0];
                    $to = $price[1];

                    return $query->where('price', '>=', $from)->where('price', '<=', $to);
                })
                ->paginate(12);

                // Filters products for Searchs
            }
        elseif ($request->has('search')) {
            $products = Product::with(['category', 'productImageGalleries'])
                ->where(['status' => 1, 'is_approved' => 1])
                ->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('long_description', 'like', '%' . $request->search . '%')
                        ->orWhereHas('category', function ($query) use ($request) {
                            $query->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('long_description', 'like', '%' . $request->search . '%');
                        });
                })
                ->paginate(12);
            }
            // Show all products if not filters are applied
            else {
                $products = Product::with(['category', 'productImageGalleries'])
                    ->where(['status' => 1, 'is_approved' => 1])
                    ->orderBy('id', 'ASC')
                    // ->inRandomOrder() order random
                    ->paginate(12);
            }

            $categories = Cache::rememberForever('categories', function () {
                return Category::where(['status' => 1])->get();
            });

            $brands = Cache::rememberForever('brand', function () {
                return Brand::where(['status' => 1])->get();
            });

            $shippingRules = ShippingRule::where('type', 'min_cost')->first();


        return view('frontend.pages.product', compact('products', 'categories', 'brands','shippingRules'));
    }

public function chageListView(Request $request)
{
   Session::put('product_list_style', $request->style);
}

    /**show product details */
    public function showProduct(string $slug, Request $request)
    {

         \Log::info('Buscando producto con slug: ' . $slug);

        $product = Product::with(['category','productImageGalleries','variants', 'combinations','brand','moreEccomerce'])
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();
            if (!$product) {
                $slugParts = explode('-', $slug);
                // El slug base puede tener varios guiones, así que prueba desde el más largo al más corto
                for ($i = count($slugParts) - 1; $i > 0; $i--) {
                    $baseSlug = implode('-', array_slice($slugParts, 0, $i));
                    $product = Product::with(['category','productImageGalleries','variants', 'combinations','brand','moreEccomerce'])
                        ->where('slug', $baseSlug)
                        ->where('status', 1)
                        ->first();
                    if ($product) break;
                }
            }
            $hasCombinations = $product->combinations->count() > 0;
            $productCombinations = $product->combinations;

            // Si tiene combinaciones y NO hay comb en la URL, redirecciona a la combinación por defecto
            if ($hasCombinations && !$request->has('comb')) {
                $defaultCombination = $productCombinations->where('is_default', 1)->first() ?? $productCombinations->first();
                if ($defaultCombination) {
                    // Construye el slug con la variante principal (ejemplo: tipo-j)
                    $mainVariantName = '';
                    if (count($product->variants) > 0) {
                        $mainVariant = $product->variants->first();
                        $mainItemId = json_decode($defaultCombination->variants_item_ids, true)[0] ?? null;
                        $mainItem = $mainVariant->productVariantItems->where('id', $mainItemId)->first();
                        if ($mainItem) {
                            $mainVariantName = \Str::slug($mainItem->name);
                        }
                    }
                    $newUrl = route('product-detail', [
                        'slug' => $product->slug . ($mainVariantName ? '-' . $mainVariantName : ''),
                        'comb' => $defaultCombination->id
                    ]);
                    return redirect($newUrl);
                }
            }

            $selectedCombination = null;
            if ($hasCombinations) {
                if ($request->has('comb')) {
                    $selectedCombination = $productCombinations->where('id', $request->comb)->first();
                }
                if (!$selectedCombination) {
                    $selectedCombination = $productCombinations->first();
                }
            }
        $reviews = ProductReview::where('product_id', $product->id)->where('status', 1)->paginate(6);

        $ratingCounts = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        $averageRating = $reviews->avg('rating');

        return view('frontend.pages.product-detail', compact('product','reviews', 'ratingCounts', 'averageRating', 'selectedCombination','hasCombinations', 'productCombinations'));
    }
}
