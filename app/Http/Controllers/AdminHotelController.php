<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Http\Controllers\AdminController;
use App\Services\FileUploadService;

class AdminHotelController extends Controller
{

    /**
     * Display a listing of hotels.
     */
    public function index()
    {
        $hotels = Hotel::withCount('quotas')
            ->latest()
            ->paginate(20);

        return view('admin.hotels.index', compact('hotels'));
    }

    /**
     * Show the form for creating a new hotel.
     */
    public function create()
    {
        return view('admin.hotels.create');
    }

    /**
     * Store a newly created hotel.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'website' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
            'hotel_images' => 'required|array|min:3|max:10',
            'hotel_images.*' => 'image|mimes:jpeg,jpg,png',
        ], [
            'hotel_images.required' => 'Você deve enviar pelo menos 3 imagens do hotel.',
            'hotel_images.min' => 'Você deve enviar no mínimo 3 imagens do hotel.',
            'hotel_images.max' => 'Você pode enviar no máximo 10 imagens do hotel.',
            'hotel_images.*.image' => 'O arquivo deve ser uma imagem válida.',
            'hotel_images.*.mimes' => 'Apenas formatos JPEG, JPG e PNG são permitidos.',
        ]);

        $hotelData = $request->except('hotel_images');
        
        // Upload images
        $uploadService = new FileUploadService();
        $images = [];
        
        if ($request->hasFile('hotel_images')) {
            $uploadedImages = $uploadService->uploadHotelImages($request->file('hotel_images'));
            $hotelData['images'] = $uploadedImages;
        }

        $hotel = Hotel::create($hotelData);

        AdminController::logAction('created', 'Hotel', $hotel->id, null, $hotel->toArray());

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel criado com sucesso!');
    }

    /**
     * Display the specified hotel.
     */
    public function show(Hotel $hotel)
    {
        $hotel->load('quotas.user');
        
        return view('admin.hotels.show', compact('hotel'));
    }

    /**
     * Show the form for editing the specified hotel.
     */
    public function edit(Hotel $hotel)
    {
        return view('admin.hotels.edit', compact('hotel'));
    }

    /**
     * Update the specified hotel.
     */
    public function update(Request $request, Hotel $hotel)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'website' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
            'hotel_images' => 'nullable|array|max:10',
            'hotel_images.*' => 'image|mimes:jpeg,jpg,png',
        ], [
            'hotel_images.max' => 'Você pode enviar no máximo 10 imagens do hotel.',
            'hotel_images.*.image' => 'O arquivo deve ser uma imagem válida.',
            'hotel_images.*.mimes' => 'Apenas formatos JPEG, JPG e PNG são permitidos.',
        ]);

        $oldData = $hotel->toArray();
        
        $hotelData = $request->except('hotel_images');
        
        // Upload new images if provided
        if ($request->hasFile('hotel_images')) {
            $uploadService = new FileUploadService();
            $newImages = $uploadService->uploadHotelImages($request->file('hotel_images'));
            
            // Merge with existing images
            $existingImages = $hotel->images ?? [];
            $hotelData['images'] = array_merge($existingImages, $newImages);
            
            // Limit to 10 images total
            if (count($hotelData['images']) > 10) {
                $hotelData['images'] = array_slice($hotelData['images'], 0, 10);
            }
        }
        
        $hotel->update($hotelData);

        AdminController::logAction('updated', 'Hotel', $hotel->id, $oldData, $hotel->toArray());

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel atualizado com sucesso!');
    }

    /**
     * Toggle hotel active status.
     */
    public function toggleActive(Hotel $hotel)
    {
        $oldData = $hotel->toArray();
        
        $hotel->update(['is_active' => !$hotel->is_active]);

        $action = $hotel->is_active ? 'activated' : 'deactivated';
        AdminController::logAction($action, 'Hotel', $hotel->id, $oldData, $hotel->toArray());

        return redirect()->back()
            ->with('success', $hotel->is_active ? 'Hotel ativado!' : 'Hotel desativado!');
    }

    /**
     * Remove the specified hotel.
     */
    public function destroy(Hotel $hotel)
    {
        $oldData = $hotel->toArray();
        
        $hotel->delete();

        AdminController::logAction('deleted', 'Hotel', $hotel->id, $oldData, null);

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel removido com sucesso!');
    }
}
