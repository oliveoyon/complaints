<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComplaintCategory;
use Illuminate\Validation\Rule;

class ComplaintCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Complaint Category')->only('index');
        $this->middleware('permission:Create Complaint Category')->only('store');
        $this->middleware('permission:Edit Complaint Category')->only(['edit','update']);
        $this->middleware('permission:Delete Complaint Category')->only('destroy');
    }

    public function test()
    {
        echo "hello";
    }

    public function index()
    {
        $categories = ComplaintCategory::all();
        return view('admin.rbac.complaint_categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:complaint_categories,name',
        ]);

        $category = ComplaintCategory::create($validated);

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'Category created successfully!'
        ]);
    }

    public function edit($id)
    {
        $category = ComplaintCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = ComplaintCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('complaint_categories')->ignore($category->id)],
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'Category updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $category = ComplaintCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!'
        ]);
    }
}
