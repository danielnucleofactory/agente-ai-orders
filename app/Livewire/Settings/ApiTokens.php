<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokens extends Component {

    public $tokenName = '';
    public $expiresAt = '';
    public $abilities = [];
    public $newTokenValue = '';
    public $showNewToken = false;
    public $showCreateForm = false;

    protected $rules = [
        'tokenName' => 'required|string|max:255',
        'expiresAt' => 'nullable|date|after:now',
    ];

    protected $messages = [
        'tokenName.required' => 'El nombre del token es requerido',
        'tokenName.max' => 'El nombre del token no puede exceder 255 caracteres',
        'expiresAt.date' => 'La fecha de expiración debe ser una fecha válida',
        'expiresAt.after' => 'La fecha de expiración debe ser posterior a la fecha actual',
    ];

    public function createToken()
    {
        $this->validate();

        $user = Auth::user();

        $token = $user->createToken(
            $this->tokenName,
            ['*'], // Abilities - por defecto todas las habilidades
            $this->expiresAt ? now()->parse($this->expiresAt) : null
        );

        $this->newTokenValue = $token->plainTextToken;
        $this->showNewToken = true;

        // Reset form
        $this->reset(['tokenName', 'expiresAt', 'showCreateForm']);

        // Dispatch event to open modal
        $this->dispatch('open-modal', 'new-token-modal');

        session()->flash('message', 'Token creado exitosamente. Asegúrate de copiarlo ahora, no podrás verlo nuevamente.');
    }

    public function deleteToken($tokenId)
    {
        $user = Auth::user();
        $token = $user->tokens()->find($tokenId);

        if ($token) {
            $token->delete();
            session()->flash('message', 'Token eliminado exitosamente.');
        }
    }

    public function closeNewTokenModal()
    {
        $this->showNewToken = false;
        $this->newTokenValue = '';
        $this->dispatch('close-modal', 'new-token-modal');
    }

    public function render() {
        $user = Auth::user();
        $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();

        return view('livewire.settings.api-tokens', [
            'tokens' => $tokens
        ])->layout('layouts.app');
    }
}
