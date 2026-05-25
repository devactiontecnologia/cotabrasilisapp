<?php

namespace App\Services;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Validator;

class CPFValidationService
{
    /**
     * Validate CPF format and check if it's unique.
     */
    public function validateCPF(string $cpf, ?int $excludeUserId = null): array
    {
        $cpf = $this->cleanCPF($cpf);
        
        // Validate format
        if (!$this->isValidCPFFormat($cpf)) {
            return [
                'valid' => false,
                'message' => 'CPF inválido. Verifique o formato.'
            ];
        }
        
        // Check if CPF is already registered
        if ($this->isCPFAlreadyRegistered($cpf, $excludeUserId)) {
            return [
                'valid' => false,
                'message' => 'CPF já cadastrado no sistema.'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'CPF válido.',
            'cpf' => $this->formatCPF($cpf)
        ];
    }
    
    /**
     * Clean CPF string (remove non-numeric characters).
     */
    public function cleanCPF(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }
    
    /**
     * Format CPF with dots and dash.
     */
    public function formatCPF(string $cpf): string
    {
        $cpf = $this->cleanCPF($cpf);
        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }
    
    /**
     * Check if CPF format is valid using algorithm.
     */
    public function isValidCPFFormat(string $cpf): bool
    {
        $cpf = $this->cleanCPF($cpf);
        
        // Check length
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Check for invalid sequences
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $firstDigit = ($remainder < 2) ? 0 : 11 - $remainder;
        
        if (intval($cpf[9]) !== $firstDigit) {
            return false;
        }
        
        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $secondDigit = ($remainder < 2) ? 0 : 11 - $remainder;
        
        return intval($cpf[10]) === $secondDigit;
    }
    
    /**
     * Check if CPF is already registered in the system.
     */
    public function isCPFAlreadyRegistered(string $cpf, ?int $excludeUserId = null): bool
    {
        $query = UserProfile::where('cpf', $this->formatCPF($cpf));
        
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        
        return $query->exists();
    }
    
    /**
     * Get CPF validation rules for Laravel validation.
     */
    public function getValidationRules(): array
    {
        return [
            'cpf' => [
                'required',
                'string',
                'min:11',
                'max:14',
                function ($attribute, $value, $fail) {
                    $result = $this->validateCPF($value);
                    if (!$result['valid']) {
                        $fail($result['message']);
                    }
                },
            ]
        ];
    }
}