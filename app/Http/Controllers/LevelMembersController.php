<?php

namespace App\Http\Controllers;

use App\Models\LevelMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelMembersController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function showLevelmemebrs(Request $request)
    {
        if (isset($request->ownid))
            $user = User::with('levelMembers')->where('own_id', $request->ownid)->first();
        else
            $user = User::with('levelMembers')->where('own_id', Auth::user()->own_id)->first();


        return view('admin.levelMembers', compact('user'));
    }
    function showLevelTree(Request $request)
    {
        if (isset($request->ownid))
            $childs = LevelMember::where('ownid', $request->ownid)->where('level','<',3)->get();
        else
            $childs = LevelMember::where('ownid', Auth::user()->own_id)->where('level','<',3)->get();
        $childs1=$childs->where('level',1);
        $childs2=$childs->where('level',2);
        $childs3=$childs->where('level',3);
        $user=User::where('own_id',isset($request->ownid)?$request->ownid:Auth::user()->own_id)->first();

        return view('admin.myTree', compact('user','childs1','childs2','childs3'));
    }
}
