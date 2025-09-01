<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDownloadRequest;
use App\Http\Requests\UpdateDownloadRequest;
use App\Models\Download;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DownloadController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Downloads/Index', [
            'downloads' => fn () => Download::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Downloads/Create');
    }

    public function store(StoreDownloadRequest $request, DossierService $dossierService)
    {
        $validated = $request->validated();
        $filePath = $request->file('file')->storePubliclyAs('downloads',
            Str::slug($validated['name']) . '.' . $request->file('file')->extension(),
            'public');

        $download = auth()->user()->downloads()->create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'file_path' => $filePath,
        ]);

        $dossierService->create(
            auth()->user(),
            "created the file *{$download->name}*."
        );

        return redirect()->route('admin.downloads.index');
    }

    public function edit(Download $download)
    {
        return Inertia::render('Admin/Downloads/Edit', [
            'download' => $download,
        ]);
    }

    public function update(UpdateDownloadRequest $request, Download $download, DossierService $dossierService)
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->storePubliclyAs('downloads',
                Str::slug($validated['name'] ?? $download->name) . '.' . $request->file('file')->extension(),
                'public');
        }

        $download->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'file_path' => $filePath ?? $download->file_path,
        ]);

        $dossierService->create(
            auth()->user(),
            "updated the file *{$download->name}*."
        );

        return redirect()->route('admin.downloads.index');
    }

    public function destroy(Download $download, DossierService $dossierService): RedirectResponse
    {
        $dossierService->create(
            auth()->user(),
            "deleted the file *{$download->name}*."
        );

        $download->delete();

        return redirect()->route('admin.downloads.index');
    }
}
