<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobCategory;

class JobCategoryController extends Controller
{
    public function index()
    {
        $categories = JobCategory::withCount('workerJobs')->latest()->get();
        return view('job_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('job_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string'
        ]);

        JobCategory::create($request->all());

        return redirect()->route('job-categories.index')->with('success', 'Kategori pekerjaan berhasil ditambahkan.');
    }

    public function edit(JobCategory $jobCategory)
    {
        return view('job_categories.edit', compact('jobCategory'));
    }

    public function update(Request $request, JobCategory $jobCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name,' . $jobCategory->id . ',id,deleted_at,NULL',
            'description' => 'nullable|string'
        ]);

        $jobCategory->update($request->all());

        return redirect()->route('job-categories.index')->with('success', 'Kategori pekerjaan berhasil diperbarui.');
    }

    public function destroy(Request $request, JobCategory $jobCategory)
    {
        $jobCategory->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori pekerjaan berhasil dihapus.'
            ]);
        }
        
        return redirect()->route('job-categories.index')->with('success', 'Kategori pekerjaan berhasil dihapus.');
    }
}
