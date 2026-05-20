<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::latest()->get();

        return view('banner.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('banner.create');
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpg,jpeg,png,webp',
    ]);

    $imageName = time() . '.' . $request->image->extension();

    $request->image->move(
        public_path('uploads/banner'),
        $imageName
    );

    Banner::create([
        'image'  => 'uploads/banner/' . $imageName,
        'status' => $request->status ?? 'active',
    ]);

    return redirect()
        ->route('banner.index')
        ->with('success', 'Banner created successfully');
}

    public function edit(string $id)
    {
        $banner = Banner::findOrFail($id);

        return view('banner.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
 public function update(Request $request, string $id)
{
    $banner = Banner::findOrFail($id);

    $request->validate([
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
    ]);

    $imagePath = $banner->image;

    if ($request->hasFile('image')) {

        if (file_exists(public_path($banner->image))) {
            unlink(public_path($banner->image));
        }

        $imageName = time() . '.' . $request->image->extension();

        $request->image->move(
            public_path('uploads/banner'),
            $imageName
        );

        $imagePath = 'uploads/banner/' . $imageName;
    }

    $banner->update([
        'image'  => $imagePath,
        'status' => $request->status,
    ]);

    return redirect()
        ->route('banner.index')
        ->with('success', 'Banner updated successfully');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);

        if (file_exists(public_path($banner->image))) {
            unlink(public_path($banner->image));
        }

        $banner->delete();

        return redirect()
            ->route('banner.index')
            ->with('success', 'Banner deleted successfully');
    }

    public function changeStatus($id)
    {
        $banner = Banner::findOrFail($id);

        $banner->status =
            $banner->status == 'active'
            ? 'inactive'
            : 'active';

        $banner->save();

        return redirect()
            ->back()
            ->with('success', 'Banner status updated successfully');
    }
}