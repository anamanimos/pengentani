<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntityController extends Controller
{
    public function index()
    {
        $entities = Entity::with('users')->latest()->get();
        return view('entities.index', compact('entities'));
    }

    public function create()
    {
        return view('entities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:investor,pengelola,perusahaan',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        Entity::create($request->all());

        return redirect()->route('entities.index')->with('success', 'Entitas berhasil dibuat.');
    }

    public function edit(Entity $entity)
    {
        $users = User::where('is_active', true)->get();
        return view('entities.edit', compact('entity', 'users'));
    }

    public function update(Request $request, Entity $entity)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:investor,pengelola,perusahaan',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $entity->update($request->all());

        return redirect()->route('entities.index')->with('success', 'Entitas berhasil diperbarui.');
    }

    public function destroy(Entity $entity)
    {
        if ($entity->investments()->count() > 0 || $entity->pengelolaPertanians()->count() > 0) {
            return redirect()->route('entities.index')->with('error', 'Entitas tidak dapat dihapus karena masih terkait dengan data pertanian.');
        }

        $entity->delete();

        return redirect()->route('entities.index')->with('success', 'Entitas berhasil dihapus.');
    }

    public function addMember(Request $request, Entity $entity)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string'
        ]);

        if ($entity->users()->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'Pengguna sudah menjadi anggota entitas ini.');
        }

        $entity->users()->attach($request->user_id, ['role' => $request->role]);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function removeMember(Entity $entity, $userId)
    {
        $entity->users()->detach($userId);

        return back()->with('success', 'Anggota berhasil dihapus.');
    }
}

