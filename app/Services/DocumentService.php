<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Support\Collection;

class DocumentService
{
    const TYPE_KARTU_KENDALI = 'kartu_kendali';

    const TYPE_LAPORAN_ASESOR = 'laporan_asesor';

    const TYPE_SK = 'sk';

    const TYPE_SERTIFIKAT = 'sertifikat';

    const ROLE_ADMIN = 'admin';

    const ROLE_SUPER_ADMIN = 'super_admin';

    const ROLE_ASESOR = 'asesor';

    const ROLE_PESANTREN = 'pesantren';

    /**
     * Upload a document file and create the Document record.
     */
    public function upload(array $data): Document
    {
        if (isset($data['file'])) {
            $data['file_path'] = $data['file']->store('documents');
            unset($data['file']);
        }

        return Document::create($data);
    }

    /**
     * Return documents for an akreditasi filtered by role visibility rules.
     *
     * Visibility matrix:
     *   - Kartu Kendali: hidden from Asesor
     *   - Laporan Asesor: hidden from Pesantren
     *   - SK / Sertifikat: hidden from other Pesantren (only uploader's pesantren)
     */
    public function getVisibleDocuments(int $akreditasiId, string $role, ?int $userId = null, ?string $asesorScope = null): Collection
    {
        $documents = Document::with('category')
            ->where('akreditasi_id', $akreditasiId)
            ->get();

        if (in_array($role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true)) {
            return $documents;
        }

        return $documents->filter(function (Document $document) use ($role, $userId, $asesorScope) {
            if ($document->category) {
                return $document->category->isVisibleToRole($role, $asesorScope);
            }

            return $this->fallbackVisibility($document, $role, $userId);
        })->values();
    }

    private function fallbackVisibility(Document $document, string $role, ?int $userId = null): bool
    {
        if ($role === self::ROLE_ASESOR) {
            return $document->type !== self::TYPE_KARTU_KENDALI;
        }

        if ($role === self::ROLE_PESANTREN) {
            if ($document->type === self::TYPE_LAPORAN_ASESOR) {
                return false;
            }

            if (in_array($document->type, [self::TYPE_SK, self::TYPE_SERTIFIKAT], true)) {
                return ! $userId || $document->uploaded_by_user_id === $userId;
            }

            return true;
        }

        return false;
    }

    /**
     * Kartu Kendali visible to Admin, SuperAdmin, and Pesantren. Hidden from Asesor.
     */
    public function isKartuKendaliVisibleTo(string $role): bool
    {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN, self::ROLE_PESANTREN]);
    }

    /**
     * Laporan Asesor visible to Admin, SuperAdmin, and Asesor. Hidden from Pesantren.
     */
    public function isLaporanAsesorVisibleTo(string $role): bool
    {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN, self::ROLE_ASESOR]);
    }

    /**
     * Validate which required documents are still missing for an akreditasi phase.
     *
     * Returns an array of missing document requirements:
     *   [['category_id' => int, 'category_name' => string], ...]
     */
    public function validateDocumentRequirement(int $akreditasiId, string $phase): array
    {
        $requiredCategories = DocumentCategory::where('is_active', true)
            ->where(function ($query) use ($phase) {
                $query->whereNull('required_for_phase')
                    ->orWhere('required_for_phase', '')
                    ->orWhere('required_for_phase', $phase);
            })
            ->pluck('name', 'id');

        $uploadedCategoryIds = Document::where('akreditasi_id', $akreditasiId)
            ->pluck('category_id')
            ->unique()
            ->toArray();

        $missing = [];

        foreach ($requiredCategories as $categoryId => $categoryName) {
            if (! in_array($categoryId, $uploadedCategoryIds)) {
                $missing[] = [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                ];
            }
        }

        return $missing;
    }
}
