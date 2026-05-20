<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest()->get();

        return view('category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp',
        ]);

        $imageName = time() . '.' . $request->image->extension();

        $request->image->move(
            public_path('uploads/category'),
            $imageName
        );

        Category::create([
            'title'  => $request->title,
            'image'  => 'uploads/category/' . $imageName,
            'status' => $request->status ?? 'active',
        ]);

        return redirect()
            ->route('category.index')
            ->with('success', 'Category created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);

        return view('category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        $imagePath = $category->image;

        if ($request->hasFile('image')) {

            if (file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }

            $imageName = time() . '.' . $request->image->extension();

            $request->image->move(
                public_path('uploads/category'),
                $imageName
            );

            $imagePath = 'uploads/category/' . $imageName;
        }

        $category->update([
            'title'  => $request->title,
            'image'  => $imagePath,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('category.index')
            ->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if (file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $category->delete();

        return redirect()
            ->route('category.index')
            ->with('success', 'Category deleted successfully');
    }

    public function changeStatus($id)
    {
        $category = Category::findOrFail($id);

        $category->status =
            $category->status == 'active'
            ? 'inactive'
            : 'active';

        $category->save();

        return redirect()
            ->back()
            ->with('success', 'Category status updated successfully');
    }
}