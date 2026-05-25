<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload user photo with validation and optimization.
     */
    public function uploadUserPhoto(UploadedFile $file): array
    {
        $validation = $this->validateImageFile($file, 'user_photo');
        if (!$validation['valid']) {
            return $validation;
        }
        
        // Verificar se é PDF
        $isPdf = $file->getMimeType() === 'application/pdf' || strtolower($file->getClientOriginalExtension()) === 'pdf';
        
        if ($isPdf) {
            // Se for PDF, salvar diretamente sem otimização
            $extension = strtolower($file->getClientOriginalExtension()) ?: 'pdf';
            $filename = $this->generateFilename('user_photos', $file, $extension);
            $path = $file->storeAs('user_photos', $filename, 'public');
        } else {
            // Se for imagem, otimizar
            $filename = $this->generateFilename('user_photos', $file, 'jpg');
            $path = $this->storeOptimizedImage($file, 'user_photos', $filename, 400, 400);
        }
        
        return [
            'valid' => true,
            'path' => $path,
            'filename' => $filename
        ];
    }
    
    /**
     * Upload document photo (RG/CNH) with validation and optimization.
     */
    public function uploadDocumentPhoto(UploadedFile $file, string $documentType = 'rg'): array
    {
        $validation = $this->validateImageFile($file, 'document');
        if (!$validation['valid']) {
            return $validation;
        }
        
        // Verificar se é PDF
        $isPdf = $file->getMimeType() === 'application/pdf' || strtolower($file->getClientOriginalExtension()) === 'pdf';
        
        if ($isPdf) {
            // Se for PDF, salvar diretamente sem otimização
            $extension = strtolower($file->getClientOriginalExtension()) ?: 'pdf';
            $filename = $this->generateFilename('documents', $file, $extension);
            $path = $file->storeAs('documents', $filename, 'public');
        } else {
            // Se for imagem, otimizar
            $filename = $this->generateFilename('documents', $file, 'jpg');
            $path = $this->storeOptimizedImage($file, 'documents', $filename, 800, 600);
        }
        
        return [
            'valid' => true,
            'path' => $path,
            'filename' => $filename
        ];
    }
    
    /**
     * Upload quota contract document.
     */
    public function uploadQuotaContract(UploadedFile $file): array
    {
        $validation = $this->validateDocumentFile($file, 'quota_contract');
        if (!$validation['valid']) {
            return $validation;
        }
        
        $filename = $this->generateFilename('quota_contracts', $file, 'pdf');
        $path = $file->storeAs('quota_contracts', $filename, 'public');
        
        return [
            'valid' => true,
            'path' => $path,
            'filename' => $filename
        ];
    }
    
    /**
     * Upload authorization document for authorized users.
     */
    public function uploadAuthorizationDocument(UploadedFile $file): array
    {
        $validation = $this->validateDocumentFile($file, 'authorization');
        if (!$validation['valid']) {
            return $validation;
        }
        
        $filename = $this->generateFilename('authorizations', $file, 'pdf');
        $path = $file->storeAs('authorizations', $filename, 'public');
        
        return [
            'valid' => true,
            'path' => $path,
            'filename' => $filename
        ];
    }

    /**
     * Upload hospitality authorization term document (only PDF).
     */
    public function uploadHospitalityAuthorizationTerm(UploadedFile $file): array
    {
        // Validar PDF e imagens
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($mimeType, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'message' => 'O arquivo deve ser um PDF ou imagem (JPG, PNG).'
            ];
        }

        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB
            return [
                'valid' => false,
                'message' => 'O arquivo não pode ser maior que 10MB.'
            ];
        }
        
        // Usar a extensão original do arquivo
        $fileExtension = $extension ?: 'pdf';
        $filename = $this->generateFilename('hospitality_authorizations', $file, $fileExtension);
        $path = $file->storeAs('hospitality_authorizations', $filename, 'public');
        
        return [
            'valid' => true,
            'path' => $path,
            'filename' => $filename
        ];
    }

    /**
     * Modelos padrão exibidos em Termos de autorização (PDF ou imagem).
     */
    public function uploadPlatformAuthorizationDocument(UploadedFile $file): array
    {
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($mimeType, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'message' => 'O arquivo deve ser um PDF ou imagem (JPG, PNG).',
            ];
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return [
                'valid' => false,
                'message' => 'O arquivo não pode ser maior que 10MB.',
            ];
        }

        $fileExtension = $extension ?: 'pdf';
        $filename = $this->generateFilename('platform_authorization_documents', $file, $fileExtension);
        $path = $file->storeAs('platform_authorization_documents', $filename, 'public');

        return [
            'valid' => true,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    /**
     * Upload period proof document (distribution of weeks).
     */
    public function uploadPeriodProof(UploadedFile $file): array
    {
        $validation = $this->validateDocumentFile($file, 'period_proof');
        if (!$validation['valid']) {
            return $validation;
        }

        $filename = $this->generateFilename('period_proofs', $file, 'pdf');
        $path = $file->storeAs('period_proofs', $filename, 'public');

        return [
            'valid' => true,
            'path' => $path,
            'filename' => $filename
        ];
    }

    /**
     * Upload multiple hotel images.
     */
    public function uploadHotelImages(array $files): array
    {
        $uploadedImages = [];
        
        foreach ($files as $file) {
            $path = $file->store('hotels', 'public');
            $uploadedImages[] = $path;
        }
        
        return $uploadedImages;
    }
    
    /**
     * Validate image file.
     */
    private function validateImageFile(UploadedFile $file, string $type): array
    {
        $rules = [
            'user_photo' => [
                'mimes:jpeg,jpg,png,pdf'
            ],
            'document' => [
                'mimes:jpeg,jpg,png,pdf'
            ],
            'hotel_image' => [
                'mimes:jpeg,jpg,png'
            ]
        ];
        
        $validator = validator(['file' => $file], ['file' => $rules[$type] ?? []]);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => implode(', ', $validator->errors()->all())
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate document file.
     */
    private function validateDocumentFile(UploadedFile $file, string $type): array
    {
        $rules = [
            'quota_contract' => [
                'mimes:pdf,jpeg,jpg,png',
                'max:10240' // 10MB
            ],
            'authorization' => [
                'mimes:pdf,jpeg,jpg,png',
                'max:10240' // 10MB
            ],
            'period_proof' => [
                'mimes:pdf,jpeg,jpg,png',
                'max:10240' // 10MB
            ]
        ];
        
        $validator = validator(['file' => $file], ['file' => $rules[$type] ?? []]);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => implode(', ', $validator->errors()->all())
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generate unique filename.
     */
    private function generateFilename(string $directory, UploadedFile $file, string $extension): string
    {
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        return "{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Store optimized image.
     */
    private function storeOptimizedImage(UploadedFile $file, string $directory, string $filename, int $maxWidth, int $maxHeight): string
    {
        if (! \extension_loaded('gd')) {
            return $file->storeAs($directory, $filename, 'public');
        }

        $imageInfo = \getimagesize($file->getRealPath());
        if (! $imageInfo) {
            return $file->storeAs($directory, $filename, 'public');
        }

        $mimeType = $imageInfo['mime'];
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];

        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $newWidth = (int) ($sourceWidth * $ratio);
        $newHeight = (int) ($sourceHeight * $ratio);

        $sourceImage = match ($mimeType) {
            'image/jpeg', 'image/jpg' => \imagecreatefromjpeg($file->getRealPath()),
            'image/png' => \imagecreatefrompng($file->getRealPath()),
            'image/gif' => \imagecreatefromgif($file->getRealPath()),
            default => false,
        };

        if ($sourceImage === false) {
            return $file->storeAs($directory, $filename, 'public');
        }

        $newImage = \imagecreatetruecolor($newWidth, $newHeight);

        if ($mimeType === 'image/png') {
            \imagealphablending($newImage, false);
            \imagesavealpha($newImage, true);
            $transparent = \imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            \imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        \imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        $path = "{$directory}/{$filename}";
        $fullPath = storage_path('app/public/' . $path);

        $dir = \dirname($fullPath);
        if (! \is_dir($dir)) {
            \mkdir($dir, 0755, true);
        }

        \imagejpeg($newImage, $fullPath, 85);

        \imagedestroy($sourceImage);
        \imagedestroy($newImage);

        return $path;
    }
    
    /**
     * Delete file from storage.
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return false;
    }
    
    /**
     * Get file URL.
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}