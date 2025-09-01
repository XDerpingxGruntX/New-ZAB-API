<?php

namespace App\Http\Controllers;

use App\Enums\DocumentCategory;
use App\Models\Document;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index()
    {
        return Inertia::render('Documents/Index', [
            'policyDocuments' => fn () => Document::whereCategory(DocumentCategory::POLICY)->orderBy('name')->get(),
            'loaDocuments' => fn () => Document::whereCategory(DocumentCategory::LOA)->orderBy('name')->get(),
            'sopDocuments' => fn () => Document::whereCategory(DocumentCategory::SOP)->orderBy('name')->get(),
            'referenceDocuments' => fn (
            ) => Document::whereCategory(DocumentCategory::REFERENCE)->orderBy('name')->get(),
            'miscellaneousDocuments' => fn (
            ) => Document::whereCategory(DocumentCategory::MISCELLANEOUS)->orderBy('name')->get(),
        ]);
    }

    public function show(Document $document)
    {
        return Inertia::render('Documents/Show', [
            'document' => $document,
        ]);
    }
}
