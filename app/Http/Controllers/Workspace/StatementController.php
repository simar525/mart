<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Statement;
use Carbon\Carbon;

class StatementController extends Controller
{
    public function index()
    {
        $statements = Statement::where('user_id', authUser()->id);

        if (request()->filled('date_from')) {
            $statements->where('created_at', '>=', Carbon::parse(request('date_from'))->startOfDay());
        }

        if (request()->filled('date_to')) {
            $statements->where('created_at', '<=', Carbon::parse(request('date_to'))->endOfDay());
        }

        $statements = $statements->orderbyDesc('id')->paginate(20);
        $statements->appends(request()->only(['date_from', 'date_to']));

        return theme_view('workspace.statements', [
            'statements' => $statements,
        ]);
    }
}
