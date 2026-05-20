<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryAttributeController extends Controller
{
    // LIST PAGE
    public function index()
    {
        $categories = Category::with('attributes')->get();

        return view('category-attributes.index', compact('categories'));
    }

    // CREATE PAGE
   public function create()
{
    $categories = Category::with('attributes')->get();
    $attributes = Attribute::all();

    return view('category-attributes.create', compact(
        'categories',
        'attributes'
    ));
}

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'attributes' => 'nullable|array'
        ]);

        $category = Category::findOrFail($request->category_id);

        $category->attributes()->sync(
            array_filter($request->input('attributes', []))
        );

        return redirect()
            ->route('category.attributes.index')
            ->with('success', 'Attributes linked successfully');
    }

    public function storeAttribute(Request $request, Category $category)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'display_type' => 'nullable|string|in:chip,swatch',
            'options'   => 'nullable|array',
            'options.*.value' => 'nullable|string|max:100',
            'options.*.hex_code' => 'nullable|string|max:20',
        ]);

        $attribute = Attribute::firstOrCreate(
            ['name' => $request->name],
            ['display_type' => $request->input('display_type', 'chip')]
        );

        if (!$category->attributes()->whereKey($attribute->id)->exists()) {
            $category->attributes()->attach($attribute->id);
        }

        if (!empty($request->options)) {
            foreach ($request->options as $option) {
                $value = trim((string) data_get($option, 'value', $option));
                $hexCode = trim((string) data_get($option, 'hex_code', ''));

                if ($value) {
                    AttributeOption::firstOrCreate(
                        [
                            'attribute_id' => $attribute->id,
                            'value' => $value,
                        ],
                        [
                            'hex_code' => $request->input('display_type', 'chip') === 'swatch' && $hexCode !== ''
                                ? Str::upper($hexCode)
                                : null,
                        ]
                    );
                }
            }
        }

        $category->load('attributes.options');

        return response()->json([
            'message'  => 'Attribute linked successfully',
            'category' => $category,
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /categories/{category}/attributes/{attribute}/options
    // Existing attribute mein naya option add karo
    // ─────────────────────────────────────────────────────────────────────────
    public function storeOption(Request $request, Category $category, Attribute $attribute)
    {
        $request->validate([
            'value' => 'required|string|max:100',
            'hex_code' => 'nullable|string|max:20',
        ]);

        abort_unless(
            $category->attributes()->whereKey($attribute->id)->exists(),
            404,
            'Attribute is not linked to this category.'
        );

        $value = trim($request->value);
        $hexCode = $request->filled('hex_code')
            ? Str::upper(trim($request->hex_code))
            : null;

        $option = $attribute->options()->firstOrCreate(
            ['value' => $value],
            ['hex_code' => $hexCode]
        );

        if ($hexCode && !$option->hex_code) {
            $option->update(['hex_code' => $hexCode]);
        }

        $category->load('attributes.options');

        return response()->json([
            'message'  => 'Option added successfully',
            'category' => $category,
        ], 201);
    }

    public function destroyAttribute(Category $category, Attribute $attribute)
    {
        abort_unless(
            $category->attributes()->whereKey($attribute->id)->exists(),
            404,
            'Attribute is not linked to this category.'
        );

        $category->attributes()->detach($attribute->id);
        $category->load('attributes.options');

        return response()->json([
            'message' => 'Attribute removed successfully',
            'category' => $category,
        ], 200);
    }
}
