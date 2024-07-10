<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Exception;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::where('user_id', authUser()->id)
            ->active();

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $purchases->where('code', 'like', $searchTerm)
                ->OrWhereHas('item', function ($query) use ($searchTerm) {
                    $query->where('id', 'like', $searchTerm)
                        ->OrWhere('name', 'like', $searchTerm)
                        ->OrWhere('slug', 'like', $searchTerm)
                        ->OrWhere('description', 'like', $searchTerm)
                        ->OrWhere('options', 'like', $searchTerm)
                        ->OrWhere('demo_link', 'like', $searchTerm)
                        ->OrWhere('tags', 'like', $searchTerm)
                        ->OrWhere('regular_price', 'like', $searchTerm)
                        ->OrWhere('extended_price', 'like', $searchTerm);
                });
        }

        $purchases = $purchases->orderbyDesc('id')->paginate(20);
        $purchases->appends(request()->only(['search']));

        return theme_view('workspace.purchases.index', [
            'purchases' => $purchases,
        ]);
    }

    public function license($id)
    {
        $purchase = Purchase::where('id', $id)
            ->where('user_id', authUser()->id)
            ->active()
            ->firstOrFail();

        return theme_view('workspace.purchases.license', [
            'purchase' => $purchase,
        ]);
    }

    public function download($id)
    {
        $purchase = Purchase::where('id', $id)
            ->where('user_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $url = route('workspace.purchases.index');
        if (!$this->isAuthorizedURL($url)) {
            return redirect($url);
        }

        $item = $purchase->item;
        try {
            $response = $item->download();
            if (isset($response->type) && $response->type == "error") {
                throw new Exception($response->message);
            }
            $purchase->is_downloaded = true;
            $purchase->update();
            return $response;
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }

    private function isAuthorizedURL($url)
    {
        $referer = request()->server('HTTP_REFERER');
        if ($referer && filter_var($referer, FILTER_VALIDATE_URL) !== false) {
            $referer = parse_url($referer);
            $url = parse_url($url);
            if ($url['host'] == $referer['host']) {
                return true;
            }
        }
        return false;
    }
}
