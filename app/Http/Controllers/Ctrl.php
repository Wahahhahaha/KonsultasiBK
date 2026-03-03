<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class Ctrl extends Controller
{

    public function notfound(){
        return response()->view('all.error', [], 404);
    }

//==========================================================================================

    public function login(){
        $system = DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view ('all.login',compact('system'));
        echo view ('all.footer');
    }

    public function loginact(Request $request){
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::table('user')
            ->where('username', $request->username)
            ->first();

        if (!$user) {
            return back()->with('error', 'Username not found!');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password wrong');
        }

        $name = null;
        $email = null;
        $phonenumber = null;
        $role = null;

        if ($user->levelid == 3) {
            $data = DB::table('student')->where('userid', $user->userid)->first();

            if ($data) {
                $name = $data->name;
                $email = $data->email;
                $phonenumber = $data->phonenumber;
            }

        } elseif ($user->levelid == 1) {
            $data = DB::table('employer')
                ->leftJoin('role', 'role.roleid', '=', 'employer.roleid')
                ->where('employer.userid', $user->userid)
                ->select('employer.*', 'role.rolename')
                ->first();

            if ($data) {
                $name = $data->name;
                $email = $data->email;
                $phonenumber = $data->phonenumber;
                $role = $data->rolename;
            }

        } else {
            $data = DB::table('teacher')
                ->leftJoin('role', 'role.roleid', '=', 'teacher.roleid')
                ->where('teacher.userid', $user->userid)
                ->select('teacher.*', 'role.rolename')
                ->first();

            if ($data) {
                $name = $data->name;
                $email = $data->email;
                $phonenumber = $data->phonenumber;
                $role = $data->rolename;
            }
        }

        Session::put([
            'userid' => $user->userid,
            'username' => $user->username,
            'level' => $user->levelid,
            'name' => $name,
            'email' => $email,
            'phonenumber' => $phonenumber,
            'role' => $role,
            'is_login' => true
        ]);

        return redirect('/home')->with('success', 'Login successful');
    }

    public function logout(Request $request){
        $userid = $request->session()->get('userid');

        $this->logActivity('logout', 'auth', $userid, 'User logged out');

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect ('/home');
    }

//==============================================================================================

    public function register(){
        $system=DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view ('all.register',compact('system'));
        echo view ('all.footer');
    }

    public function registeract(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    }

    public function loadactivation(){
        echo view ('all.header',compact('system'));
        echo view ('all.loadactivation');
        echo view ('all.footer');
    }

//==============================================================================================

    public function home(){
        $system = DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('all.home',compact('system'));
        echo view ('all.footer');
    }

//=====================================================================================

    public function userdata(){
        $data=DB::table('user')
            ->leftjoin('level','level.levelid','=','user.levelid')
            ->leftjoin('employer','employer.userid','=','user.userid')
            ->leftjoin('teacher','teacher.userid','=','user.userid')
            ->leftjoin('student','student.userid','=','user.userid')
            ->leftJoin('role', function($join) {
                $join->on('role.roleid', '=', 'employer.roleid')
                ->orOn('role.roleid', '=', 'teacher.roleid');
            })
            ->select(
                'user.userid',
                'user.username',
                'level.levelname',
                'role.rolename',

                DB::raw('COALESCE(teacher.email, employer.email,student.email) as email'),
                DB::raw('COALESCE(teacher.phonenumber, employer.phonenumber,student.phonenumber) as phonenumber'),
                DB::raw('COALESCE(teacher.name, employer.name,student.name) as name'),
            )
            ->get();
        // $system = DB::table('system')->first();
        // $level = DB::table('level')->get();
        // $role = DB::table('role')->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('admin.userdata',compact('data'));
        echo view ('all.footer');
    }

    public function deleteuser($id){
        $user = DB::table('user')
               ->where('userid',$id)
               ->first();

        $student = DB::table('student')
               ->where('userid',$id)
               ->first();

        $employer = DB::table('employer')
               ->where('userid',$id)
               ->first();

        $teacher = DB::table('teacher')
               ->where('userid',$id)
               ->first();

        DB::table('user')
               ->where('userid',$id)
               ->delete();

        DB::table('student')
               ->where('userid',$id)
               ->delete();

        DB::table('employer')
               ->where('userid',$id)
               ->delete();

        DB::table('teacher')
               ->where('userid',$id)
               ->delete();

        return back();
    }

    public function userresetpassword($id){
        $user = DB::table('user')->where('userid', $id)->first();

        DB::table('user')->where('userid', $id)->update([
            'password' => Hash::make($user->username)
        ]);

        return back();
    }

//====================================================================================
    public function setting(){
        $system = DB::table('system')->first();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('superadmin.setting',compact('system'));
        echo view ('all.footer');
    }

    public function savesetting(Request $request){
        $old = DB::table('system')
            ->where('systemid', $request->systemid)
            ->first();

        $request->validate([
            'name'=>'required',
            'logo'=>'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'address'=>'required',
            'manager'=>'required',
            'contact'=>'required',
        ]);

        if ($request->hasFile('logo')) {
            if ($old && $old->systemlogo) {
                Storage::delete('public/' . $old->systemlogo);
            }
            $fotoPath = $request->file('logo')->store('uploads', 'public');
        } else {
            $fotoPath = $old->systemlogo; // pakai logo lama
        }

        $data = [
            'systemname'     => $request->name,
            'systemlogo'     => $fotoPath,
            'systemaddress'  => $request->address,
            'systemmanager'        => $request->manager,
            'systemcontact'  => $request->contact,
        ];

        DB::table('system')
            ->where('systemid', $request->systemid)
            ->update($data);
        return redirect('/setting')->with('success','Setting succesafully updated');
    }

//=================================================================================================
    public function profile(Request $request){
        $system = DB::table('system')->first();
        $userid = $request->session()->get('userid');

                $data = DB::table('user')
            ->leftJoin('human', 'human.userid', '=', 'user.userid')
            ->leftJoin('employer', 'employer.userid', '=', 'user.userid')

            ->leftJoin('blood as bh', 'bh.bloodid', '=', 'human.bloodid')
            ->leftJoin('blood as be', 'be.bloodid', '=', 'employer.bloodid')

            ->select(
                'user.userid',
                'user.username',

                DB::raw('COALESCE(human.email, employer.email) as email'),
                DB::raw('COALESCE(human.phonenumber, employer.phonenumber) as phonenumber'),
                DB::raw('COALESCE(human.name, employer.name) as name'),
                DB::raw('COALESCE(human.gender, employer.gender) as gender'),
                DB::raw('COALESCE(human.birthdate, employer.birthdate) as birthdate'),
                DB::raw('COALESCE(human.picture, employer.picture) as picture'),

                DB::raw('COALESCE(bh.bloodid, be.bloodid) as bloodid'),
                DB::raw('COALESCE(bh.bloodtype, be.bloodtype) as bloodtype')
            )
            ->where('user.userid', $userid)
            ->first();
        $blood = DB::table('blood')->get();

        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));  
        echo view('all.profile', compact('data','blood'));
        echo view('all.footer');

    }

    public function updateprofile(Request $request){
        $userid = session('userid');

        $oldPhoto = DB::table('human')
        ->where('userid', $userid)
        ->value('picture');

        if ($request->hasFile('picture')) {
            if (!empty($oldPhoto) && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
            $path = $request->file('picture')->store('profile', 'public');
        } else {
            $path = $oldPhoto;
        }

        $oldPhotos = DB::table('employer')
        ->where('userid', $userid)
        ->value('picture');

        if ($request->hasFile('picture')) {
            if (!empty($oldPhotos) && Storage::disk('public')->exists($oldPhotos)) {
                Storage::disk('public')->delete($oldPhotos);
            }
            $path = $request->file('picture')->store('profile', 'public');
        } else {
            $path = $oldPhotos;
        }

        DB::table('user')
        ->where('userid', $userid)
        ->update([
            'username' => $request->username
            ]);

        DB::table('human')
            ->where('userid', $userid)
            ->update([
                'picture' => $path,
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phone,
            ]);

        DB::table('employer')
            ->where('userid', $userid)
            ->update([
                'picture' => $path,
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phone,
            ]);

        return back()->with('success', 'Profile updated!');
    }

    public function changepw(Request $request){
        $userid = session('userid');
        $user = DB::table('user')->where('userid', $userid)->first();
        if (!Hash::check($request->cp, $user->password)) {
            return back()->with('error', 'Current password wrong');
        }

        DB::table('user')
            ->where('userid', $userid)
            ->update([
                'password' => Hash::make($request->np)
            ]);

        return back()->with('success', 'Password changed successfully');
    }

//========================================================================================================
    public function databasePage(){
        $system = DB::table('system')->first();

        echo view ('all.header',compact('system'));
        echo view('all.menu', compact('system'));
        echo view('superadmin.database');
        echo view('all.footer');
    }

    public function exportDatabase(){
        $tables = array_map('current', DB::select('SHOW TABLES'));

        $sql = "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = DB::select("SHOW CREATE TABLE `$table`")[0]->{"Create Table"};
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $create . ";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->count() === 0) {
                continue;
            }

            foreach ($rows as $row) {
                $columns = array_keys((array) $row);
                $values = array_map(function ($value) {
                    if (is_null($value)) {
                        return 'NULL';
                    }
                    return "'" . str_replace("'", "''", $value) . "'";
                }, array_values((array) $row));

                $sql .= "REPLACE INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES (" . implode(',', $values) . ");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_' . date('Ymd_His') . '.sql';

        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function importDatabase(Request $request){
        $request->validate([
            'backup_file' => 'required|file'
        ]);

        $path = $request->file('backup_file')->getRealPath();
        $contents = file_get_contents($path);

        $statements = array_filter(array_map('trim', explode(";\n", $contents)));

        DB::beginTransaction();
        try {
            foreach ($statements as $statement) {
                if ($statement === '' || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
                    continue;
                }
                DB::unprepared($statement . ';');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Database imported successfully');
    }

}
