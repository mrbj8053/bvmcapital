<?php

namespace App\Http\Controllers;

use App\Helper\myhelper;
use App\Models\Income;
use App\Models\income_misses;
use App\Models\Package;
use App\Models\PackageRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class PackageRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    function index()
    {
        $packages=Package::where('is_disabled',0)->get();
        $current=PackageRequest::where('user_id',Auth::user()->id)->first();
        return view('admin.Package.apply_package',compact('packages','current'));
    }
    function apply(Request $request)
    {
        $this->validate($request,[
            'package'=>'required|exists:packages,id',
            'payment_screenshot'=>'required|image',
        ]);

        //check if another package already in pending
        $check=PackageRequest::where('user_id',Auth::user()->id)->where('status',0)->count();
        if($check>0)
        {
            myhelper::showMessage('Already applied for a package , please wait for it before applying other.',true);
            return redirect()->back();

        }
        PackageRequest::where('user_id',Auth::user()->id)->delete();
        $packageRequest=new PackageRequest();
        $packageRequest->package=$request->package;
        $packageRequest->transaction_id=$request->transaction_id;
        $packageRequest->user_id=Auth::user()->id;
        $imagePath=myhelper::uploadImage($request->file('payment_screenshot'),'packageRequests');
        $packageRequest->payment_screenshot=$imagePath;
        if($packageRequest->save())
        {
            myhelper::showMessage('Package applied successfully');
        }
        else
        {
            myhelper::showMessage('Unable to apply package please try after some time.',true);
        }
        return redirect()->back();
    }
    function showRequests($type=0)
    {
       if($type==0)
       $title="Pending";
       else if($type==1)
       $title="Approoved";
       elseif($type==2)
       $title="Paid";

       if(Auth::user()->role=='admin')
       $packageRequests=PackageRequest::with('packageApplied','user')->get();
       else
       $packageRequests=PackageRequest::with('packageApplied','user')->where('user_id',Auth::user()->id)->get();

       $packageRequests=$packageRequests->where('status',$type);

        return view('admin.Package.show_requests',compact('packageRequests','title'));

    }
    function updateRequest(Request $request)
    {
        try {
            $package_id = Crypt::decrypt($request->id);
            $package=PackageRequest::with('packageApplied')->find($package_id);
            if($package->status!=0)
            {
                myhelper::showMessage("Unable to changes packages status, package already applied.");
            }
            else
            {
                if($request->type==1)
                {
                    $packageSelected=$package->packageApplied()->first();
                    $user=User::with('sponsor')->find($package->user_id);
                    $user->is_active=1;
                    $user->save();
                    //first send direct income
                    $amount=$packageSelected->entry_amount;
                    $directIncome=$amount*($packageSelected->direct_income/100);
                    $adminCharge=$directIncome*0.10;

                    //send only if user is active
                    if($user->sponsor->is_active==1)
                    {
                        if(!myhelper::incomeLimitReached($user->sponsor->id))
                        {
                            $income=new Income();
                            $income->user_id=$user->sponsor->id;
                            $income->from_user=$user->sponsor->id;
                            $income->income_type='Direct';
                            $income->amount=$directIncome;
                            $income->admin_charge=$adminCharge;
                            $income->admin_charge_per=10;
                            $income->net_amount=$directIncome-$adminCharge;
                            $income->save();

                            //set direct business
                            $incomeUser=User::find($user->sponsor->id);
                            $incomeUser->direct_business=$incomeUser->direct_business+$amount;
                            $incomeUser->save();
                        }
                    }
                    else
                    {
                            $income=new income_misses();
                            $income->user_id=$user->sponsor->id;
                            $income->income_type='Direct income becuase id inactive when activated '.$user->id;
                            $income->amount=$directIncome;
                            $income->admin_charge=$adminCharge;
                            $income->admin_charge_per=10;
                            $income->net_amount=$directIncome-$adminCharge;
                            $income->save();
                    }

                    //set level income fo crone



                    $package->status=1;
                    $package->save();
                    myhelper::showMessage('Package approoved successfully');

                }
                else if($request->type==2)
                {
                    $package->status=2;
                    $package->save();
                    myhelper::showMessage('Package rejected successfully');

                }
                else
                {
                    myhelper::showMessage('Invalid package ,please try again',true);

                }
            }
        } catch (Exception $e) {
            myhelper::showMessage($e->getMessage(),true);
        }
        return redirect()->back();
    }
}
