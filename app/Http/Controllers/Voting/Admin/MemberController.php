<?php

namespace App\Http\Controllers\Voting\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\Voting\Admin\StoreMemberRequest;

class MemberController extends Controller
{
    public function index()
    {
        $members = User::where('role', 'member')->latest()->get();
        return View::make('voting.admin.members.index', compact('members'));
    }

    public function create()
    {
        return View::make('voting.admin.members.create');
    }

    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'member';
        $data['is_active'] = true;

        User::create($data);

        return Redirect::route('voting.admin.members.index')->with('success', 'Member berhasil ditambahkan.');
    }

    public function update(Request $request, User $member)
    {
        // Toggle status aktif
        $member->update(['is_active' => !$member->is_active]);
        
        $status = $member->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return Redirect::back()->with('success', "Member {$member->name} berhasil {$status}.");
    }

    public function destroy(User $member)
    {
        //
    }
}
