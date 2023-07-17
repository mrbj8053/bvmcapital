<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
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
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);


    }

    function generateOwnId()
    {
        $rand="BVM".rand(1000000,9999999);
        $count=User::where('own_id',$rand)->count();
        while($count>0)
        {
            $rand="BM".rand(1000000,9999999);
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
            'parent_id' => $data['sponsorId'],
            'sponsor_id' => $data['sponsorId'],
            'own_id' => $ownId,
            'position' => 'noneed',
            'password' => Hash::make($data['password']),
        ]);
    }
}