<?php

namespace App\Http\Controllers\Admin\Members;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::where('id', '!=', authAdmin()->id);

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $admins->where('firstname', 'like', $searchTerm)
                ->OrWhere('lastname', 'like', $searchTerm)
                ->OrWhere('username', 'like', $searchTerm)
                ->OrWhere('email', 'like', $searchTerm);
        }

        $admins = $admins->orderbyDesc('id')->paginate(50);
        $admins->appends(request()->only(['search']));

        return view('admin.members.admins.index', [
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        return view('admin.members.admins.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:5', 'alpha_dash', 'block_patterns', 'max:50', 'unique:admins'],
            'email' => ['required', 'email', 'string', 'indisposable', 'block_patterns', 'max:100', 'unique:admins'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        $admin = Admin::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($admin) {
            toastr()->success(translate('Created Successfully'));
            return redirect()->route('admin.members.admins.edit', $admin->id);
        }
    }

    public function edit(Admin $admin)
    {
        abort_if($admin->id == authAdmin()->id, 404);
        return view('admin.members.admins.edit', ['admin' => $admin]);
    }

    public function update(Request $request, Admin $admin)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:5', 'alpha_dash', 'block_patterns', 'max:50', 'unique:admins,username,' . $admin->id],
            'email' => ['required', 'email', 'string', 'indisposable', 'block_patterns', 'max:100', 'unique:admins,email,' . $admin->id],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        abort_if($admin->id == authAdmin()->id, 404);

        if ($request->has('avatar')) {
            $avatar = imageUpload($request->file('avatar'), 'images/avatars/admins/', '120x120', null, $admin->avatar);
        } else {
            $avatar = $admin->avatar;
        }

        $update = $admin->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $avatar,
        ]);

        if ($update) {
            toastr()->success(translate('Updated Successfully'));
            return back();
        }
    }

    public function destroy(Admin $admin)
    {
        abort_if($admin->id == authAdmin()->id, 404);
        removeFile($admin->avatar);
        $admin->delete();
        toastr()->success(translate('Deleted Successfully'));
        return back();
    }

    public function sendMail(Request $request, Admin $admin)
    {
        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'block_patterns'],
            'reply_to' => ['required', 'email', 'block_patterns'],
            'message' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        if (!@settings('smtp')->status) {
            toastr()->error(translate('SMTP is not enabled'));
            return back()->withInput();
        }

        try {
            $email = $admin->email;
            $subject = $request->subject;
            $replyTo = $request->reply_to;
            $msg = $request->message;
            \Mail::send([], [], function ($message) use ($msg, $email, $subject, $replyTo) {
                $message->to($email)
                    ->replyTo($replyTo)
                    ->subject($subject)
                    ->html($msg);
            });
            toastr()->success(translate('Sent successfully'));
            return back();
        } catch (\Exception $e) {
            toastr()->error(translate('Sent error'));
            return back();
        }
    }

    public function showActionsForm(Admin $admin)
    {
        abort_if($admin->id == authAdmin()->id, 404);
        return view('admin.members.admins.actions', ['admin' => $admin]);
    }

    public function updateActions(Request $request, Admin $admin)
    {
        $google2faStatus = 0;
        if ($request->has('google2fa_status')) {
            if (!$admin->google2fa_status) {
                toastr()->error(translate('Two-Factor authentication cannot activated from admin side'));
                return back();
            } else {
                $google2faStatus = 1;
            }
        }

        $admin->google2fa_status = $google2faStatus;
        $admin->update();

        toastr()->success(translate('Updated Successfully'));
        return back();
    }

    public function showPasswordForm(Admin $admin)
    {
        abort_if($admin->id == authAdmin()->id, 404);
        return view('admin.members.admins.password', ['admin' => $admin]);
    }

    public function updatePassword(Request $request, Admin $admin)
    {
        $validator = Validator::make($request->all(), [
            'new-password' => ['required', 'string', 'min:6', 'confirmed'],
            'new-password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        abort_if($admin->id == authAdmin()->id, 404);

        $update = $admin->update([
            'password' => bcrypt($request->get('new-password')),
        ]);

        if ($update) {
            toastr()->success(translate('Updated Successfully'));
            return back();
        }
    }

}
