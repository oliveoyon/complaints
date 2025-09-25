<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeverityLevel;
use Illuminate\Validation\Rule;

class SeverityLevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Severity Level')->only(['index']);
        $this->middleware('permission:Create Severity Level')->only(['store']);
        $this->middleware('permission:Edit Severity Level')->only(['edit', 'update']);
        $this->middleware('permission:Delete Severity Level')->only(['destroy']);
    }

    public function index()
    {
        $levels = SeverityLevel::all();
        return view('admin.rbac.severity_level', compact('levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:severity_levels,name',
            'description' => 'nullable|string',
            'priority' => 'required|integer|min:1',
        ]);

        $level = SeverityLevel::create($validated);

        return response()->json([
            'success' => true,
            'level' => $level,
            'message' => 'Severity level created successfully!'
        ]);
    }

    public function edit($id)
    {
        $level = SeverityLevel::findOrFail($id);
        return response()->json($level);
    }

    public function update(Request $request, $id)
    {
        $level = SeverityLevel::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('severity_levels')->ignore($level->id)],
            'description' => 'nullable|string',
            'priority' => 'required|integer|min:1',
        ]);

        $level->update($validated);

        return response()->json([
            'success' => true,
            'level' => $level,
            'message' => 'Severity level updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $level = SeverityLevel::findOrFail($id);
        $level->delete();

        return response()->json([
            'success' => true,
            'message' => 'Severity level deleted successfully!'
        ]);
    }
}
