<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Complaint')->only(['index']);
        $this->middleware('permission:Create Complaint')->only(['store']);
        $this->middleware('permission:Edit Complaint')->only(['edit', 'update']);
        $this->middleware('permission:Delete Complaint')->only(['destroy']);
    }

    public function index()
    {
        $complaints = Complaint::with('user')->get();
        return view('admin.complaints.index', compact('complaints'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'status'      => ['required', Rule::in(['pending', 'in_progress', 'resolved'])],
        ]);

        $validated['user_id'] = auth()->id();

        $complaint = Complaint::create($validated);

        return response()->json([
            'success'   => true,
            'complaint' => $complaint->load('user'),
            'message'   => 'Complaint created successfully!'
        ]);
    }

    public function edit($id)
    {
        $complaint = Complaint::with('user')->findOrFail($id);
        return response()->json($complaint);
    }

    public function update(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'status'      => ['required', Rule::in(['pending', 'in_progress', 'resolved'])],
        ]);

        $complaint->update($validated);

        return response()->json([
            'success'   => true,
            'complaint' => $complaint->load('user'),
            'message'   => 'Complaint updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint deleted successfully!'
        ]);
    }
}
