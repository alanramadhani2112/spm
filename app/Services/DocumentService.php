<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

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
    public function getVisibleDocuments(int $akreditasiId, string $role, ?int $userId = null): Collection
    {
        $query = Document::where('akreditasi_id', $akreditasiId);

        if (in_array($role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $query->get();
        }

        if ($role === self::ROLE_ASESOR) {
            return $query->where('type', '!=', self::TYPE_KARTU_KENDALI)->get();
        }

        if ($role === self::ROLE_PESANTREN) {
            $query->where('type', '!=', self::TYPE_LAPORAN_ASESOR);

            if ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereNotIn('type', [self::TYPE_SK, self::TYPE_SERTIFIKAT])
                      ->orWhere(function ($q2) use ($userId) {
                          $q2->whereIn('type', [self::TYPE_SK, self::TYPE_SERTIFIKAT])
                             ->where('uploaded_by_user_id', $userId);
                      });
                });
            }

            return $query->get();
        }

        return collect();
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
            ->pluck('name', 'id');

        $uploadedCategoryIds = Document::where('akreditasi_id', $akreditasiId)
            ->pluck('category_id')
            ->unique()
            ->toArray();

        $missing = [];

        foreach ($requiredCategories as $categoryId => $categoryName) {
            if (!in_array($categoryId, $uploadedCategoryIds)) {
                $missing[] = [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                ];
            }
        }

        return $missing;
    }
}
