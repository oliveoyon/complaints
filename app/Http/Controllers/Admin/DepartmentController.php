<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Department')->only(['index']);
        $this->middleware('permission:Create Department')->only(['store']);
        $this->middleware('permission:Edit Department')->only(['edit', 'update']);
        $this->middleware('permission:Delete Department')->only(['destroy']);
    }

    public function index()
    {
        $departments = Department::all();
        return view('admin.rbac.department', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:departments,name',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($validated);

        return response()->json([
            'success' => true,
            'department' => $department,
            'message' => 'Department created successfully!'
        ]);
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return response()->json($department);
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string',
        ]);

        $department->update($validated);

        return response()->json([
            'success' => true,
            'department' => $department,
            'message' => 'Department updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully!'
        ]);
    }
}
