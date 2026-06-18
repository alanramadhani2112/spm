<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AkreditasiCertificateController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    public function download(Request $request, Akreditasi $akreditasi): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $role = $user->role?->parameter;
        $isAdminLike = in_array($role, ['admin', 'super_admin', 'superadmin'], true);
        $isOwnerPesantren = in_array($role, ['pesantren'], true) && (int) $akreditasi->user_id === (int) $user->id;

        abort_unless($isAdminLike || $isOwnerPesantren, 403);

        $document = Document::query()
            ->where('akreditasi_id', $akreditasi->id)
            ->where('type', DocumentService::TYPE_SERTIFIKAT)
            ->latest('id')
            ->first();

        $path = $document?->file_path ?? $akreditasi->sertifikat_path;
        abort_if(blank($path) || ! Storage::exists($path), 404);

        return Storage::download($path, 'sertifikat-'.$akreditasi->uuid.'.pdf');
    }
}
