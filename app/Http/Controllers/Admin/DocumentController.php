<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Documents/Index', [
            'documents' => fn () => Document::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Documents/Create');
    }

    public function store(StoreDocumentRequest $request, DossierService $dossierService)
    {
        $validated = $request->validated();
        $slug = Str::slug($validated['name']);

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->storePubliclyAs('documents',
                $slug . '.' . $request->file('file')->extension(), 'public');

            $document = auth()->user()->documents()->create([
                'name' => $validated['name'],
                'slug' => $slug,
                'category' => $validated['category'],
                'description' => $validated['description'],
                'file_path' => $filePath,
            ]);

            $dossierService->create(
                auth()->user(),
                "created the document *{$document->name}*."
            );

            return redirect()->route('admin.documents.index');
        }

        $document = auth()->user()->documents()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'content' => $validated['content'],
        ]);

        $dossierService->create(
            auth()->user(),
            "created the document *{$document->name}*."
        );

        return redirect()->route('admin.documents.index');
    }

    public function edit(Document $document)
    {
        return Inertia::render('Admin/Documents/Edit', [
            'document' => $document,
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document, DossierService $dossierService)
    {
        $validated = $request->validated();
        $slug = Str::slug($validated['name']);

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->storePubliclyAs('documents',
                $slug . '.' . $request->file('file')->extension(), 'public');

            $document->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'category' => $validated['category'],
                'description' => $validated['description'],
                'file_path' => $filePath,
            ]);

            $dossierService->create(
                auth()->user(),
                "updated the document *{$document->name}*."
            );

            return redirect()->route('admin.documents.index');
        }

        $document->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'content' => $validated['content'],
        ]);

        $dossierService->create(
            auth()->user(),
            "updated the document *{$document->name}*."
        );

        return redirect()->route('admin.documents.index');
    }

    public function destroy(Document $document, DossierService $dossierService): RedirectResponse
    {
        $dossierService->create(
            auth()->user(),
            "deleted the document *{$document->name}*."
        );

        $document->delete();

        return redirect()->route('admin.documents.index');
    }
}
