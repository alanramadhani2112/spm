<?php

namespace App\Services;

use App\Exceptions\WorkflowException;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Support\SuperAdminSettings;
use Illuminate\Support\Collection;

class DocumentService
{
    public const PHASE_BEFORE_VISITASI = 'before_visitasi';

    public const PHASE_BEFORE_SUBMIT = 'before_submit';

    public const PHASE_BEFORE_ADMIN_VALIDATION = 'before_admin_validation';

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
        $missing = $this->missingGlobalWorkflowDocuments($akreditasiId, $phase);

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

    public function assertDocumentRequirementsMet(int $akreditasiId, string $phase): void
    {
        $missing = $this->validateDocumentRequirement($akreditasiId, $phase);

        if ($missing === []) {
            return;
        }

        $names = collect($missing)->pluck('category_name')->implode(', ');

        throw new WorkflowException(
            "Dokumen wajib belum lengkap untuk fase ini: {$names}."
        );
    }

    private function missingGlobalWorkflowDocuments(int $akreditasiId, string $phase): array
    {
        $missing = [];

        if (
            $this->isRequirementDue(SuperAdminSettings::get(SuperAdminSettings::KARTU_KENDALI_WAJIB_BEFORE), $phase)
            && ! $this->hasDocumentType($akreditasiId, self::TYPE_KARTU_KENDALI)
        ) {
            $missing[] = [
                'category_id' => null,
                'category_name' => 'Kartu Kendali',
            ];
        }

        if (
            $this->isRequirementDue(SuperAdminSettings::get(SuperAdminSettings::LAPORAN_WAJIB_BEFORE), $phase)
            && ! $this->hasDocumentType($akreditasiId, self::TYPE_LAPORAN_ASESOR)
        ) {
            $missing[] = [
                'category_id' => null,
                'category_name' => 'Laporan Visitasi',
            ];
        }

        return $missing;
    }

    private function hasDocumentType(int $akreditasiId, string $type): bool
    {
        return Document::where('akreditasi_id', $akreditasiId)
            ->where('type', $type)
            ->exists();
    }

    private function isRequirementDue(mixed $requiredBefore, string $phase): bool
    {
        $order = [
            self::PHASE_BEFORE_VISITASI => 1,
            self::PHASE_BEFORE_SUBMIT => 2,
            self::PHASE_BEFORE_ADMIN_VALIDATION => 3,
        ];

        if (! isset($order[$requiredBefore], $order[$phase])) {
            return false;
        }

        return $order[$requiredBefore] <= $order[$phase];
    }
}
