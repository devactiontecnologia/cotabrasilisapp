<?php

namespace App\Services;

use App\Models\KYCValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OCRValidationService
{
    /**
     * Process document with OCR and extract information.
     */
    public function processDocument(KYCValidation $validation): array
    {
        try {
            $imagePath = Storage::disk('public')->path($validation->document_photo_path);
            
            // For now, we'll simulate OCR processing
            // In production, integrate with a real OCR service like Google Vision API, AWS Textract, etc.
            $ocrData = $this->simulateOCRProcessing($imagePath, $validation->document_type);
            
            // Update validation with OCR data
            $validation->update([
                'ocr_data' => $ocrData,
                'validation_status' => 'processing'
            ]);
            
            // Perform validation based on OCR data
            $validationResult = $this->validateDocumentData($ocrData, $validation->document_type);
            
            return [
                'success' => true,
                'ocr_data' => $ocrData,
                'validation_result' => $validationResult
            ];
            
        } catch (\Exception $e) {
            Log::error('OCR processing failed', [
                'validation_id' => $validation->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro no processamento do documento. Tente novamente.'
            ];
        }
    }
    
    /**
     * Simulate OCR processing (replace with real OCR service).
     */
    private function simulateOCRProcessing(string $imagePath, string $documentType): array
    {
        // This is a simulation - in production, use a real OCR service
        $simulatedData = [
            'rg' => [
                'document_number' => '12.345.678-9',
                'name' => 'João da Silva Santos',
                'birth_date' => '15/03/1985',
                'father_name' => 'José da Silva Santos',
                'mother_name' => 'Maria da Silva Santos',
                'issuing_agency' => 'SSP/SP',
                'issue_date' => '10/05/2010',
                'confidence_score' => 0.95
            ],
            'cnh' => [
                'document_number' => '12345678901',
                'name' => 'João da Silva Santos',
                'birth_date' => '15/03/1985',
                'cpf' => '123.456.789-00',
                'category' => 'AB',
                'issue_date' => '10/05/2020',
                'expiry_date' => '10/05/2025',
                'confidence_score' => 0.92
            ],
            'passport' => [
                'document_number' => 'BR1234567',
                'name' => 'João da Silva Santos',
                'birth_date' => '15/03/1985',
                'nationality' => 'Brasileira',
                'issue_date' => '10/05/2020',
                'expiry_date' => '10/05/2030',
                'confidence_score' => 0.88
            ]
        ];
        
        return $simulatedData[$documentType] ?? [];
    }
    
    /**
     * Validate document data extracted from OCR.
     */
    private function validateDocumentData(array $ocrData, string $documentType): array
    {
        $validation = [
            'valid' => true,
            'issues' => [],
            'confidence_score' => $ocrData['confidence_score'] ?? 0
        ];
        
        // Check confidence score
        if ($validation['confidence_score'] < 0.8) {
            $validation['valid'] = false;
            $validation['issues'][] = 'Qualidade da imagem insuficiente para leitura automática';
        }
        
        // Validate required fields based on document type
        $requiredFields = $this->getRequiredFields($documentType);
        foreach ($requiredFields as $field) {
            if (empty($ocrData[$field])) {
                $validation['valid'] = false;
                $validation['issues'][] = "Campo obrigatório não encontrado: {$field}";
            }
        }
        
        // Validate document number format
        if (!$this->validateDocumentNumber($ocrData['document_number'] ?? '', $documentType)) {
            $validation['valid'] = false;
            $validation['issues'][] = 'Número do documento inválido';
        }
        
        return $validation;
    }
    
    /**
     * Get required fields for each document type.
     */
    private function getRequiredFields(string $documentType): array
    {
        return [
            'rg' => ['document_number', 'name', 'birth_date'],
            'cnh' => ['document_number', 'name', 'birth_date', 'cpf'],
            'passport' => ['document_number', 'name', 'birth_date', 'nationality']
        ][$documentType] ?? [];
    }
    
    /**
     * Validate document number format.
     */
    private function validateDocumentNumber(string $number, string $documentType): bool
    {
        $patterns = [
            'rg' => '/^\d{1,2}\.\d{3}\.\d{3}-?\d{1}$/',
            'cnh' => '/^\d{11}$/',
            'passport' => '/^[A-Z]{2}\d{7}$/'
        ];
        
        $pattern = $patterns[$documentType] ?? null;
        return $pattern ? preg_match($pattern, $number) : false;
    }
    
    /**
     * Extract CPF from CNH OCR data.
     */
    public function extractCPFFromCNH(array $ocrData): ?string
    {
        return $ocrData['cpf'] ?? null;
    }
    
    /**
     * Compare document data with user profile data.
     */
    public function compareWithProfile(array $ocrData, array $profileData): array
    {
        $comparison = [
            'matches' => true,
            'differences' => []
        ];
        
        // Compare name
        if (isset($ocrData['name']) && isset($profileData['full_name'])) {
            $ocrName = $this->normalizeName($ocrData['name']);
            $profileName = $this->normalizeName($profileData['full_name']);
            
            if ($ocrName !== $profileName) {
                $comparison['matches'] = false;
                $comparison['differences'][] = [
                    'field' => 'name',
                    'ocr_value' => $ocrData['name'],
                    'profile_value' => $profileData['full_name']
                ];
            }
        }
        
        // Compare CPF (if available in OCR data)
        if (isset($ocrData['cpf']) && isset($profileData['cpf'])) {
            $ocrCpf = preg_replace('/[^0-9]/', '', $ocrData['cpf']);
            $profileCpf = preg_replace('/[^0-9]/', '', $profileData['cpf']);
            
            if ($ocrCpf !== $profileCpf) {
                $comparison['matches'] = false;
                $comparison['differences'][] = [
                    'field' => 'cpf',
                    'ocr_value' => $ocrData['cpf'],
                    'profile_value' => $profileData['cpf']
                ];
            }
        }
        
        return $comparison;
    }
    
    /**
     * Normalize name for comparison.
     */
    private function normalizeName(string $name): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $name)));
    }
    
    /**
     * Get validation rules for OCR processing.
     */
    public function getValidationRules(): array
    {
        return [
            'document_photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB
                'dimensions:min_width=400,min_height=300'
            ]
        ];
    }
}