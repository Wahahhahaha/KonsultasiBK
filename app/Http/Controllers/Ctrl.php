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

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect ('/home');
    }

//==============================================================================================

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
        $system = DB::table('system')->first();
        $level = DB::table('level')->get();
        $role = DB::table('role')->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('admin.userdata',compact('data','level','role'));
        echo view ('all.footer');
    }

    public function saveuser(Request $request){
        $rules = [
            'name' => 'required',
            'username' => 'required|unique:user,username',
            'email' => 'required|unique:student,email|unique:employer,email|unique:teacher,email',
                'phonenumber' => 'required|unique:student,phonenumber|unique:employer,phonenumber|unique:teacher,phonenumber',
                'level' => 'required',
        ];
        if ($request->level == 3) {
            $rules['role'] = 'nullable';
        } else {
            $rules['role'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below');
        }

        DB::beginTransaction();

        try {
           $userid = DB::table('user')->insertGetId([
            'username' => $request->username,
            'password' => Hash::make($request->username), // default password
            'levelid' => $request->level,
        ]);

       
        if ($request->level == 3) {
            DB::table('student')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'userid' => $userid,
            ]);
        } else if ($request->level == 1){
            DB::table('employer')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'roleid' => $request->role,
                'userid' => $userid,
            ]);
        } else {
            $teacherid = DB::table('teacher')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'roleid' => $request->role,
                'userid' => $userid,
            ]);

            if ($request->role == 3) {
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                foreach ($days as $day) {
                    $startTime = '08:00:00';
                    $endTime = in_array($day, ['saturday', 'sunday']) ? '12:00:00' : '15:00:00';
                    
                    DB::table('schedule')->insert([
                        'teacherid' => $teacherid,
                        'day_of_week' => $day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        DB::commit();

        return redirect()->back()->with('success', 'User successfully added');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add user: ' . $e->getMessage());
        }
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
            ->leftJoin('student', 'student.userid', '=', 'user.userid')
            ->leftJoin('employer', 'employer.userid', '=', 'user.userid')
            ->leftJoin('teacher', 'teacher.userid', '=', 'user.userid')

            ->select(
                'user.userid',
                'user.username',

                DB::raw('COALESCE(student.email, employer.email,teacher.email) as email'),
                DB::raw('COALESCE(student.phonenumber, employer.phonenumber,teacher.phonenumber) as phonenumber'),
                DB::raw('COALESCE(student.name, employer.name,teacher.name) as name'),
            )
            ->where('user.userid', $userid)
            ->first();

        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));  
        echo view('all.profile', compact('data'));
        echo view('all.footer');

    }

    public function updateprofile(Request $request){
        $userid = session('userid');

        DB::table('student')
            ->where('userid', $userid)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phone,
            ]);

        DB::table('employer')
            ->where('userid', $userid)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phone,
            ]);

        DB::table('teacher')
            ->where('userid', $userid)
            ->update([
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

//========================================================================================

    public function teacherlist(){
        $system = DB::table('system')->first();
        
        $teachers = DB::table('teacher')
            ->leftJoin('homeroomtc', 'homeroomtc.teacherid', '=', 'teacher.teacherid')
            ->leftJoin('counceltc', 'counceltc.teacherid', '=', 'teacher.teacherid')
            ->leftJoin('grade', 'grade.gradeid', '=', 'counceltc.gradeid')
            ->where('teacher.roleid', '3')
            ->select('teacher.*', 'grade.gradename')
            ->get();

        foreach ($teachers as $teacher) {
            $teacher->schedules = DB::table('schedule')
                ->where('teacherid', $teacher->teacherid)
                ->where('status', 1)
                ->get();
        }

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('student.teacherlist', ['data' => $teachers]);
        echo view('all.footer');
    }

    public function getAvailableTimes(Request $request) {
        $teacherid = $request->teacherid;
        $date = $request->date;
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        DB::beginTransaction();
        try {
            // Cek apakah di tabel time_slots sudah ada untuk tanggal & guru ini
            $existingSlots = DB::table('time_slots')
                ->where('teacherid', $teacherid)
                ->where('date', $date)
                ->lockForUpdate()
                ->get();

            // Jika belum ada, kita generate berdasarkan master schedule
            if ($existingSlots->isEmpty()) {
                $schedule = DB::table('schedule')
                    ->where('teacherid', $teacherid)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('status', 1)
                    ->first();

                if (!$schedule) {
                    DB::commit();
                    return response()->json(['available' => false, 'message' => 'No schedule for this day']);
                }

                $startTime = strtotime($schedule->start_time);
                $endTime = strtotime($schedule->end_time);

                while ($startTime < $endTime) {
                    $slotStart = date('H:i:s', $startTime);
                    // DURASI 30 MENIT (1800 detik)
                    $nextSlot = $startTime + 1800; 
                    if ($nextSlot > $endTime) break;
                    $slotEnd = date('H:i:s', $nextSlot);

                    DB::table('time_slots')->insert([
                        'teacherid' => $teacherid,
                        'date' => $date,
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'is_booked' => 0,
                        'created_at' => now()
                    ]);

                    $startTime = $nextSlot;
                }

                // Ambil ulang setelah di-generate
                $existingSlots = DB::table('time_slots')
                    ->where('teacherid', $teacherid)
                    ->where('date', $date)
                    ->get();
            }

            $slots = [];
            $currentTimestamp = time();
            $isToday = ($date == date('Y-m-d'));

            foreach ($existingSlots as $slot) {
                $slotStartTimestamp = strtotime($date . ' ' . $slot->start_time);
                $isPast = $isToday && ($slotStartTimestamp < $currentTimestamp);

                $slots[] = [
                    'slotid' => $slot->slotid,
                    'start' => $slot->start_time,
                    'end' => $slot->end_time,
                    'is_booked' => $slot->is_booked,
                    'is_past' => $isPast
                ];
            }
            
            DB::commit();
            return response()->json(['available' => true, 'slots' => $slots]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['available' => false, 'message' => $e->getMessage()]);
        }
    }

    public function bookConsult(Request $request) {
        $userid = session('userid');
        $student = DB::table('student')->where('userid', $userid)->first();

        if (!$student) {
            return back()->with('error', 'Student data not found');
        }

        $slotid = $request->slotid;
        
        DB::beginTransaction();
        try {
            // 1. Validasi: Siswa hanya boleh memiliki 1 booking aktif (status pending atau active)
            $activeBooking = DB::table('consult')
                ->where('studentid', $student->studentid)
                ->whereIn('status', ['pending', 'active'])
                ->exists();

            if ($activeBooking) {
                DB::rollBack();
                return back()->with('error', 'You already have an active consultation. Please finish or cancel it before booking a new one.');
            }

            // 2. Cek kembali ketersediaan slot
            $slot = DB::table('time_slots')->where('slotid', $slotid)->lockForUpdate()->first();
            
            if (!$slot || $slot->is_booked) {
                DB::rollBack();
                return back()->with('error', 'Slot is already booked or not found');
            }

            // 3. Simpan ke tabel consult
            DB::table('consult')->insert([
                'studentid' => $student->studentid,
                'slotid' => $slotid,
                'problem' => $request->problem,
                'status' => 'pending', // Default status
                'created_at' => now()
            ]);

            // 4. Update status di time_slots
            DB::table('time_slots')->where('slotid', $slotid)->update([
                'is_booked' => 1
            ]);

            DB::commit();
            return back()->with('success', 'Booking successful!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to book: ' . $e->getMessage());
        }
    }

//=======================================================================================
    public function chat(Request $request) {
        $system = DB::table('system')->first();
        $userid = session('userid');
        $level = session('level');

        $query = DB::table('consult')
            ->join('time_slots', 'time_slots.slotid', '=', 'consult.slotid')
            ->join('teacher', 'teacher.teacherid', '=', 'time_slots.teacherid')
            ->join('student', 'student.studentid', '=', 'consult.studentid');

        if ($level == 3) { // Student
            $student = DB::table('student')->where('userid', $userid)->first();
            $query->where('consult.studentid', $student->studentid);
        } else if ($level == 2) { // Teacher
            $teacher = DB::table('teacher')->where('userid', $userid)->first();
            $query->where('time_slots.teacherid', $teacher->teacherid);
        }

        $consults = $query->select(
            'consult.*',
            'teacher.name as teacher_name',
            'student.name as student_name',
            'time_slots.date',
            'time_slots.start_time',
            'time_slots.end_time'
        )->orderBy('consult.created_at', 'desc')->get();

        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('student.chat', compact('consults'));
        echo view('all.footer');
    }

    public function getMessages($id) {
        $messages = DB::table('consul_message')
            ->join('user', 'user.userid', '=', 'consul_message.userid')
            ->leftJoin('student', 'student.userid', '=', 'user.userid')
            ->leftJoin('teacher', 'teacher.userid', '=', 'user.userid')
            ->leftJoin('employer', 'employer.userid', '=', 'user.userid')
            ->where('consul_message.consultid', $id)
            ->select(
                'consul_message.*',
                DB::raw('COALESCE(student.name, teacher.name, employer.name) as sender_name'),
                'user.levelid as sender_level'
            )
            ->orderBy('consul_message.created_at', 'asc')
            ->get();

        $status = DB::table('consult')->where('consultid', $id)->value('status');

        return response()->json([
            'messages' => $messages,
            'status' => $status
        ]);
    }

    public function sendMessage(Request $request) {
        $userid = session('userid');
        $file_path = null;

        if ($request->hasFile('file')) {
            $file_path = $request->file('file')->store('chat_files', 'public');
        }

        DB::table('consul_message')->insert([
            'consultid' => $request->consultid,
            'userid' => $userid,
            'message' => $request->message,
            'file' => $file_path,
            'created_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function endConsultation(Request $request) {
        $userid = session('userid');
        $level = session('level');
        $consultid = $request->consultid;

        $consult = DB::table('consult')->where('consultid', $consultid)->first();

        if ($level == 3) {
            DB::table('consult')->where('consultid', $consultid)->update(['student_agree_end' => true]);
        } else {
            DB::table('consult')->where('consultid', $consultid)->update(['teacher_agree_end' => true]);
        }

        $updatedConsult = DB::table('consult')->where('consultid', $consultid)->first();

        if ($updatedConsult->student_agree_end && $updatedConsult->teacher_agree_end) {
            DB::table('consult')->where('consultid', $consultid)->update(['status' => 'completed']);
            return response()->json(['success' => true, 'completed' => true]);
        }

        return response()->json(['success' => true, 'completed' => false]);
    }

    public function cancelConsultation(Request $request) {
        $consultid = $request->consultid;
        
        DB::beginTransaction();
        try {
            $consult = DB::table('consult')->where('consultid', $consultid)->first();
            
            DB::table('consult')->where('consultid', $consultid)->update(['status' => 'cancelled']);
            
            // Release the slot
            DB::table('time_slots')->where('slotid', $consult->slotid)->update(['is_booked' => 0]);
            
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function approveConsultation(Request $request) {
        DB::table('consult')->where('consultid', $request->consultid)->update([
            'status' => 'active',
            'updated_at' => now()
        ]);
        return response()->json(['success' => true]);
    }

    public function rejectConsultation(Request $request) {
        $consultid = $request->consultid;
        DB::beginTransaction();
        try {
            $consult = DB::table('consult')->where('consultid', $consultid)->first();
            DB::table('consult')->where('consultid', $consultid)->update(['status' => 'cancelled']);
            // Release the slot
            DB::table('time_slots')->where('slotid', $consult->slotid)->update(['is_booked' => 0]);
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

}
