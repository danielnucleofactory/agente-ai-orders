<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Livewire\Attributes\Watch;

class ProfileNew extends Component {
    use WithFileUploads;

    public $user;
    public $role;
    public $name;
    public $email;
    public $company;
    public $phone;
    public $country;
    public $city;
    public $zip;
    public $description;
    public $website;
    public $profileImage;

    public $editProfile;

    public function mount() {
        $this->user = auth()->user();
        $this->role = $this->user->roles->first()->name ?? 'Sin rol';
        $this->name = $this->user->name ?? 'Sin nombre';
        $this->email = $this->user->email ?? 'Sin correo';
        $this->company = $this->user->company->name ?? 'Sin empresa';
        $this->phone = $this->user->phone ?? 'Sin teléfono';
        $this->country = $this->user->company->country ?? 'Sin país';
        $this->city = $this->user->company->city ?? 'Sin ciudad';
        $this->zip = $this->user->company->zip ?? 'Sin código postal';
        $this->description = $this->user->description ?? 'Sin descripción';
        $this->website = $this->user->company->website ?? 'Sin sitio web';
    }

    public function activeEditProfile() {
        $this->editProfile = true;
    }

    public function updateProfile() {
        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'description' => $this->description,
        ]);

        $this->editProfile = false;
        $this->dispatch('open-modal', 'successModal');
    }

    public function cancelEditProfile() {
        $this->editProfile = false;
    }

    #[Watch('profileImage')]
    public function updatedProfileImage()
    {
        $this->validate([
            'profileImage' => 'image|max:5000',
        ], [
            'profileImage.image' => 'El archivo debe ser una imagen.',
            'profileImage.max' => 'El tamaño máximo de la imagen es de 5MB.',
        ]);

        // Eliminar todas las imágenes anteriores
        $this->user->clearMediaCollection('profile-photo');

        // Agregar la nueva imagen a la colección
        $media = $this->user->addMediaFromStream($this->profileImage->get())
            ->usingName($this->user->name . '-profile')
            ->usingFileName($this->profileImage->hashName())
            ->toMediaCollection('profile-photo');

        // Limpiar la variable temporal
        $this->profileImage = null;

        // Forzar la actualización del usuario y la vista
        $this->user = $this->user->fresh();

        // Emitir un evento para actualizar la interfaz
        $this->dispatch('profile-photo-updated');
        $this->dispatch('open-modal', 'successModal');
    }

    public function render() {
        return view('livewire.profile.profile-new');
    }
}
