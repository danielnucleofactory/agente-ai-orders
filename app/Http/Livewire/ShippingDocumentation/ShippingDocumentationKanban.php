<?php

namespace App\Http\Livewire\ShippingDocumentation;

use App\Models\ShippingDocument;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShippingDocumentationKanban extends Component
{
    public function saveAndMoveDocument()
    {
        try {
            // Validación de campos según la columna
            $this->validate([
                'newColumnId' => 'required',
                // Agrega otras validaciones según necesites
            ]);

            DB::beginTransaction();

            // Tu lógica de guardado actual
            // ...

            DB::commit();

            // Emitir evento de éxito
            $this->dispatch('document-moved-successfully');
            $this->dispatch('close-modal', 'modal-document-move');

            // Limpiar campos
            $this->reset(['comment', 'attachment']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', ['message' => 'Error al mover el documento: ' . $e->getMessage()]);
        }
    }

    public function cancelMove()
    {
        // Limpiar campos
        $this->reset(['comment', 'attachment']);
    }

    // Modifica el método setCurrentDocument para guardar el estado original
    public function setCurrentDocument($documentId, $newColumnId)
    {
        $this->currentDocumentId = $documentId;
        $this->newColumnId = $newColumnId;
        $this->originalColumnId = $this->getDocumentCurrentColumn($documentId);
    }

    private function getDocumentCurrentColumn($documentId)
    {
        // Implementa la lógica para obtener la columna actual del documento
        return ShippingDocument::find($documentId)->current_column_id;
    }
}
