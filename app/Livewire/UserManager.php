<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserManager extends Component
{
    use WithPagination;

    // Form fields
    public $name, $email, $password, $password_confirmation, $role = 'Viewer', $status = true, $user_id;
    
    // UI state
    public $isEdit = false;
    public $isOpen = false;
    
    // Filters
    public $search = '';
    public $filterRole = '';
    public $filterStatus = '';

    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'password' => $this->isEdit ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
            'role' => 'required|in:Admin,Editor,Viewer,CS,Leader CS,CX,Finance,Gudang',
            'status' => 'required|integer|in:0,1',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'role.required' => 'Peran (Role) wajib dipilih.',
    ];

    public function render()
    {
        $query = User::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterRole) {
            $query->where('role', $this->filterRole);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', (int) $this->filterStatus);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.user-manager', [
            'users' => $users
        ]);
    }

    #[On('create-user')]
    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->resetErrorBag();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'Viewer';
        $this->status = 1;
        $this->user_id = null;
        $this->isEdit = false;
    }

    public function store(\App\Services\UserService $userService)
    {
        $this->authorize('manage-users');
        $this->validate();

        try {
            $userService->createUser([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'role' => $this->role,
                'status' => $this->status,
            ]);

            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Pengguna baru telah ditambahkan.',
                'icon' => 'success',
                'timer' => 3000,
                'toast' => true,
                'position' => 'top-end'
            ]);

            $this->closeModal();
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'title' => 'Gagal!',
                'text' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function edit($id)
    {
        $this->authorize('manage-users');
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->status = (int) $user->status;
        $this->password = ''; 
        $this->password_confirmation = '';
        $this->isEdit = true;
        
        $this->openModal();
    }

    public function update(\App\Services\UserService $userService)
    {
        $this->authorize('manage-users');
        $this->validate();

        try {
            $user = User::findOrFail($this->user_id);
            $userService->updateUser($user, [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'status' => $this->status,
                'password' => $this->password,
            ]);

            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Data pengguna telah diperbarui.',
                'icon' => 'success',
                'timer' => 3000,
                'toast' => true,
                'position' => 'top-end'
            ]);

            $this->closeModal();
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'title' => 'Gagal!',
                'text' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function toggleStatus($id, \App\Services\UserService $userService)
    {
        $this->authorize('manage-users');

        try {
            $user = User::findOrFail($id);
            $userService->toggleStatus($user);

            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Status pengguna telah diubah menjadi ' . ($user->status ? 'Aktif' : 'Non-Aktif'),
                'icon' => 'success',
                'timer' => 2000,
                'toast' => true,
                'position' => 'top-end'
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'title' => 'Gagal!',
                'text' => $e->getMessage(),
                'icon' => 'error',
                'timer' => 3000,
                'toast' => true,
                'position' => 'top-end'
            ]);
        }
    }

    public function delete($id, \App\Services\UserService $userService)
    {
        $this->authorize('manage-users');

        try {
            $user = User::findOrFail($id);
            $userService->deleteUser($user);
            
            $this->dispatch('swal', [
                'title' => 'Terhapus!',
                'text' => 'Pengguna telah dihapus.',
                'icon' => 'warning',
                'timer' => 3000,
                'toast' => true,
                'position' => 'top-end'
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'title' => 'Gagal!',
                'text' => $e->getMessage(),
                'icon' => 'error',
                'timer' => 3000,
                'toast' => true,
                'position' => 'top-end'
            ]);
        }
    }
}
