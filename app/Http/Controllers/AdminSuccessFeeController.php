<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuccessFee;
use Illuminate\Validation\Rule;

class AdminSuccessFeeController extends Controller
{
    /**
     * Display a listing of success fees.
     */
    public function index(Request $request)
    {
        $query = SuccessFee::query();

        // Filtro por tipo de perfil
        if ($request->filled('profile_type')) {
            $query->where('profile_type', $request->profile_type);
        }

        // Filtro por status ativo
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $successFees = $query->ordered()->paginate(20);
        $profileTypes = SuccessFee::getProfileTypes();

        return view('admin.success-fees.index', compact('successFees', 'profileTypes'));
    }

    /**
     * Show the form for creating a new success fee.
     */
    public function create()
    {
        $profileTypes = SuccessFee::getProfileTypes();
        return view('admin.success-fees.create', compact('profileTypes'));
    }

    /**
     * Store a newly created success fee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'profile_type' => [
                'required',
                Rule::in(['curioso', 'inteligente', 'sabio'])
            ],
            'days' => [
                'required',
                'integer',
                'min:1',
                'max:30',
                Rule::unique('success_fees')->where(function ($query) use ($request) {
                    return $query->where('profile_type', $request->profile_type);
                })
            ],
            'fee_amount' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
        ], [
            'profile_type.required' => 'O tipo de perfil é obrigatório.',
            'profile_type.in' => 'Tipo de perfil inválido.',
            'days.required' => 'O número de dias é obrigatório.',
            'days.integer' => 'O número de dias deve ser um número inteiro.',
            'days.min' => 'O número de dias deve ser no mínimo 1.',
            'days.max' => 'O número de dias deve ser no máximo 30.',
            'days.unique' => 'Já existe uma taxa cadastrada para este perfil e número de dias.',
            'fee_amount.required' => 'O valor da taxa é obrigatório.',
            'fee_amount.numeric' => 'O valor da taxa deve ser um número.',
            'fee_amount.min' => 'O valor da taxa não pode ser negativo.',
            'fee_amount.max' => 'O valor da taxa não pode ser maior que 999.999,99.',
            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem não pode ser negativa.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
        ]);

        $successFee = SuccessFee::create($validated);

        AdminController::logAction('created', 'SuccessFee', $successFee->id, null, $successFee->toArray());

        return redirect()->route('admin.success-fees.index')
            ->with('success', 'Taxa de êxito criada com sucesso!');
    }

    /**
     * Display the specified success fee.
     */
    public function show(SuccessFee $successFee)
    {
        return view('admin.success-fees.show', compact('successFee'));
    }

    /**
     * Show the form for editing the specified success fee.
     */
    public function edit(SuccessFee $successFee)
    {
        $profileTypes = SuccessFee::getProfileTypes();
        return view('admin.success-fees.edit', compact('successFee', 'profileTypes'));
    }

    /**
     * Update the specified success fee.
     */
    public function update(Request $request, SuccessFee $successFee)
    {
        $validated = $request->validate([
            'profile_type' => [
                'required',
                Rule::in(['curioso', 'inteligente', 'sabio'])
            ],
            'days' => [
                'required',
                'integer',
                'min:1',
                'max:30',
                Rule::unique('success_fees')
                    ->where(function ($query) use ($request) {
                        return $query->where('profile_type', $request->profile_type);
                    })
                    ->ignore($successFee->id)
            ],
            'fee_amount' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
        ], [
            'profile_type.required' => 'O tipo de perfil é obrigatório.',
            'profile_type.in' => 'Tipo de perfil inválido.',
            'days.required' => 'O número de dias é obrigatório.',
            'days.integer' => 'O número de dias deve ser um número inteiro.',
            'days.min' => 'O número de dias deve ser no mínimo 1.',
            'days.max' => 'O número de dias deve ser no máximo 30.',
            'days.unique' => 'Já existe uma taxa cadastrada para este perfil e número de dias.',
            'fee_amount.required' => 'O valor da taxa é obrigatório.',
            'fee_amount.numeric' => 'O valor da taxa deve ser um número.',
            'fee_amount.min' => 'O valor da taxa não pode ser negativo.',
            'fee_amount.max' => 'O valor da taxa não pode ser maior que 999.999,99.',
            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem não pode ser negativa.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
        ]);

        $oldData = $successFee->toArray();
        $successFee->update($validated);

        AdminController::logAction('updated', 'SuccessFee', $successFee->id, $oldData, $successFee->toArray());

        return redirect()->route('admin.success-fees.index')
            ->with('success', 'Taxa de êxito atualizada com sucesso!');
    }

    /**
     * Remove the specified success fee.
     */
    public function destroy(SuccessFee $successFee)
    {
        $oldData = $successFee->toArray();
        $successFee->delete();

        AdminController::logAction('deleted', 'SuccessFee', $successFee->id, $oldData, null);

        return redirect()->route('admin.success-fees.index')
            ->with('success', 'Taxa de êxito excluída com sucesso!');
    }

    /**
     * Toggle active status of success fee.
     */
    public function toggleActive(SuccessFee $successFee)
    {
        $oldData = $successFee->toArray();
        $successFee->update(['is_active' => !$successFee->is_active]);

        AdminController::logAction(
            $successFee->is_active ? 'activated' : 'deactivated',
            'SuccessFee',
            $successFee->id,
            $oldData,
            $successFee->toArray()
        );

        return redirect()->back()
            ->with('success', $successFee->is_active 
                ? 'Taxa de êxito ativada com sucesso!' 
                : 'Taxa de êxito desativada com sucesso!');
    }
}
