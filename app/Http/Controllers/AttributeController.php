<?php
namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    // LIST
    public function index()
    {
        $attributes = Attribute::with('options')->get();
        return view('attributes.index', compact('attributes'));
    }

    // CREATE FORM
    public function create()
    {
        return view('attributes.create');
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'display_type' => 'nullable|string|in:chip,swatch',
        ]);

        // CREATE ATTRIBUTE
        $attribute = Attribute::create([
            'name' => $request->name,
            'display_type' => $request->input('display_type', 'chip'),
        ]);

        // CREATE OPTIONS
        foreach ($this->normalizeOptions($request->input('options', []), $request->input('display_type', 'chip')) as $option) {
            AttributeOption::create([
                'attribute_id' => $attribute->id,
                'value' => $option['value'],
                'hex_code' => $option['hex_code'],
            ]);
        }

        return redirect()->route('attributes.index')
            ->with('success', 'Attribute created successfully');
    }

    // EDIT
    public function edit($id)
    {
        $attribute = Attribute::with('options')->findOrFail($id);
        return view('attributes.edit', compact('attribute'));
    }

    // UPDATE
public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'display_type' => 'nullable|string|in:chip,swatch',
    ]);

    $attribute = Attribute::findOrFail($id);
    $displayType = $request->input('display_type', $attribute->display_type ?: 'chip');

    $attribute->update([
        'name' => $request->name,
        'display_type' => $displayType,
    ]);

    $deleteOptionIds = collect($request->input('delete_options', []))
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->all();

    if (!empty($deleteOptionIds)) {
        $attribute->options()->whereIn('id', $deleteOptionIds)->delete();
    }

    foreach ($this->normalizeOptions($request->input('options', []), $displayType) as $optionId => $optionData) {
        $attribute->options()
            ->whereKey((int) $optionId)
            ->update([
                'value' => $optionData['value'],
                'hex_code' => $optionData['hex_code'],
            ]);
    }

    foreach ($this->normalizeOptions($request->input('new_options', []), $displayType) as $optionData) {
        $attribute->options()->create([
            'value' => $optionData['value'],
            'hex_code' => $optionData['hex_code'],
        ]);
    }

    return redirect()->route('attributes.index')
        ->with('success', 'Attribute updated');
}

    // DELETE
    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return back()->with('success', 'Deleted successfully');
    }

    private function normalizeOptions(array $options, string $displayType): array
    {
        return collect($options)
            ->map(function ($option) use ($displayType) {
                if (is_array($option)) {
                    $value = trim((string) ($option['value'] ?? ''));
                    $hexCode = trim((string) ($option['hex_code'] ?? ''));
                } else {
                    $value = trim((string) $option);
                    $hexCode = '';
                }

                return [
                    'value' => $value,
                    'hex_code' => $displayType === 'swatch' && $hexCode !== '' ? strtoupper($hexCode) : null,
                ];
            })
            ->filter(fn ($option) => $option['value'] !== '')
            ->all();
    }
}
