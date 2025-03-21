<?php

namespace App\Livewire\Usuario;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $userId;
    public $showConfirmModal = false;
    public $showDeactivateModal = false;
    public $showEditModal = false;
    public $deactivationReason = '';
    public $userData = [
        'name' => '',
        'email' => '',
        'phone_number' => '',
        'trial' => true,
        'status' => 'active',
        'role' => 'user',
    ];

    protected $rules = [
        'userData.name' => 'required|string|max:255',
        'userData.email' => 'required|email|max:255',
        'userData.phone_number' => 'nullable|string|max:20',
        'userData.trial' => 'boolean',
        'userData.status' => 'required|in:active,inactive,pending',
        'userData.role' => 'required|in:user,admin',
    ];

    public function render()
    {
        // Solo un administrador puede ver esta página
        if (!Auth::user()->isAdmin()) {
            $this->redirect(route('dashboard'));
            return;
        }

        $usuarios = User::when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.usuario.index', [
            'usuarios' => $usuarios
        ]);
    }

    public function confirmUser($id)
    {
        $user = User::findOrFail($id);
        $user->is_confirmed = true;
        $user->status = 'active';
        $user->save();

        session()->flash('message', 'Usuario confirmado correctamente.');
    }

    public function confirmUserModal($id)
    {
        $this->userId = $id;
        $this->showConfirmModal = true;
    }

    public function confirmUserAction()
    {
        $this->confirmUser($this->userId);
        $this->showConfirmModal = false;
    }

    public function cancelConfirm()
    {
        $this->showConfirmModal = false;
    }

    public function deactivateUserModal($id)
    {
        $this->userId = $id;
        $this->deactivationReason = '';
        $this->showDeactivateModal = true;
    }

    public function deactivateUserAction()
    {
        $user = User::findOrFail($this->userId);
        $user->status = 'inactive';
        $user->deactivation_reason = $this->deactivationReason;
        $user->save();

        $this->showDeactivateModal = false;
        session()->flash('message', 'Usuario desactivado correctamente.');
    }

    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->deactivation_reason = null;
        $user->save();

        session()->flash('message', 'Usuario activado correctamente.');
    }

    public function cancelDeactivate()
    {
        $this->showDeactivateModal = false;
    }

    public function toggleTrial($id)
    {
        $user = User::findOrFail($id);
        $user->trial = !$user->trial;
        $user->save();

        session()->flash('message', 'Estado de Trial actualizado correctamente.');
    }

    public function editUserModal($id)
    {
        $this->userId = $id;
        $user = User::findOrFail($id);

        $this->userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'trial' => $user->trial,
            'status' => $user->status,
            'role' => $user->role,
        ];

        $this->showEditModal = true;
    }

    public function updateUser()
    {
        $this->validate();

        $user = User::findOrFail($this->userId);
        $user->update([
            'name' => $this->userData['name'],
            'email' => $this->userData['email'],
            'phone_number' => $this->userData['phone_number'],
            'trial' => $this->userData['trial'],
            'status' => $this->userData['status'],
            'role' => $this->userData['role'],
        ]);

        $this->showEditModal = false;
        session()->flash('message', 'Usuario actualizado correctamente.');
    }

    public function cancelEdit()
    {
        $this->showEditModal = false;
    }
}
