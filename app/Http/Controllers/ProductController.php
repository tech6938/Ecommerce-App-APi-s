<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Models\ProductVariantOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // ─── CREATE PAGE ──────────────────────────────────────────────────────────
    public function create()
    {
        $categories = Category::with('attributes.options')->get();

        return view('products.create', compact('categories'));
    }

    // ─── STORE ────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'product_image_attribute_option_ids' => 'nullable|array',
            'specifications' => 'nullable|array',
            'specifications.*.label' => 'nullable|string|max:255',
            'specifications.*.value' => 'nullable|string|max:1000',
            'variants'    => 'nullable|array',
        ]);

        $thumbnailPath = $request->hasFile('thumbnail')
            ? $this->uploadFile($request->file('thumbnail'), 'products')
            : null;

        $specifications = collect($request->input('specifications', []))
            ->map(function ($specification) {
                return [
                    'label' => trim((string) data_get($specification, 'label', '')),
                    'value' => trim((string) data_get($specification, 'value', '')),
                ];
            })
            ->filter(fn($specification) => $specification['label'] !== '' && $specification['value'] !== '')
            ->values()
            ->all();

        // ── 1. PRODUCT SAVE ───────────────────────────────────────────────────
        $product = Product::create([
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'brand'       => $request->brand,
            'price'       => $request->price ?? 0,
            'discount_price' => $request->discount_price,
            'description' => $request->description,
            'thumbnail'   => $thumbnailPath,
            'specifications' => $specifications,
            'status'      => 'active',
        ]);

        // ── 2. PRODUCT IMAGES (multiple) ──────────────────────────────────────
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $this->uploadFile($file, 'products'),
                    'sort_order' => $index,
                    'attribute_option_id' => $request->input("product_image_attribute_option_ids.$index") ?: null,
                ]);
            }
        }

        // ── 3. VARIANTS ───────────────────────────────────────────────────────
        if ($request->filled('variants')) {
            $seenCombinations = [];
            $defaultVariantAssigned = false;

            foreach ($request->variants as $variantData) {
                $optionIds = collect($variantData['options'] ?? [])
                    ->filter()
                    ->map(fn($optionId) => (int) $optionId)
                    ->unique()
                    ->sort()
                    ->values();

                $combinationKey = $optionIds->implode('-');

                if ($combinationKey !== '' && isset($seenCombinations[$combinationKey])) {
                    return back()
                        ->withErrors(['variants' => 'Duplicate variant combinations are not allowed.'])
                        ->withInput();
                }

                if ($combinationKey !== '') {
                    $seenCombinations[$combinationKey] = true;
                }

                $isDefault = !empty($variantData['is_default']) && !$defaultVariantAssigned;

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku'        => $variantData['sku']   ?? null,
                    'price'      => $variantData['price'] ?? 0,
                    'discount_price' => $variantData['discount_price'] ?? null,
                    'stock'      => $variantData['stock'] ?? 0,
                    'is_default' => $isDefault,
                ]);

                $defaultVariantAssigned = $defaultVariantAssigned || $isDefault;

                // ── 3a. VARIANT IMAGES ─────────────────────────────────────────
                // Teri table: variant_id | image | timestamps  (sort_order nahi)
                if (!empty($variantData['images'])) {

                    foreach ($variantData['images'] as $file) {

                        if (!($file instanceof \Illuminate\Http\UploadedFile)) {
                            continue;
                        }

                        ProductVariantImage::create([
                            'variant_id' => $variant->id,
                            'image'      => $this->uploadFile($file, 'variants'),
                        ]);
                    }
                }

                // ── 3b. VARIANT OPTIONS ────────────────────────────────────────
                if ($optionIds->isNotEmpty()) {
                    foreach ($optionIds as $optionId) {

                        if (!$optionId) continue;

                        ProductVariantOption::create([
                            'variant_id'          => $variant->id,
                            'attribute_option_id' => $optionId,
                        ]);
                    }
                }
            }
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product successfully create ho gaya!');
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────
    public function index()
    {
        $products = Product::with(['category', 'images'])->latest()->get();

        return view('products.index', compact('products'));
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $product = Product::with([
            'category',
            'images.taggedOption.attribute',
            'variants.images',
            'variants.attributeOptions.attribute',
        ])->findOrFail($id);

        return view('products.show', compact('product'));
    }

    // ─── EDIT PAGE ────────────────────────────────────────────────────────────
    public function edit($id)
    {
        $categories = Category::with('attributes.options')->get();

        $product = Product::with([
            'images',
            'variants.attributeOptions.attribute',
            'variants.images',
        ])->findOrFail($id);

        $product->variants->each(function ($variant) {
            $variant->attribute_options = $variant->attributeOptions->map(function ($option) {
                return [
                    'id' => $option->id,
                    'attribute_id' => $option->attribute_id,
                    'value' => $option->value,
                ];
            });
        });

        return view('products.edit', compact('categories', 'product'));
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────
    // public function update(Request $request, $id)
    // {
    //     $product = Product::with(['variants.attributeOptions', 'variants.images'])->findOrFail($id);

    //     $request->validate([
    //         'title'       => 'required|string|max:255',
    //         'category_id' => 'required|exists:categories,id',
    //         'price'       => 'nullable|numeric|min:0',
    //         'discount_price' => 'nullable|numeric|min:0',
    //         'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //         'images'      => 'nullable|array',
    //         'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:2048',
    //         'product_image_attribute_option_ids' => 'nullable|array',
    //         'specifications' => 'nullable|array',
    //         'specifications.*.label' => 'nullable|string|max:255',
    //         'specifications.*.value' => 'nullable|string|max:1000',
    //         'variants'    => 'nullable|array',
    //     ]);

    //     $thumbnailPath = $request->hasFile('thumbnail')
    //         ? $this->uploadFile($request->file('thumbnail'), 'products')
    //         : $product->getRawOriginal('thumbnail');

    //     $specifications = collect($request->input('specifications', []))
    //         ->map(function ($specification) {
    //             return [
    //                 'label' => trim((string) data_get($specification, 'label', '')),
    //                 'value' => trim((string) data_get($specification, 'value', '')),
    //             ];
    //         })
    //         ->filter(fn($specification) => $specification['label'] !== '' && $specification['value'] !== '')
    //         ->values()
    //         ->all();

    //     DB::transaction(function () use ($request, $product, $thumbnailPath, $specifications) {
    //         $product->update([
    //             'category_id' => $request->category_id,
    //             'title'       => $request->title,
    //             'brand'       => $request->brand,
    //             'price'       => $request->price ?? 0,
    //             'discount_price' => $request->discount_price,
    //             'description' => $request->description,
    //             'thumbnail'   => $thumbnailPath,
    //             'specifications' => $specifications,
    //         ]);

    //         if ($request->filled('variants')) {
    //             $submittedVariantIds = [];
    //             $defaultVariantAssigned = false;

    //             foreach ($request->variants as $variantData) {
    //                 $variant = null;
    //                 if (!empty($variantData['id'])) {
    //                     $variant = ProductVariant::where('product_id', $product->id)
    //                         ->where('id', $variantData['id'])
    //                         ->first();
    //                 }

    //                 if (!$variant) {
    //                     $variant = new ProductVariant(['product_id' => $product->id]);
    //                 }

    //                 $optionIds = collect($variantData['options'] ?? [])
    //                     ->filter()
    //                     ->map(fn($optionId) => (int) $optionId)
    //                     ->unique()
    //                     ->sort()
    //                     ->values();

    //                 $isDefault = !empty($variantData['is_default']) && !$defaultVariantAssigned;

    //                 $variant->sku = $variantData['sku'] ?? null;
    //                 $variant->price = $variantData['price'] ?? 0;
    //                 $variant->discount_price = $variantData['discount_price'] ?? null;
    //                 $variant->stock = $variantData['stock'] ?? 0;
    //                 $variant->is_default = $isDefault;
    //                 $variant->save();

    //                 $defaultVariantAssigned = $defaultVariantAssigned || $isDefault;
    //                 $submittedVariantIds[] = $variant->id;

    //                 ProductVariantOption::where('variant_id', $variant->id)->delete();

    //                 if ($optionIds->isNotEmpty()) {
    //                     foreach ($optionIds as $optionId) {
    //                         if (!$optionId) {
    //                             continue;
    //                         }

    //                         ProductVariantOption::create([
    //                             'variant_id'          => $variant->id,
    //                             'attribute_option_id' => $optionId,
    //                         ]);
    //                     }
    //                 }

    //                 if (!empty($variantData['images'])) {
    //                     foreach ($variantData['images'] as $file) {
    //                         if (!($file instanceof \Illuminate\Http\UploadedFile)) {
    //                             continue;
    //                         }

    //                         ProductVariantImage::create([
    //                             'variant_id' => $variant->id,
    //                             'image'      => $this->uploadFile($file, 'variants'),
    //                         ]);
    //                     }
    //                 }
    //             }

    //             $product->variants()
    //                 ->whereNotIn('id', $submittedVariantIds)
    //                 ->get()
    //                 ->each(function ($variant) {
    //                     ProductVariantOption::where('variant_id', $variant->id)->delete();
    //                     ProductVariantImage::where('variant_id', $variant->id)->delete();
    //                     $variant->delete();
    //                 });
    //         }
    //     });

    //     return redirect()
    //         ->route('products.index')
    //         ->with('success', 'Product successfully updated!');
    // }

    public function update(Request $request, $id)
    {
        $product = Product::with(['variants.attributeOptions', 'variants.images'])->findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'variants'    => 'nullable|array',
            'variants.*.attribute_options' => 'nullable|array',
        ]);

        $thumbnailPath = $request->hasFile('thumbnail')
            ? $this->uploadFile($request->file('thumbnail'), 'products')
            : $product->getRawOriginal('thumbnail');

        DB::transaction(function () use ($request, $product, $thumbnailPath) {
            $product->update([
                'category_id' => $request->category_id,
                'title'       => $request->title,
                'brand'       => $request->brand,
                'price'       => $request->price ?? 0,
                'discount_price' => $request->discount_price,
                'description' => $request->description,
                'thumbnail'   => $thumbnailPath,
            ]);

            if ($request->filled('variants')) {
                $submittedVariantIds = [];
                $defaultVariantAssigned = false;

                foreach ($request->variants as $variantData) {
                    // Find or create variant
                    $variant = null;
                    if (!empty($variantData['id'])) {
                        $variant = ProductVariant::where('product_id', $product->id)
                            ->where('id', $variantData['id'])
                            ->first();
                    }

                    if (!$variant) {
                        $variant = new ProductVariant(['product_id' => $product->id]);
                    }

                    // Get attribute options from the correct structure
                    // The data comes as: attribute_options[attribute_id] = option_id
                    $optionIds = [];
                    if (!empty($variantData['attribute_options']) && is_array($variantData['attribute_options'])) {
                        foreach ($variantData['attribute_options'] as $attributeId => $optionId) {
                            if (!empty($optionId)) {
                                $optionIds[] = (int) $optionId;
                            }
                        }
                    }

                    $isDefault = !empty($variantData['is_default']) && !$defaultVariantAssigned;

                    // Update variant basic info
                    $variant->sku = $variantData['sku'] ?? null;
                    $variant->price = $variantData['price'] ?? 0;
                    $variant->discount_price = $variantData['discount_price'] ?? null;
                    $variant->stock = $variantData['stock'] ?? 0;
                    $variant->is_default = $isDefault;
                    $variant->save();

                    $defaultVariantAssigned = $defaultVariantAssigned || $isDefault;
                    $submittedVariantIds[] = $variant->id;

                    // Sync attribute options - Delete old ones
                    ProductVariantOption::where('variant_id', $variant->id)->delete();

                    // Insert new attribute options
                    foreach ($optionIds as $optionId) {
                        if ($optionId) {
                            ProductVariantOption::create([
                                'variant_id' => $variant->id,
                                'attribute_option_id' => $optionId,
                            ]);
                        }
                    }

                    // Handle deleted images
                    if (!empty($variantData['deleted_images'])) {
                        $deletedImageIds = is_array($variantData['deleted_images'])
                            ? $variantData['deleted_images']
                            : explode(',', $variantData['deleted_images']);

                        foreach ($deletedImageIds as $imageId) {
                            $image = ProductVariantImage::where('variant_id', $variant->id)
                                ->where('id', $imageId)
                                ->first();
                            if ($image) {
                                if ($image->image && Storage::disk('public')->exists($image->image)) {
                                    Storage::disk('public')->delete($image->image);
                                }
                                $image->delete();
                            }
                        }
                    }

                    // Handle new images
                    if (!empty($variantData['new_images']) && is_array($variantData['new_images'])) {
                        foreach ($variantData['new_images'] as $file) {
                            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                                continue;
                            }
                            ProductVariantImage::create([
                                'variant_id' => $variant->id,
                                'image'      => $this->uploadFile($file, 'variants'),
                            ]);
                        }
                    }
                }

                // Delete variants that were removed
                $product->variants()
                    ->whereNotIn('id', $submittedVariantIds)
                    ->get()
                    ->each(function ($variant) {
                        foreach ($variant->images as $image) {
                            if ($image->image && Storage::disk('public')->exists($image->image)) {
                                Storage::disk('public')->delete($image->image);
                            }
                        }
                        ProductVariantOption::where('variant_id', $variant->id)->delete();
                        ProductVariantImage::where('variant_id', $variant->id)->delete();
                        $variant->delete();
                    });
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product successfully updated!');
    }

    // ─── DELETE PRODUCT ──────────────────────────────────────────────────────
    public function destroy($id)
    {
        $product = Product::with(['images', 'variants.images', 'variants.attributeOptions'])->findOrFail($id);

        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                $image->delete();
            }

            foreach ($product->variants as $variant) {
                $variant->images()->delete();
                $variant->attributeOptions()->delete();
                $variant->delete();
            }

            $product->delete();
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product successfully deleted!');
    }

    // ─── HELPER: FILE UPLOAD ──────────────────────────────────────────────────
    private function uploadFile($file, string $folder): string
    {
        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($folder), $filename);
        return $folder . '/' . $filename;
    }

    // ─── CHANGE STATUS ─────────────────────────────────────
    public function changeStatus($id)
    {
        $product = Product::findOrFail($id);

        $product->status = $product->status == 'active'
            ? 'inactive'
            : 'active';

        $product->save();

        return back()->with('success', 'Product status updated successfully');
    }
}
