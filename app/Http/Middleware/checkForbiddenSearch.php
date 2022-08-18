<?php

namespace App\Http\Middleware;
use App\Models\ForbiddenSearch;
use Illuminate\Support\Facades\DB;
use App\Enums\EErrorCode;



use Closure;

class checkForbiddenSearch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        if(request()->filled("filter.q")){
            $forbidden_search = new ForbiddenSearch();
            $forbidden_search = $forbidden_search->from('forbidden_search as fs')->select('fs.*')->get();
            $check = DB::table('forbidden_search')->whereRaw( '? ilike \'%\' || name || \'%\' and status = ?', [request('filter')['q'], 1] )->first();
            if($check) {
                return response()->json([
                    'error' => EErrorCode::ERROR,
                    'msg' => 'Từ khóa ' . '"' .$check->name . '"' .' bạn tìm kiếm bị cấm',
                ]);
            }
        }
        
        return $next($request);
    }
}
