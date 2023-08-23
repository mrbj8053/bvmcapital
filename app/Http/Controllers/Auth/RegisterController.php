<?php

namespace App\Http\Controllers\Auth;

use App\Helper\myhelper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = "/login";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    public function registerSuccess(Request $request)
    {
        if(isset($request->own_id))
            $user=User::select('name','email','own_id','sponsor_id','password_crypt')->where('own_id',$request->own_id)->first();
        else
        return redirect()->login();



        return view('auth.registerSuccess',compact('user'));
    }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'sponsorId'=>['required','string','max:10','exists:users,own_id'],
            // 'position'=>['required','string','in:Left,Right'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'mobile'=>['required','string']
        ]);


    }

    function generateOwnId()
    {
        $rand="BVM".rand(1000000,9999999);
        $count=User::where('own_id',$rand)->count();
        while($count>0)
        {
            $rand="BVM".rand(1000000,9999999);
            $count=User::where('own_id',$rand)->count();
        }

        return $rand;
    }

    function getParentId($leg,$parentId)
    {


        $where['position']=$leg;
        $where['parent_id']=$parentId;
        $check=User::where($where)->first();

        while($check)
        {
            $parentId=$check->own_id;
            $where['parent_id']=$parentId;
            $check=User::where($where)->first();
        }

        return $parentId;

    }
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {

        //generate ownid
        $ownId=$this->generateOwnId();
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'parent_id' => strtoupper($data['sponsorId']),
            'sponsor_id' => strtoupper($data['sponsorId']),
            'own_id' => $ownId,
            'position' => 'noneed',
            'password' => Hash::make($data['password']),
            'password_crypt' => Crypt::encrypt($data['password']),
            'mobile' => $data['mobile'],
        ]);

    }
}
