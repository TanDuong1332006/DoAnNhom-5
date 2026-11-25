<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignRequest;
use App\Services\CustomerCampaigns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerCampaignController extends Controller
{
    public function preview(CampaignRequest $request,CustomerCampaigns $service)
    {
        $p=$service->preview($request->validated());unset($p['ids']);
        return response()->json(['data'=>$p,'timezone'=>'Asia/Ho_Chi_Minh']);
    }
    public function store(CampaignRequest $request,CustomerCampaigns $service)
    {
        $request->validate(['preview_hash'=>'required|string|size:64','request_key'=>'required|uuid']);
        return response()->json(['data'=>$service->create($request->validated(),$request->user()->id)],201);
    }
    public function index()
    {
        $stats=DB::table('campaign_recipients')->select('campaign_id')->selectRaw("SUM(status='delivered') delivered, SUM(status='failed') failed, SUM(status='pending') pending, SUM(status='skipped') skipped")->groupBy('campaign_id');
        return response()->json(['data'=>DB::table('customer_campaigns as c')->leftJoinSub($stats,'s','s.campaign_id','=','c.id')->select('c.*','s.delivered','s.failed','s.pending','s.skipped')->orderByDesc('c.id')->paginate(20)]);
    }
    public function dispatch(CustomerCampaigns $service)
    {
        return response()->json(['status'=>1,'delivered'=>$service->deliverDue(100)]);
    }
}
