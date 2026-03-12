<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class AnnouncementManager extends Component
{
    use WithFileUploads;

    public string $title = '';

    public string $content = '';

    public $image = null;

    public ?Announcement $editing = null;

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.announcement-manager', [
            'announcements' => Announcement::orderByDesc('created_at')->get(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editing = null;
    }

    public function edit(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->editing = $announcement;
        $this->title = $announcement->title;
        $this->content = $announcement->content;
        $this->image = null;
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:190'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Tope de 15 novedades: antes de insertar una nueva
        if (! $this->editing) {
            $count = Announcement::count();
            if ($count >= 15) {
                $oldest = Announcement::orderBy('created_at', 'asc')->first();
                if ($oldest && $oldest->image_path) {
                    Storage::disk('public')->delete($oldest->image_path);
                }
                $oldest?->delete();
            }
        }

        $imagePath = $this->editing?->image_path;

        if ($this->image) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->image->store('announcements', 'public');
        }

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'image_path' => $imagePath,
        ];

        if ($this->editing) {
            $this->editing->update($data);
        } else {
            $userId = Auth::id();

            if (! $userId) {
                $userId = User::where('role', 'admin')->value('id')
                    ?? User::query()->value('id');
            }

            Announcement::create($data + ['author_id' => $userId]);
        }

        $this->resetForm();
        $this->editing = null;
    }

    public function delete(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        $announcement->delete();

        if ($this->editing && $this->editing->id === $id) {
            $this->resetForm();
            $this->editing = null;
        }
    }

    protected function resetForm(): void
    {
        $this->title = '';
        $this->content = '';
        $this->image = null;
    }
}

