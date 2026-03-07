<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;


class Ctrl extends Controller
{
    private function logActivity(Request $request, string $action, ?int $userid = null, ?float $lat = null, ?float $lng = null, ?string $details = null) {
        DB::statement('CREATE TABLE IF NOT EXISTS activity_logs (
            logid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NULL,
            username VARCHAR(255) NULL,
            actor_label VARCHAR(255) NULL,
            action VARCHAR(255) NOT NULL,
            ip_address VARCHAR(64) NULL,
            latitude DOUBLE NULL,
            longitude DOUBLE NULL,
            details TEXT NULL,
            created_at DATETIME NOT NULL
        )');
        try { DB::statement('ALTER TABLE activity_logs ADD COLUMN details TEXT NULL'); } catch (\Exception $e) { /* ignore */ }
        $uid = $userid ?? session('userid');
        $user = null; $username = null; $label = null;
        if ($uid) {
            $user = DB::table('user')->where('userid', $uid)->first();
            if ($user) {
                $username = $user->username;
                if ($user->levelid == 3) {
                    $label = 'student';
                } elseif ($user->levelid == 1 || $user->levelid == 2) {
                    $role = session('role');
                    if ($role) {
                        $roleKey = strtolower(str_replace(' ', '', $role));
                        $label = $roleKey; // superadmin/admin/counsellingteacher/homeroomteacher
                    } else {
                        // Fallback: fetch from tables
                        $roleid = DB::table('employer')->where('userid',$uid)->value('roleid')
                                  ?? DB::table('teacher')->where('userid',$uid)->value('roleid');
                        $label = DB::table('role')->where('roleid',$roleid)->value('rolename') ?? 'employer_teacher';
                    }
                } else {
                    $label = 'guest';
                }
            }
        } else {
            $label = 'guest';
        }
        $latFinal = $lat ?? ($request->input('latitude') ?: session('latitude'));
        $lngFinal = $lng ?? ($request->input('longitude') ?: session('longitude'));
        DB::table('activity_logs')->insert([
            'userid' => $uid,
            'username' => $username,
            'actor_label' => $label,
            'action' => $action,
            'ip_address' => $request->ip(),
            'latitude' => $latFinal,
            'longitude' => $lngFinal,
            'details' => $details,
            'created_at' => now()
        ]);
        if (preg_match('/^(update_|delete_)/', $action)) {
            $msg = "**Action:** {$action}\n**User:** " . ($username ?? '-') . " (" . ($label ?? '-') . ")\n**IP:** " . ($request->ip() ?? '-') . "\n**Time:** " . now() . "\n**Details:** " . ($details ?? '-');
            $this->sendDiscordWebhook($msg);
        }
    }
    private function pushNotification(int $userid, string $title, string $body) {
        DB::statement('CREATE TABLE IF NOT EXISTS notifications (
            notificationid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            body TEXT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )');
        DB::table('notifications')->insert([
            'userid' => $userid,
            'title' => $title,
            'body' => $body,
            'is_read' => 0,
            'created_at' => now()
        ]);
    }
    private function saveTrash(string $entityType, int $entityId, string $action, $snapshot, Request $request = null, string $details = null) {
        DB::statement('CREATE TABLE IF NOT EXISTS trash_bin (
            trashid INT AUTO_INCREMENT PRIMARY KEY,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            action VARCHAR(20) NOT NULL,
            snapshot LONGTEXT NOT NULL,
            created_by INT NULL,
            actor_username VARCHAR(255) NULL,
            actor_label VARCHAR(255) NULL,
            actor_level INT NULL,
            ip_address VARCHAR(64) NULL,
            details TEXT NULL,
            created_at DATETIME NOT NULL
        )');
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_username VARCHAR(255) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_label VARCHAR(255) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_level INT NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN ip_address VARCHAR(64) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN details TEXT NULL'); } catch (\Exception $e) {}
        $uid = session('userid');
        $username = null; $label = null; $level = null;
        if ($uid) {
            $u = DB::table('user')->where('userid',$uid)->first();
            if ($u) {
                $username = $u->username; $level = $u->levelid;
                if ($u->levelid == 3) {
                    $label = 'student';
                } elseif ($u->levelid == 1 || $u->levelid == 2) {
                    $role = session('role');
                    if ($role) {
                        $label = strtolower(str_replace(' ', '', $role));
                    } else {
                        $rid = DB::table('employer')->where('userid',$uid)->value('roleid')
                               ?? DB::table('teacher')->where('userid',$uid)->value('roleid');
                        $label = DB::table('role')->where('roleid',$rid)->value('rolename') ?? 'staff';
                    }
                } else {
                    $label = 'guest';
                }
            }
        }
        $ip = $request ? $request->ip() : null;
        DB::table('trash_bin')->insert([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'snapshot' => json_encode($snapshot),
            'created_by' => session('userid'),
            'actor_username' => $username,
            'actor_label' => $label,
            'actor_level' => $level,
            'ip_address' => $ip,
            'details' => $details,
            'created_at' => now()
        ]);
    }
    private function sendDiscordWebhook(string $message) {
        $url = env('DISCORD_WEBHOOK_URL');
        if (!$url) return;
        try {
            Http::post($url, ['content' => $message]);
        } catch (\Exception $e) {}
    }

    public function notfound(){
        return response()->view('all.error', [], 404);
    }

//==========================================================================================

    public function login(Request $request){
        $system = DB::table('system')->first();
        
        $ipKey = 'login_attempts:' . $request->ip();
        $attempts = RateLimiter::attempts($ipKey);
        $showCaptcha = $attempts >= 3;
        
        $mathProblem = null;
        if ($showCaptcha) {
            $n1 = rand(1, 10);
            $n2 = rand(1, 10);
            $mathProblem = "$n1 + $n2 = ?";
            session(['math_solution' => $n1 + $n2]);
        }

        echo view ('all.header',compact('system'));
        echo view ('all.login',compact('system', 'showCaptcha', 'mathProblem'));
        echo view ('all.footer');
    }

    public function loginact(Request $request){
        $ipKey = 'login_attempts:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($ipKey, 3)) {
            // Captcha validation required
            $recaptcha = $request->input('g-recaptcha-response');
            $mathAnswer = $request->input('math_answer');
            $isValid = false;

            if ($recaptcha) {
                // Verify online
                try {
                    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => env('RECAPTCHA_SECRET_KEY'),
                        'response' => $recaptcha,
                        'remoteip' => $request->ip()
                    ]);
                    $isValid = $response->json()['success'] ?? false;
                } catch (\Exception $e) {
                    // If offline or error, fallback to math check logic should be prioritized in frontend
                    // but if they sent recaptcha, it means they thought they were online.
                    // If verify fails, isValid remains false.
                }
            } elseif ($mathAnswer !== null) {
                // Verify offline
                $isValid = (int)$mathAnswer === (int)session('math_solution');
            }

            if (!$isValid) {
                RateLimiter::hit($ipKey);
                return back()->with('error', 'CAPTCHA verification failed. Please try again.');
            }
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::table('user')
            ->where('username', $request->username)
            ->first();

        if (!$user) {
            RateLimiter::hit($ipKey);
            return back()->with('error', 'Username not found!');
        }

        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($ipKey);
            return back()->with('error', 'Password wrong');
        }

        // Login success, clear attempts
        RateLimiter::clear($ipKey);
        Session::forget('math_solution');

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

        $this->logActivity($request, 'login', $user->userid, null, null, 'username=' . $user->username);

        return redirect('/home')->with('success', 'Login successful');
    }

    public function logout(Request $request){
        $userid = $request->session()->get('userid');
        $this->logActivity($request, 'logout', $userid);
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
        $countUsers = DB::table('user')->count();
        $countStudents = DB::table('student')->count();
        $countTeachers = DB::table('teacher')->count();
        $countConsults = DB::table('consult')->whereIn('status',['active','completed'])->count();
        $teachers = DB::table('teacher')
            ->leftJoin('homeroomtc', 'homeroomtc.teacherid', '=', 'teacher.teacherid')
            ->leftJoin('counceltc', 'counceltc.teacherid', '=', 'teacher.teacherid')
            ->leftJoin('grade', 'grade.gradeid', '=', 'counceltc.gradeid')
            ->where('teacher.roleid', '3')
            ->select('teacher.*', 'grade.gradename')
            ->limit(8)
            ->get();
        foreach ($teachers as $t) {
            $t->schedules = DB::table('schedule')
                ->where('teacherid', $t->teacherid)
                ->where('status', 1)
                ->get();
        }
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('all.home',compact('system','countUsers','countStudents','countTeachers','countConsults','teachers'));
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
            ->leftJoin('class', 'class.classid', '=', 'student.classid')
            ->leftJoin('major', 'major.majorid', '=', 'class.majorid')
            ->leftJoin('grade', 'grade.gradeid', '=', 'class.gradeid')
            ->select(
                'user.userid',
                'user.username',
                'level.levelid',
                'level.levelname',
                'role.rolename',
                'class.classname',
                'level.levelname',
                'major.majorname',
                'grade.gradename',

                DB::raw('COALESCE(teacher.email, employer.email,student.email) as email'),
                DB::raw('COALESCE(teacher.phonenumber, employer.phonenumber,student.phonenumber) as phonenumber'),
                DB::raw('COALESCE(teacher.name, employer.name,student.name) as name'),
            )
            ->get();
        $system = DB::table('system')->first();
        $level = DB::table('level')->get();
        $role = DB::table('role')->get();
        $classes = DB::table('class')
            ->leftJoin('grade', 'grade.gradeid', '=', 'class.gradeid')
            ->leftJoin('major', 'major.majorid', '=', 'class.majorid')
            ->select('class.classid', 'class.classname', 'grade.gradename', 'major.majorname')
            ->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu', compact('system'));
        echo view ('admin.userdata',compact('data','level','role','classes'));
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
            $rules['classid'] = 'required|exists:class,classid';
            // Re-validate with class rule when level is student
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please fix the errors below');
            }
            DB::table('student')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'classid' => $request->classid,
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

        $levelMap = [1 => 'employer', 2 => 'teacher', 3 => 'student'];
        $details = 'username=' . $request->username . '; level=' . ($levelMap[$request->level] ?? (string)$request->level) . '; role=' . ($request->role ?? '');
        $this->logActivity($request, 'add_user', $userid, null, null, $details);

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

        $levelLabelPre = $user ? ($user->levelid == 3 ? 'student' : ($user->levelid == 1 ? 'employer' : 'teacher')) : '';
        $trashDetails = 'deleted user id ' . $id . '; username=' . ($user->username ?? '') . '; role=' . $levelLabelPre;
        $snapshot = [
            'user' => $user,
            'student' => $student,
            'employer' => $employer,
            'teacher' => $teacher
        ];
        $this->saveTrash('user', $id, 'delete', $snapshot, request(), $trashDetails);

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

        $levelLabel = $user ? ($user->levelid == 3 ? 'student' : ($user->levelid == 1 ? 'employer' : 'teacher')) : '';
        $details = 'userid=' . $id . '; username=' . ($user->username ?? '') . '; level=' . $levelLabel;
        $this->logActivity(request(), 'delete_user', null, null, null, $details);

        return back();
    }

    public function exportUsers() {
        $rows = DB::table('user')
            ->leftjoin('level','level.levelid','=','user.levelid')
            ->leftjoin('employer','employer.userid','=','user.userid')
            ->leftjoin('teacher','teacher.userid','=','user.userid')
            ->leftjoin('student','student.userid','=','user.userid')
            ->leftJoin('role', function($join) {
                $join->on('role.roleid', '=', 'employer.roleid')
                ->orOn('role.roleid', '=', 'teacher.roleid');
            })
            ->leftJoin('class', 'class.classid', '=', 'student.classid')
            ->leftJoin('grade', 'grade.gradeid', '=', 'class.gradeid')
            ->leftJoin('major', 'major.majorid', '=', 'class.majorid')
            ->select(
                'user.userid',
                'user.username',
                DB::raw('COALESCE(teacher.name, employer.name,student.name) as name'),
                'user.password',
                DB::raw('COALESCE(teacher.email, employer.email,student.email) as email'),
                DB::raw('COALESCE(teacher.phonenumber, employer.phonenumber,student.phonenumber) as phonenumber'),
                'level.levelname as level',
                'role.rolename as role',
                'class.classname',
                'grade.gradename',
                'major.majorname'
            )
            ->orderBy('user.userid', 'asc')
            ->get();
            
        $data = [];
        $data[] = ['UserID','Username','Name','Password','Email','Phonenumber','Level','Role','Class','Grade','Major'];
        foreach ($rows as $r) {
            $data[] = [
                (string)$r->userid,
                (string)$r->username,
                (string)$r->name,
                (string)$r->password,
                (string)$r->email,
                (string)$r->phonenumber,
                (string)$r->level,
                (string)$r->role,
                (string)($r->classname ?? ''),
                (string)($r->gradename ?? ''),
                (string)($r->majorname ?? '')
            ];
        }
        $bin = \App\Libraries\ExportXlsx::generate($data, 'Users');
        return response($bin, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="users.xlsx"'
        ]);
    }

    public function importUsers(Request $request) {
        if (!$request->hasFile('file')) {
            return back()->with('error', 'Please upload a file');
        }
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $imported = 0;
        $errors = [];
        
        try {
            if ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                if (!$handle) {
                    return back()->with('error', 'Cannot open CSV file');
                }
                // Detect delimiter from first non-empty line
                $firstLine = '';
                $pos = ftell($handle);
                while (($firstLine = fgets($handle)) !== false) {
                    if (trim($firstLine) !== '') break;
                }
                if ($firstLine === '' || $firstLine === false) {
                    fclose($handle);
                    return back()->with('error', 'CSV file is empty');
                }
                $delims = [',', ';', "\t"];
                $bestDelim = ',';
                $bestCount = 1;
                foreach ($delims as $d) {
                    $parts = str_getcsv($firstLine, $d);
                    if (count($parts) > $bestCount) {
                        $bestCount = count($parts);
                        $bestDelim = $d;
                    }
                }
                // Rewind and read header using detected delimiter
                rewind($handle);
                $header = fgetcsv($handle, 0, $bestDelim);
                if (!$header || count($header) < 2) {
                    fclose($handle);
                    return back()->with('error', 'CSV header invalid or single-column. Use comma/semicolon/tab delimiter.');
                }
                // Strip BOM
                if (isset($header[0])) {
                    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                }
                // Normalize header keys to lowercase and trim
                $header = array_map(function($h) {
                    return strtolower(trim($h));
                }, $header);
                
                $rowNumber = 2; // Start from 2 (after header)
                while (($row = fgetcsv($handle, 0, $bestDelim)) !== false) {
                    // Skip empty lines
                    $joined = implode('', array_map('trim', $row));
                    if ($joined === '') { $rowNumber++; continue; }
                    // Adjust row length to header length
                    if (count($row) < count($header)) {
                        $row = array_pad($row, count($header), '');
                    } elseif (count($row) > count($header)) {
                        $row = array_slice($row, 0, count($header));
                    }
                    $data = [];
                    for ($i=0; $i<count($header); $i++) {
                        $data[$header[$i]] = isset($row[$i]) ? trim($row[$i]) : '';
                    }
                    $this->importUserRow($data, $errors, $imported, $rowNumber);
                    $rowNumber++;
                }
                fclose($handle);
            } else if ($ext === 'xls') {
                $content = file_get_contents($file->getRealPath());
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($content);
                libxml_clear_errors();
                $rows = $dom->getElementsByTagName('tr');
                $header = [];
                $headerSet = false;
                $rowNumber = 2;
                
                foreach ($rows as $tr) {
                    $tdCells = [];
                    foreach ($tr->getElementsByTagName('td') as $td) {
                        $tdCells[] = trim($td->textContent);
                    }
                    $thCells = [];
                    foreach ($tr->getElementsByTagName('th') as $th) {
                        $thCells[] = trim($th->textContent);
                    }
                    
                    // Determine header using the row with the most cells (th preferred)
                    if (!$headerSet) {
                        $candidate = (count($thCells) > count($tdCells)) ? $thCells : $tdCells;
                        if (count($candidate) >= 2) {
                            $header = array_map(function($h) {
                                return strtolower(trim($h));
                            }, $candidate);
                            $headerSet = true;
                            // Skip processing this header row
                            continue;
                        } else {
                            // Not a valid header row, continue
                            continue;
                        }
                    }
                    
                    // Use td cells for data
                    $cells = $tdCells;
                    // Skip rows without data
                    $joined = implode('', array_map('trim', $cells));
                    if ($joined === '') { $rowNumber++; continue; }
                    
                    // Align cells to header length
                    if (count($cells) < count($header)) {
                        $cells = array_pad($cells, count($header), '');
                    } elseif (count($cells) > count($header)) {
                        $cells = array_slice($cells, 0, count($header));
                    }
                    $data = array_combine($header, $cells);
                    $this->importUserRow($data, $errors, $imported, $rowNumber);
                    $rowNumber++;
                }
            } else if ($ext === 'xlsx') {
                // Minimal XLSX reader: parse sheet1 and sharedStrings
                $zip = new \ZipArchive();
                if ($zip->open($file->getRealPath()) !== true) {
                    return back()->with('error', 'Cannot open XLSX file');
                }
                // Load shared strings
                $sharedStrings = [];
                $ssIndex = $zip->locateName('xl/sharedStrings.xml');
                if ($ssIndex !== false) {
                    $ssXml = $zip->getFromIndex($ssIndex);
                    if ($ssXml) {
                        $ssDoc = simplexml_load_string($ssXml);
                        if ($ssDoc && isset($ssDoc->si)) {
                            foreach ($ssDoc->si as $si) {
                                // support t or rich text (r)
                                if (isset($si->t)) {
                                    $sharedStrings[] = (string)$si->t;
                                } else if (isset($si->r)) {
                                    $str = '';
                                    foreach ($si->r as $r) {
                                        $str .= (string)$r->t;
                                    }
                                    $sharedStrings[] = $str;
                                } else {
                                    $sharedStrings[] = '';
                                }
                            }
                        }
                    }
                }
                // Load sheet1
                $sheetXml = null;
                $sheetPaths = ['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet01.xml'];
                foreach ($sheetPaths as $sp) {
                    $idx = $zip->locateName($sp);
                    if ($idx !== false) {
                        $sheetXml = $zip->getFromIndex($idx);
                        break;
                    }
                }
                if (!$sheetXml) {
                    $zip->close();
                    return back()->with('error', 'XLSX missing sheet1.xml');
                }
                $zip->close();
                $sheet = simplexml_load_string($sheetXml);
                if (!$sheet) {
                    return back()->with('error', 'Invalid XLSX sheet content');
                }
                // Helper to convert column letters to index
                $colToIndex = function($colRef) {
                    $col = preg_replace('/\d+/', '', $colRef); // remove row number
                    $len = strlen($col);
                    $num = 0;
                    for ($i = 0; $i < $len; $i++) {
                        $num = $num * 26 + (ord($col[$i]) - ord('A') + 1);
                    }
                    return $num - 1;
                };
                $header = [];
                $headerSet = false;
                $rowNumber = 2;
                if (isset($sheet->sheetData->row)) {
                    foreach ($sheet->sheetData->row as $row) {
                        $cells = [];
                        $maxIndex = 0;
                        foreach ($row->c as $c) {
                            $r = (string)$c['r']; // e.g., A1
                            $idx = $colToIndex($r);
                            $maxIndex = max($maxIndex, $idx);
                            $t = (string)$c['t'];
                            $v = '';
                            if (isset($c->v)) {
                                $val = (string)$c->v;
                                if ($t === 's') {
                                    $ssIdx = intval($val);
                                    $v = isset($sharedStrings[$ssIdx]) ? $sharedStrings[$ssIdx] : '';
                                } else {
                                    $v = $val;
                                }
                            } elseif (isset($c->is->t)) {
                                $v = (string)$c->is->t;
                            } else {
                                $v = '';
                            }
                            $cells[$idx] = trim($v);
                        }
                        // Normalize to sequential array from 0..maxIndex
                        $rowArr = [];
                        for ($i=0; $i<=$maxIndex; $i++) {
                            $rowArr[$i] = isset($cells[$i]) ? $cells[$i] : '';
                        }
                        if (!$headerSet) {
                            if (count($rowArr) >= 2) {
                                $header = array_map(function($h){ return strtolower(trim($h)); }, $rowArr);
                                $headerSet = true;
                                continue;
                            } else {
                                continue;
                            }
                        }
                        // Skip empty
                        $joined = implode('', array_map('trim', $rowArr));
                        if ($joined === '') { $rowNumber++; continue; }
                        // Align to header
                        if (count($rowArr) < count($header)) {
                            $rowArr = array_pad($rowArr, count($header), '');
                        } elseif (count($rowArr) > count($header)) {
                            $rowArr = array_slice($rowArr, 0, count($header));
                        }
                        $data = array_combine($header, $rowArr);
                        $this->importUserRow($data, $errors, $imported, $rowNumber);
                        $rowNumber++;
                    }
                }
            } else {
                return back()->with('error', 'Unsupported file type. Use CSV or Excel .xls');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Import error: '.$e->getMessage());
        }
        
        // Build detailed message
        $msg = "Import completed: {$imported} users processed successfully";
        if (!empty($errors)) {
            $msg .= ". " . count($errors) . " errors encountered:";
            // Show first 5 errors to avoid message too long
            $errorCount = 0;
            foreach ($errors as $error) {
                if ($errorCount >= 5) break;
                $msg .= "<br>- " . htmlspecialchars($error);
                $errorCount++;
            }
            if (count($errors) > 5) {
                $msg .= "<br>- ... and " . (count($errors) - 5) . " more errors";
            }
        }
        
        return back()->with('success', $msg);
    }

    private function importUserRow($data, &$errors, &$imported, $rowNumber = null) {
        $userid = isset($data['userid']) && is_numeric($data['userid']) ? $data['userid'] : null;
        $username = trim($data['username'] ?? '');
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phonenumber'] ?? '');
        $levelName = strtolower(trim($data['level'] ?? ''));
        $roleName = trim($data['role'] ?? '');
        $password = trim($data['password'] ?? ''); // Raw password from file
        
        $className = trim($data['class'] ?? ''); // column header 'Class' mapped to key 'class'
        $gradeName = trim($data['grade'] ?? '');
        $majorName = trim($data['major'] ?? '');

        // Basic validation
        $rowPrefix = $rowNumber ? "Row {$rowNumber}: " : "";
        if (empty($username) || empty($name) || empty($email) || empty($phone) || empty($levelName)) {
            $errors[] = $rowPrefix . "Missing required fields for user: {$username}";
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $rowPrefix . "Invalid email format for user: {$username}";
            return;
        }

        $levelMap = ['employer' => 1, 'teacher' => 2, 'student' => 3];
        $levelid = $levelMap[$levelName] ?? null;
        
        if (!$levelid) {
            // Try numeric
            if (in_array($levelName, [1, 2, 3])) {
                $levelid = (int)$levelName;
            } else {
                $errors[] = $rowPrefix . "Invalid level '{$levelName}' for user: {$username}";
                return; 
            }
        }

        // Determine Role ID
        $roleid = null;
        if ($levelid == 1 || $levelid == 2) {
            if (!empty($roleName)) {
                $roleid = DB::table('role')->where('rolename', $roleName)->value('roleid');
            }
            if (!$roleid) {
                // Default fallback
                if ($levelid == 1) $roleid = 2; // Admin
                if ($levelid == 2) $roleid = 3; // Teacher
            }
        }

        // Determine Grade/Major/Class (create if not exists)
        $classid = null;
        if ($levelid == 3) {
            // Resolve Grade
            $gradeid = null;
            if (!empty($gradeName)) {
                $grade = DB::table('grade')->where('gradename', $gradeName)->first();
                if (!$grade) {
                    $gradeid = DB::table('grade')->insertGetId(['gradename' => $gradeName]);
                } else {
                    $gradeid = $grade->gradeid;
                }
            }
            // Resolve Major
            $majorid = null;
            if (!empty($majorName)) {
                $major = DB::table('major')->where('majorname', $majorName)->first();
                if (!$major) {
                    $majorid = DB::table('major')->insertGetId(['majorname' => $majorName]);
                } else {
                    $majorid = $major->majorid;
                }
            }
            // Try find class by classname (+ optional grade/major filters)
            if (!empty($className)) {
                $cls = DB::table('class')->where('classname', $className);
                if ($gradeid) $cls->where('gradeid', $gradeid);
                if ($majorid) $cls->where('majorid', $majorid);
                $classObj = $cls->first();
                if ($classObj) {
                    $classid = $classObj->classid;
                } else {
                    // Create class using provided name with resolved grade/major
                    $classid = DB::table('class')->insertGetId([
                        'classname' => $className,
                        'gradeid' => $gradeid ?? 1,
                        'majorid' => $majorid ?? null
                    ]);
                }
            } else if ($gradeid || $majorid) {
                // Find any class by grade+major
                $cls = DB::table('class');
                if ($gradeid) $cls->where('gradeid', $gradeid);
                if ($majorid) $cls->where('majorid', $majorid);
                $classObj = $cls->first();
                if ($classObj) {
                    $classid = $classObj->classid;
                } else if ($gradeid && $majorid) {
                    // Create default class name from Grade+Major
                    $defaultName = $gradeName . ' - ' . $majorName;
                    $classid = DB::table('class')->insertGetId([
                        'classname' => $defaultName,
                        'gradeid' => $gradeid,
                        'majorid' => $majorid
                    ]);
                } else {
                    $classid = 1;
                }
            } else {
                $classid = 1;
            }
        }

        DB::beginTransaction();
        try {
            // UPDATE logic - if userid provided and exists
            if ($userid) {
                $existingUser = DB::table('user')->where('userid', $userid)->first();
                
                if ($existingUser) {
                    // Check for username conflict with other users
                    $usernameConflict = DB::table('user')
                        ->where('username', $username)
                        ->where('userid', '!=', $userid)
                        ->exists();
                    
                    if ($usernameConflict) {
                        $errors[] = $rowPrefix . "Username '{$username}' already exists for another user";
                        DB::rollback();
                        return;
                    }

                    // Update user table
                    $updateData = [
                        'username' => $username,
                        'levelid' => $levelid
                    ];
                    if (!empty($password)) {
                        $updateData['password'] = Hash::make($password);
                    }
                    
                    DB::table('user')->where('userid', $userid)->update($updateData);
                    
                    // Update or create profile tables based on new level
                    $profileData = [
                        'name' => $name,
                        'email' => $email,
                        'phonenumber' => $phone
                    ];
                    
                    // Handle level change - delete old profile and create new one
                    if ($existingUser->levelid != $levelid) {
                        // Delete old profile
                        if ($existingUser->levelid == 1) {
                            DB::table('employer')->where('userid', $userid)->delete();
                        } elseif ($existingUser->levelid == 2) {
                            DB::table('teacher')->where('userid', $userid)->delete();
                        } elseif ($existingUser->levelid == 3) {
                            DB::table('student')->where('userid', $userid)->delete();
                        }
                        
                        // Create new profile
                        if ($levelid == 1) { // Employer
                            $profileData['roleid'] = $roleid;
                            DB::table('employer')->insert(array_merge($profileData, ['userid' => $userid]));
                        } elseif ($levelid == 2) { // Teacher
                            $profileData['roleid'] = $roleid;
                            DB::table('teacher')->insert(array_merge($profileData, ['userid' => $userid]));
                        } elseif ($levelid == 3) { // Student
                            if ($classid) $profileData['classid'] = $classid;
                            DB::table('student')->insert(array_merge($profileData, ['userid' => $userid]));
                        }
                    } else {
                        // Update existing profile
                        if ($levelid == 1) { // Employer
                            $profileData['roleid'] = $roleid;
                            DB::table('employer')->where('userid', $userid)->update($profileData);
                        } elseif ($levelid == 2) { // Teacher
                            $profileData['roleid'] = $roleid;
                            DB::table('teacher')->where('userid', $userid)->update($profileData);
                        } elseif ($levelid == 3) { // Student
                            if ($classid) $profileData['classid'] = $classid;
                            DB::table('student')->where('userid', $userid)->update($profileData);
                        }
                    }
                    
                    $imported++;
                    DB::commit();
                    return;
                }
            }
            
            // INSERT logic (if no userid or userid not found)
            // Check username conflict for new user
            if (DB::table('user')->where('username', $username)->exists()) {
                $errors[] = $rowPrefix . "Username '{$username}' already exists";
                DB::rollback();
                return;
            }

            // Check email conflict
            if (DB::table('student')->where('email', $email)->exists() ||
                DB::table('employer')->where('email', $email)->exists() ||
                DB::table('teacher')->where('email', $email)->exists()) {
                $errors[] = $rowPrefix . "Email '{$email}' already exists";
                DB::rollback();
                return;
            }

            // Check phone conflict
            if (DB::table('student')->where('phonenumber', $phone)->exists() ||
                DB::table('employer')->where('phonenumber', $phone)->exists() ||
                DB::table('teacher')->where('phonenumber', $phone)->exists()) {
                $errors[] = $rowPrefix . "Phone '{$phone}' already exists";
                DB::rollback();
                return;
            }

            // Create new user
            // Use provided password or default username
            $finalPassword = !empty($password) ? $password : $username;
            
            $newUserid = DB::table('user')->insertGetId([
                'username' => $username,
                'password' => Hash::make($finalPassword),
                'levelid' => $levelid,
                'verified_at' => now()
            ]);

            if ($levelid == 1) {
                DB::table('employer')->insert([
                    'userid' => $newUserid,
                    'name' => $name,
                    'email' => $email,
                    'phonenumber' => $phone,
                    'roleid' => $roleid
                ]);
            } elseif ($levelid == 2) {
                $teacherid = DB::table('teacher')->insertGetId([
                    'userid' => $newUserid,
                    'name' => $name,
                    'email' => $email,
                    'phonenumber' => $phone,
                    'roleid' => $roleid
                ]);
                
                // Add default schedule for counseling teacher
                if ($roleid == 3) {
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    foreach ($days as $day) {
                        $end = in_array($day, ['saturday', 'sunday']) ? '12:00:00' : '15:00:00';
                        DB::table('schedule')->insert([
                            'teacherid' => $teacherid,
                            'day_of_week' => $day,
                            'start_time' => '08:00:00',
                            'end_time' => $end,
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            } elseif ($levelid == 3) {
                DB::table('student')->insert([
                    'userid' => $newUserid,
                    'name' => $name,
                    'email' => $email,
                    'phonenumber' => $phone,
                    'classid' => $classid ?? 1
                ]);
            }
            
            $imported++;
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            $errors[] = $rowPrefix . "Database error for user {$username}: " . $e->getMessage();
        }
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
    public function classdata(Request $request){
        $system = DB::table('system')->first();
        
        if ($request->ajax()) {
            $classes = DB::table('class')
                ->leftJoin('grade', 'grade.gradeid', '=', 'class.gradeid')
                ->leftJoin('major', 'major.majorid', '=', 'class.majorid')
                ->select(
                    'class.classid', 
                    'class.classname', 
                    'class.gradeid',
                    'class.majorid',
                    'grade.gradename', 
                    'major.majorname'
                )
                ->get();
            return response()->json(['data' => $classes]);
        }
            
        $grades = DB::table('grade')->get();
        $majors = DB::table('major')->get();

        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));
        echo view ('admin.classdata', compact('grades', 'majors'));
        echo view ('all.footer');
    }

    public function addClass(Request $request) {
        $request->validate([
            'classname' => 'required',
            'gradeid' => 'required|exists:grade,gradeid',
            'majorid' => 'required|exists:major,majorid'
        ]);

        DB::table('class')->insert([
            'classname' => $request->classname,
            'gradeid' => $request->gradeid,
            'majorid' => $request->majorid
        ]);

        $gname = DB::table('grade')->where('gradeid', $request->gradeid)->value('gradename');
        $mname = DB::table('major')->where('majorid', $request->majorid)->value('majorname');
        $details = 'classname=' . $request->classname . '; grade=' . ($gname ?? $request->gradeid) . '; major=' . ($mname ?? $request->majorid);
        $this->logActivity($request, 'add_class', null, null, null, $details);

        return response()->json(['success' => true, 'message' => 'Class added successfully']);
    }

    public function updateClass(Request $request) {
        $request->validate([
            'classid' => 'required|exists:class,classid',
            'classname' => 'required',
            'gradeid' => 'required|exists:grade,gradeid',
            'majorid' => 'required|exists:major,majorid'
        ]);

        $old = DB::table('class')->where('classid', $request->classid)->first();
        if ($old) {
            $oldGradeName = DB::table('grade')->where('gradeid', $old->gradeid)->value('gradename');
            $oldMajorName = DB::table('major')->where('majorid', $old->majorid)->value('majorname');
            $newGradeName = DB::table('grade')->where('gradeid', $request->gradeid)->value('gradename');
            $newMajorName = DB::table('major')->where('majorid', $request->majorid)->value('majorname');
            $trashDetails = 'edited class id ' . $request->classid
                . ' from classname ' . ($old->classname ?? '')
                . ' to ' . $request->classname
                . '; grade ' . ($oldGradeName ?? $old->gradeid ?? '')
                . ' -> ' . ($newGradeName ?? $request->gradeid)
                . '; major ' . ($oldMajorName ?? $old->majorid ?? '')
                . ' -> ' . ($newMajorName ?? $request->majorid);
            $this->saveTrash('class', $request->classid, 'update', $old, $request, $trashDetails);
        }
        DB::table('class')
            ->where('classid', $request->classid)
            ->update([
                'classname' => $request->classname,
                'gradeid' => $request->gradeid,
                'majorid' => $request->majorid
            ]);

        $oldGrade = $old ? DB::table('grade')->where('gradeid', $old->gradeid)->value('gradename') : null;
        $oldMajor = $old ? DB::table('major')->where('majorid', $old->majorid)->value('majorname') : null;
        $newGrade = DB::table('grade')->where('gradeid', $request->gradeid)->value('gradename');
        $newMajor = DB::table('major')->where('majorid', $request->majorid)->value('majorname');
        $details = 'classid=' . $request->classid
                 . '; classname=' . ($old->classname ?? '') . '->' . $request->classname
                 . '; grade=' . ($oldGrade ?? $old->gradeid ?? '') . '->' . ($newGrade ?? $request->gradeid)
                 . '; major=' . ($oldMajor ?? $old->majorid ?? '') . '->' . ($newMajor ?? $request->majorid);
        $this->logActivity($request, 'update_class', null, null, null, $details);

        return response()->json(['success' => true, 'message' => 'Class updated successfully']);
    }

    public function deleteClass(Request $request) {
        $request->validate([
            'classid' => 'required|exists:class,classid'
        ]);

        // Optional: Check if class is used by students
        // $count = DB::table('student')->where('classid', $request->classid)->count();
        // if ($count > 0) return response()->json(['success' => false, 'message' => 'Class is used by students']);

        $row = DB::table('class')->where('classid', $request->classid)->first();
        $gname = $row ? DB::table('grade')->where('gradeid', $row->gradeid)->value('gradename') : null;
        $mname = $row ? DB::table('major')->where('majorid', $row->majorid)->value('majorname') : null;
        $details = 'classid=' . $request->classid . '; classname=' . ($row->classname ?? '') . '; grade=' . ($gname ?? '') . '; major=' . ($mname ?? '');
        if ($row) {
            $trashDetails = 'deleted classid ' . $request->classid
                . ' with classname ' . ($row->classname ?? '')
                . '; grade=' . ($gname ?? '')
                . '; major=' . ($mname ?? '');
            $this->saveTrash('class', $request->classid, 'delete', $row, $request, $trashDetails);
        }
        DB::table('class')->where('classid', $request->classid)->delete();
        $this->logActivity($request, 'delete_class', null, null, null, $details);

        return response()->json(['success' => true, 'message' => 'Class deleted successfully']);
    }

    public function gradedata(Request $request){
        $system = DB::table('system')->first();
        
        if ($request->ajax()) {
            $grades = DB::table('grade')->get();
            return response()->json(['data' => $grades]);
        }
        
        $grades = DB::table('grade')->get();

        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));
        echo view ('admin.gradedata', compact('grades'));
        echo view ('all.footer');
    }

    public function exportGrades() {
        $rows = DB::table('grade')->select('gradeid', 'gradename')->orderBy('gradename')->get();
        $data = [];
        $data[] = ['GradeID','GradeName'];
        foreach ($rows as $r) {
            $data[] = [(string)$r->gradeid, (string)$r->gradename];
        }
        $bin = \App\Libraries\ExportXlsx::generate($data, 'Grades');
        return response($bin, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="grades.xlsx"'
        ]);
    }

    public function importGrades(Request $request) {
        if (!$request->hasFile('file')) return back()->with('error', 'Please upload a file');
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $imported = 0; $errors = [];
        try {
            if ($ext === 'csv') {
                $h = fopen($file->getRealPath(), 'r'); if (!$h) return back()->with('error','Cannot open CSV');
                $first=''; while(($first=fgets($h))!==false){ if(trim($first)!=='') break; } if($first===''||$first===false){ fclose($h); return back()->with('error','CSV empty'); }
                $delims=[',',';','\t']; $best=','; $cnt=1; foreach($delims as $d){ $p=str_getcsv($first,$d); if(count($p)>$cnt){$cnt=count($p);$best=$d;}}
                rewind($h); $header=fgetcsv($h,0,$best); if(!$header||count($header)<1){ fclose($h); return back()->with('error','CSV header invalid');}
                if(isset($header[0])){ $header[0]=preg_replace('/^\xEF\xBB\xBF/','',$header[0]); }
                $header=array_map(function($x){return strtolower(trim($x));},$header);
                $rowNum=2; while(($row=fgetcsv($h,0,$best))!==false){
                    $joined=implode('',array_map('trim',$row)); if($joined===''){ $rowNum++; continue; }
                    if(count($row)<count($header)){$row=array_pad($row,count($header),'');} elseif(count($row)>count($header)){$row=array_slice($row,0,count($header));}
                    $data=[]; for($i=0;$i<count($header);$i++){$data[$header[$i]]=isset($row[$i])?trim($row[$i]):'';}
                    $this->importGradeRow($data,$errors,$imported,$rowNum); $rowNum++;
                }
                fclose($h);
            } else if ($ext === 'xls') {
                $content=file_get_contents($file->getRealPath()); $dom=new \DOMDocument(); libxml_use_internal_errors(true); $dom->loadHTML($content); libxml_clear_errors();
                $rows=$dom->getElementsByTagName('tr'); $header=[]; $set=false; $rowNum=2;
                foreach($rows as $tr){ $td=[]; foreach($tr->getElementsByTagName('td') as $tdx){ $td[]=trim($tdx->textContent);} $th=[]; foreach($tr->getElementsByTagName('th') as $thx){ $th[]=trim($thx->textContent);}
                    if(!$set){ $cand=(count($th)>count($td))?$th:$td; if(count($cand)>=1){ $header=array_map(function($x){return strtolower(trim($x));},$cand); $set=true; continue;} else {continue;} }
                    $cells=$td; $join=implode('',array_map('trim',$cells)); if($join===''){ $rowNum++; continue; }
                    if(count($cells)<count($header)){$cells=array_pad($cells,count($header),'');} elseif(count($cells)>count($header)){$cells=array_slice($cells,0,count($header));}
                    $data=array_combine($header,$cells); $this->importGradeRow($data,$errors,$imported,$rowNum); $rowNum++;
                }
            } else if ($ext === 'xlsx') {
                $zip=new \ZipArchive(); if($zip->open($file->getRealPath())!==true) return back()->with('error','Cannot open XLSX');
                $ss=[]; $ssi=$zip->locateName('xl/sharedStrings.xml'); if($ssi!==false){ $ssXml=$zip->getFromIndex($ssi); if($ssXml){ $ssDoc=simplexml_load_string($ssXml); if($ssDoc&&isset($ssDoc->si)){ foreach($ssDoc->si as $si){ if(isset($si->t)){$ss[]=(string)$si->t;} else if(isset($si->r)){ $s=''; foreach($si->r as $r){ $s.=(string)$r->t;} $ss[]=$s;} else {$ss[]='';}}}}}
                $sheetXml=null; foreach(['xl/worksheets/sheet1.xml','xl/worksheets/sheet01.xml'] as $sp){ $idx=$zip->locateName($sp); if($idx!==false){ $sheetXml=$zip->getFromIndex($idx); break; } }
                $zip->close(); if(!$sheetXml) return back()->with('error','XLSX missing sheet1.xml');
                $sheet=simplexml_load_string($sheetXml); if(!$sheet) return back()->with('error','Invalid XLSX sheet');
                $colToIndex=function($ref){ $col=preg_replace('/\d+/','',$ref); $n=0; for($i=0;$i<strlen($col);$i++){ $n=$n*26+(ord($col[$i])-65+1);} return $n-1; };
                $header=[]; $set=false; $rowNum=2;
                if(isset($sheet->sheetData->row)){ foreach($sheet->sheetData->row as $row){
                    $cells=[]; $max=0; foreach($row->c as $c){ $idx=$colToIndex((string)$c['r']); $max=max($max,$idx); $t=(string)$c['t']; $v=''; if(isset($c->v)){ $val=(string)$c->v; if($t==='s'){ $si=intval($val); $v=isset($ss[$si])?$ss[$si]:'';} else {$v=$val;} } elseif(isset($c->is->t)){ $v=(string)$c->is->t; } $cells[$idx]=trim($v); }
                    $arr=[]; for($i=0;$i<=$max;$i++){ $arr[$i]=isset($cells[$i])?$cells[$i]:''; }
                    if(!$set){ if(count($arr)>=1){ $header=array_map(function($x){return strtolower(trim($x));},$arr); $set=true; continue;} else { continue; } }
                    $join=implode('',array_map('trim',$arr)); if($join===''){ $rowNum++; continue; }
                    if(count($arr)<count($header)){$arr=array_pad($arr,count($header),'');} elseif(count($arr)>count($header)){$arr=array_slice($arr,0,count($header));}
                    $data=array_combine($header,$arr); $this->importGradeRow($data,$errors,$imported,$rowNum); $rowNum++;
                } }
            } else { return back()->with('error','Unsupported file type'); }
        } catch (\Exception $e) { return back()->with('error','Import error: '.$e->getMessage()); }
        $msg="Import grades: {$imported} processed"; if(!empty($errors)){ $msg.=". ".count($errors)." errors. ".implode(' | ', array_slice($errors,0,5)); }
        return back()->with('success',$msg);
    }

    private function importGradeRow($data, &$errors, &$imported, $rowNumber=null) {
        $prefix = $rowNumber ? "Row {$rowNumber}: " : "";
        $gradeid = isset($data['gradeid']) && is_numeric($data['gradeid']) ? intval($data['gradeid']) : null;
        $gradename = trim($data['gradename'] ?? '');
        if (empty($gradename) && empty($gradeid)) { $errors[] = $prefix.'Missing gradename'; return; }
        if ($gradeid) {
            $g = DB::table('grade')->where('gradeid',$gradeid)->first();
            if ($g) {
                DB::table('grade')->where('gradeid',$gradeid)->update(['gradename' => $gradename ?: $g->gradename]);
                $imported++; return;
            }
        }
        $match = DB::table('grade')->where('gradename',$gradename)->first();
        if ($match) {
            DB::table('grade')->where('gradeid',$match->gradeid)->update(['gradename' => $gradename]);
            $imported++; return;
        }
        DB::table('grade')->insert(['gradename' => $gradename ?: 'Grade']);
        $imported++; return;
    }
    public function addGrade(Request $request) {
        $request->validate([
            'gradename' => 'required'
        ]);

        DB::table('grade')->insert([
            'gradename' => $request->gradename
        ]);

        $this->logActivity($request, 'add_grade', null, null, null, 'gradename=' . $request->gradename);

        return response()->json(['success' => true, 'message' => 'Grade added successfully']);
    }

    public function updateGrade(Request $request) {
        $request->validate([
            'gradeid' => 'required|exists:grade,gradeid',
            'gradename' => 'required'
        ]);

        $old = DB::table('grade')->where('gradeid', $request->gradeid)->first();
        if ($old) {
            $trashDetails = 'edited grade id ' . $request->gradeid
                . ' from gradename ' . ($old->gradename ?? '')
                . ' to ' . $request->gradename;
            $this->saveTrash('grade', $request->gradeid, 'update', $old, $request, $trashDetails);
        }
        DB::table('grade')
            ->where('gradeid', $request->gradeid)
            ->update([
                'gradename' => $request->gradename
            ]);

        $details = 'gradeid=' . $request->gradeid . '; gradename=' . ($old->gradename ?? '') . '->' . $request->gradename;
        $this->logActivity($request, 'update_grade', null, null, null, $details);

        return response()->json(['success' => true, 'message' => 'Grade updated successfully']);
    }

    public function deleteGrade(Request $request) {
        $request->validate([
            'gradeid' => 'required|exists:grade,gradeid'
        ]);

        // Optional: Check if grade is used by classes
        // $count = DB::table('class')->where('gradeid', $request->gradeid)->count();
        // if ($count > 0) return response()->json(['success' => false, 'message' => 'Grade is used by classes']);

        $row = DB::table('grade')->where('gradeid', $request->gradeid)->first();
        $details = 'gradeid=' . $request->gradeid . '; gradename=' . ($row->gradename ?? '');
        if ($row) {
            $trashDetails = 'deleted grade id ' . $request->gradeid
                . ' with gradename ' . ($row->gradename ?? '');
            $this->saveTrash('grade', $request->gradeid, 'delete', $row, $request, $trashDetails);
        }
        DB::table('grade')->where('gradeid', $request->gradeid)->delete();
        $this->logActivity($request, 'delete_grade', null, null, null, $details);

        return response()->json(['success' => true, 'message' => 'Grade deleted successfully']);
    }

    public function majordata(Request $request){
        $system = DB::table('system')->first();
        
        if ($request->ajax()) {
            $majors = DB::table('major')->get();
            return response()->json(['data' => $majors]);
        }
        
        $majors = DB::table('major')->get();

        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));
        echo view ('admin.majordata', compact('majors'));
        echo view ('all.footer');
    }

    public function addMajor(Request $request) {
        $request->validate([
            'majorname' => 'required'
        ]);

        DB::table('major')->insert([
            'majorname' => $request->majorname
        ]);

        $this->logActivity($request, 'add_major', null, null, null, 'majorname=' . $request->majorname);

        return response()->json(['success' => true, 'message' => 'Major added successfully']);
    }

    public function updateMajor(Request $request) {
        $request->validate([
            'majorid' => 'required|exists:major,majorid',
            'majorname' => 'required'
        ]);

        $old = DB::table('major')->where('majorid', $request->majorid)->first();
        if ($old) {
            $trashDetails = 'edited major id ' . $request->majorid
                . ' from majorname ' . ($old->majorname ?? '')
                . ' to ' . $request->majorname;
            $this->saveTrash('major', $request->majorid, 'update', $old, $request, $trashDetails);
        }
        DB::table('major')
            ->where('majorid', $request->majorid)
            ->update([
                'majorname' => $request->majorname
            ]);

        $details = 'majorid=' . $request->majorid . '; majorname=' . ($old->majorname ?? '') . '->' . $request->majorname;
        $this->logActivity($request, 'update_major', null, null, null, $details);

        return response()->json(['success' => true, 'message' => 'Major updated successfully']);
    }

    public function deleteMajor(Request $request) {
        $request->validate([
            'majorid' => 'required|exists:major,majorid'
        ]);

        // Optional: Check if major is used by classes
        // $count = DB::table('class')->where('majorid', $request->majorid)->count();
        // if ($count > 0) return response()->json(['success' => false, 'message' => 'Major is used by classes']);

        $row = DB::table('major')->where('majorid', $request->majorid)->first();
        $details = 'majorid=' . $request->majorid . '; majorname=' . ($row->majorname ?? '');
        if ($row) {
            $trashDetails = 'deleted major id ' . $request->majorid
                . ' with majorname ' . ($row->majorname ?? '');
            $this->saveTrash('major', $request->majorid, 'delete', $row, $request, $trashDetails);
        }
        DB::table('major')->where('majorid', $request->majorid)->delete();
        $this->logActivity($request, 'delete_major', null, null, null, $details);

        return response()->json(['success' => true, 'message' => 'Major deleted successfully']);
    }

    public function exportMajors() {
        $rows = DB::table('major')
            ->select('majorid', 'majorname')
            ->orderBy('majorname')
            ->get();
        $data = [];
        $data[] = ['MajorID','MajorName'];
        foreach ($rows as $r) {
            $data[] = [(string)$r->majorid, (string)$r->majorname];
        }
        $bin = \App\Libraries\ExportXlsx::generate($data, 'Majors');
        return response($bin, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="majors.xlsx"'
        ]);
    }

    public function importMajors(Request $request) {
        if (!$request->hasFile('file')) {
            return back()->with('error', 'Please upload a file');
        }
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $imported = 0;
        $errors = [];
        try {
            if ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                if (!$handle) return back()->with('error', 'Cannot open CSV file');
                $firstLine = '';
                while (($firstLine = fgets($handle)) !== false) { if (trim($firstLine) !== '') break; }
                if ($firstLine === '' || $firstLine === false) { fclose($handle); return back()->with('error', 'CSV file is empty'); }
                $delims = [',',';',"\t"]; $bestDelim = ','; $bestCount = 1;
                foreach ($delims as $d) { $parts = str_getcsv($firstLine, $d); if (count($parts) > $bestCount) { $bestCount = count($parts); $bestDelim = $d; } }
                rewind($handle);
                $header = fgetcsv($handle, 0, $bestDelim);
                if (!$header || count($header) < 1) { fclose($handle); return back()->with('error', 'CSV header invalid'); }
                if (isset($header[0])) { $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); }
                $header = array_map(function($h){ return strtolower(trim($h)); }, $header);
                $rowNumber = 2;
                while (($row = fgetcsv($handle, 0, $bestDelim)) !== false) {
                    $joined = implode('', array_map('trim', $row)); if ($joined === '') { $rowNumber++; continue; }
                    if (count($row) < count($header)) { $row = array_pad($row, count($header), ''); }
                    elseif (count($row) > count($header)) { $row = array_slice($row, 0, count($header)); }
                    $data = []; for ($i=0; $i<count($header); $i++) { $data[$header[$i]] = isset($row[$i]) ? trim($row[$i]) : ''; }
                    $this->importMajorRow($data, $errors, $imported, $rowNumber);
                    $rowNumber++;
                }
                fclose($handle);
            } else if ($ext === 'xls') {
                $content = file_get_contents($file->getRealPath());
                $dom = new \DOMDocument(); libxml_use_internal_errors(true); $dom->loadHTML($content); libxml_clear_errors();
                $rows = $dom->getElementsByTagName('tr'); $header = []; $headerSet = false; $rowNumber = 2;
                foreach ($rows as $tr) {
                    $tdCells = []; foreach ($tr->getElementsByTagName('td') as $td) { $tdCells[] = trim($td->textContent); }
                    $thCells = []; foreach ($tr->getElementsByTagName('th') as $th) { $thCells[] = trim($th->textContent); }
                    if (!$headerSet) {
                        $candidate = (count($thCells) > count($tdCells)) ? $thCells : $tdCells;
                        if (count($candidate) >= 1) { $header = array_map(function($h){ return strtolower(trim($h)); }, $candidate); $headerSet = true; continue; } else { continue; }
                    }
                    $cells = $tdCells; $joined = implode('', array_map('trim', $cells)); if ($joined === '') { $rowNumber++; continue; }
                    if (count($cells) < count($header)) { $cells = array_pad($cells, count($header), ''); }
                    elseif (count($cells) > count($header)) { $cells = array_slice($cells, 0, count($header)); }
                    $data = array_combine($header, $cells);
                    $this->importMajorRow($data, $errors, $imported, $rowNumber);
                    $rowNumber++;
                }
            } else if ($ext === 'xlsx') {
                $zip = new \ZipArchive(); if ($zip->open($file->getRealPath()) !== true) return back()->with('error', 'Cannot open XLSX file');
                $sharedStrings = []; $ssIndex = $zip->locateName('xl/sharedStrings.xml');
                if ($ssIndex !== false) {
                    $ssXml = $zip->getFromIndex($ssIndex);
                    if ($ssXml) { $ssDoc = simplexml_load_string($ssXml); if ($ssDoc && isset($ssDoc->si)) {
                        foreach ($ssDoc->si as $si) {
                            if (isset($si->t)) { $sharedStrings[] = (string)$si->t; }
                            else if (isset($si->r)) { $str=''; foreach ($si->r as $r) { $str .= (string)$r->t; } $sharedStrings[] = $str; }
                            else { $sharedStrings[]=''; }
                        }
                    } }
                }
                $sheetXml = null; $sheetPaths = ['xl/worksheets/sheet1.xml','xl/worksheets/sheet01.xml'];
                foreach ($sheetPaths as $sp) { $idx = $zip->locateName($sp); if ($idx !== false) { $sheetXml = $zip->getFromIndex($idx); break; } }
                $zip->close(); if (!$sheetXml) return back()->with('error', 'XLSX missing sheet1.xml');
                $sheet = simplexml_load_string($sheetXml); if (!$sheet) return back()->with('error', 'Invalid XLSX sheet content');
                $colToIndex = function($colRef){ $col = preg_replace('/\d+/', '', $colRef); $len=strlen($col); $num=0; for($i=0;$i<$len;$i++){ $num=$num*26+(ord($col[$i])-65+1);} return $num-1; };
                $header = []; $headerSet=false; $rowNumber=2;
                if (isset($sheet->sheetData->row)) {
                    foreach ($sheet->sheetData->row as $row) {
                        $cells=[]; $maxIndex=0;
                        foreach ($row->c as $c) {
                            $r=(string)$c['r']; $idx=$colToIndex($r); $maxIndex=max($maxIndex,$idx);
                            $t=(string)$c['t']; $v='';
                            if (isset($c->v)) { $val=(string)$c->v; if ($t==='s') { $ssIdx=intval($val); $v=isset($sharedStrings[$ssIdx])?$sharedStrings[$ssIdx]:''; } else { $v=$val; } }
                            elseif (isset($c->is->t)) { $v=(string)$c->is->t; } else { $v=''; }
                            $cells[$idx]=trim($v);
                        }
                        $rowArr=[]; for($i=0;$i<=$maxIndex;$i++){ $rowArr[$i]=isset($cells[$i])?$cells[$i]:''; }
                        if (!$headerSet) { if (count($rowArr)>=1){ $header=array_map(function($h){return strtolower(trim($h));},$rowArr); $headerSet=true; continue; } else { continue; } }
                        $joined=implode('',array_map('trim',$rowArr)); if ($joined===''){ $rowNumber++; continue; }
                        if (count($rowArr)<count($header)) { $rowArr=array_pad($rowArr, count($header), ''); }
                        elseif (count($rowArr)>count($header)) { $rowArr=array_slice($rowArr, 0, count($header)); }
                        $data=array_combine($header,$rowArr);
                        $this->importMajorRow($data, $errors, $imported, $rowNumber);
                        $rowNumber++;
                    }
                }
            } else {
                return back()->with('error', 'Unsupported file type. Use CSV or Excel .xls/.xlsx');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Import error: '.$e->getMessage());
        }
        $msg = "Import majors: {$imported} processed";
        if (!empty($errors)) {
            $msg .= ". ".count($errors)." errors. ".implode(' | ', array_slice($errors, 0, 5));
        }
        return back()->with('success', $msg);
    }

    private function importMajorRow($data, &$errors, &$imported, $rowNumber=null) {
        $rowPrefix = $rowNumber ? "Row {$rowNumber}: " : "";
        $majorid = isset($data['majorid']) && is_numeric($data['majorid']) ? intval($data['majorid']) : null;
        $majorname = trim($data['majorname'] ?? '');
        if (empty($majorname) && empty($majorid)) { $errors[] = $rowPrefix.'Missing majorname'; return; }
        if ($majorid) {
            $exists = DB::table('major')->where('majorid',$majorid)->first();
            if ($exists) {
                DB::table('major')->where('majorid',$majorid)->update([
                    'majorname' => $majorname ?: $exists->majorname
                ]);
                $imported++; return;
            }
        }
        $match = DB::table('major')->where('majorname',$majorname)->first();
        if ($match) {
            DB::table('major')->where('majorid',$match->majorid)->update(['majorname' => $majorname]);
            $imported++; return;
        }
        DB::table('major')->insert(['majorname' => $majorname ?: 'Major']);
        $imported++; return;
    }
    public function exportClasses() {
        $rows = DB::table('class')
            ->leftJoin('grade', 'grade.gradeid', '=', 'class.gradeid')
            ->leftJoin('major', 'major.majorid', '=', 'class.majorid')
            ->select(
                'class.classid',
                'class.classname',
                'grade.gradename',
                'major.majorname'
            )
            ->orderBy('grade.gradename')
            ->orderBy('major.majorname')
            ->orderBy('class.classname')
            ->get();
        $data = [];
        $data[] = ['ClassID','ClassName','GradeName','MajorName'];
        foreach ($rows as $r) {
            $data[] = [
                (string)$r->classid,
                (string)$r->classname,
                (string)($r->gradename ?? ''),
                (string)($r->majorname ?? '')
            ];
        }
        $bin = \App\Libraries\ExportXlsx::generate($data, 'Classes');
        return response($bin, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="classes.xlsx"'
        ]);
    }

    public function importClasses(Request $request) {
        if (!$request->hasFile('file')) {
            return back()->with('error', 'Please upload a file');
        }
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $imported = 0;
        $errors = [];
        try {
            if ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                if (!$handle) return back()->with('error', 'Cannot open CSV file');
                $firstLine = '';
                while (($firstLine = fgets($handle)) !== false) { if (trim($firstLine) !== '') break; }
                if ($firstLine === '' || $firstLine === false) { fclose($handle); return back()->with('error', 'CSV file is empty'); }
                $delims = [',',';',"\t"]; $bestDelim = ','; $bestCount = 1;
                foreach ($delims as $d) { $parts = str_getcsv($firstLine, $d); if (count($parts) > $bestCount) { $bestCount = count($parts); $bestDelim = $d; } }
                rewind($handle);
                $header = fgetcsv($handle, 0, $bestDelim);
                if (!$header || count($header) < 2) { fclose($handle); return back()->with('error', 'CSV header invalid'); }
                if (isset($header[0])) { $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); }
                $header = array_map(function($h){ return strtolower(trim($h)); }, $header);
                $rowNumber = 2;
                while (($row = fgetcsv($handle, 0, $bestDelim)) !== false) {
                    $joined = implode('', array_map('trim', $row)); if ($joined === '') { $rowNumber++; continue; }
                    if (count($row) < count($header)) { $row = array_pad($row, count($header), ''); }
                    elseif (count($row) > count($header)) { $row = array_slice($row, 0, count($header)); }
                    $data = []; for ($i=0; $i<count($header); $i++) { $data[$header[$i]] = isset($row[$i]) ? trim($row[$i]) : ''; }
                    $this->importClassRow($data, $errors, $imported, $rowNumber);
                    $rowNumber++;
                }
                fclose($handle);
            } else if ($ext === 'xls') {
                $content = file_get_contents($file->getRealPath());
                $dom = new \DOMDocument(); libxml_use_internal_errors(true); $dom->loadHTML($content); libxml_clear_errors();
                $rows = $dom->getElementsByTagName('tr'); $header = []; $headerSet = false; $rowNumber = 2;
                foreach ($rows as $tr) {
                    $tdCells = []; foreach ($tr->getElementsByTagName('td') as $td) { $tdCells[] = trim($td->textContent); }
                    $thCells = []; foreach ($tr->getElementsByTagName('th') as $th) { $thCells[] = trim($th->textContent); }
                    if (!$headerSet) {
                        $candidate = (count($thCells) > count($tdCells)) ? $thCells : $tdCells;
                        if (count($candidate) >= 2) { $header = array_map(function($h){ return strtolower(trim($h)); }, $candidate); $headerSet = true; continue; } else { continue; }
                    }
                    $cells = $tdCells; $joined = implode('', array_map('trim', $cells)); if ($joined === '') { $rowNumber++; continue; }
                    if (count($cells) < count($header)) { $cells = array_pad($cells, count($header), ''); }
                    elseif (count($cells) > count($header)) { $cells = array_slice($cells, 0, count($header)); }
                    $data = array_combine($header, $cells);
                    $this->importClassRow($data, $errors, $imported, $rowNumber);
                    $rowNumber++;
                }
            } else if ($ext === 'xlsx') {
                $zip = new \ZipArchive(); if ($zip->open($file->getRealPath()) !== true) return back()->with('error', 'Cannot open XLSX file');
                $sharedStrings = []; $ssIndex = $zip->locateName('xl/sharedStrings.xml');
                if ($ssIndex !== false) {
                    $ssXml = $zip->getFromIndex($ssIndex);
                    if ($ssXml) { $ssDoc = simplexml_load_string($ssXml); if ($ssDoc && isset($ssDoc->si)) {
                        foreach ($ssDoc->si as $si) {
                            if (isset($si->t)) { $sharedStrings[] = (string)$si->t; }
                            else if (isset($si->r)) { $str=''; foreach ($si->r as $r) { $str .= (string)$r->t; } $sharedStrings[] = $str; }
                            else { $sharedStrings[]=''; }
                        }
                    } }
                }
                $sheetXml = null; $sheetPaths = ['xl/worksheets/sheet1.xml','xl/worksheets/sheet01.xml'];
                foreach ($sheetPaths as $sp) { $idx = $zip->locateName($sp); if ($idx !== false) { $sheetXml = $zip->getFromIndex($idx); break; } }
                $zip->close(); if (!$sheetXml) return back()->with('error', 'XLSX missing sheet1.xml');
                $sheet = simplexml_load_string($sheetXml); if (!$sheet) return back()->with('error', 'Invalid XLSX sheet content');
                $colToIndex = function($colRef){ $col = preg_replace('/\d+/', '', $colRef); $len=strlen($col); $num=0; for($i=0;$i<$len;$i++){ $num=$num*26+(ord($col[$i])-65+1);} return $num-1; };
                $header = []; $headerSet=false; $rowNumber=2;
                if (isset($sheet->sheetData->row)) {
                    foreach ($sheet->sheetData->row as $row) {
                        $cells=[]; $maxIndex=0;
                        foreach ($row->c as $c) {
                            $r=(string)$c['r']; $idx=$colToIndex($r); $maxIndex=max($maxIndex,$idx);
                            $t=(string)$c['t']; $v='';
                            if (isset($c->v)) { $val=(string)$c->v; if ($t==='s') { $ssIdx=intval($val); $v=isset($sharedStrings[$ssIdx])?$sharedStrings[$ssIdx]:''; } else { $v=$val; } }
                            elseif (isset($c->is->t)) { $v=(string)$c->is->t; } else { $v=''; }
                            $cells[$idx]=trim($v);
                        }
                        $rowArr=[]; for($i=0;$i<=$maxIndex;$i++){ $rowArr[$i]=isset($cells[$i])?$cells[$i]:''; }
                        if (!$headerSet) { if (count($rowArr)>=2){ $header=array_map(function($h){return strtolower(trim($h));},$rowArr); $headerSet=true; continue; } else { continue; } }
                        $joined=implode('',array_map('trim',$rowArr)); if ($joined===''){ $rowNumber++; continue; }
                        if (count($rowArr)<count($header)) { $rowArr=array_pad($rowArr, count($header), ''); }
                        elseif (count($rowArr)>count($header)) { $rowArr=array_slice($rowArr, 0, count($header)); }
                        $data=array_combine($header,$rowArr);
                        $this->importClassRow($data, $errors, $imported, $rowNumber);
                        $rowNumber++;
                    }
                }
            } else {
                return back()->with('error', 'Unsupported file type. Use CSV or Excel .xls/.xlsx');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Import error: '.$e->getMessage());
        }
        $msg = "Import classes: {$imported} processed";
        if (!empty($errors)) {
            $msg .= ". ".count($errors)." errors. ".implode(' | ', array_slice($errors, 0, 5));
        }
        return back()->with('success', $msg);
    }

    private function importClassRow($data, &$errors, &$imported, $rowNumber=null) {
        $rowPrefix = $rowNumber ? "Row {$rowNumber}: " : "";
        $classid = isset($data['classid']) && is_numeric($data['classid']) ? intval($data['classid']) : null;
        $classname = trim($data['classname'] ?? '');
        $gradename = trim($data['gradename'] ?? '');
        $majorname = trim($data['majorname'] ?? '');
        if (empty($classname) && empty($classid)) { $errors[] = $rowPrefix."Missing classname"; return; }
        $gradeid = null; if (!empty($gradename)) { $g = DB::table('grade')->where('gradename',$gradename)->first(); if (!$g) { $gradeid = DB::table('grade')->insertGetId(['gradename'=>$gradename]); } else { $gradeid = $g->gradeid; } }
        $majorid = null; if (!empty($majorname)) { $m = DB::table('major')->where('majorname',$majorname)->first(); if (!$m) { $majorid = DB::table('major')->insertGetId(['majorname'=>$majorname]); } else { $majorid = $m->majorid; } }
        if ($classid) {
            $exists = DB::table('class')->where('classid',$classid)->first();
            if ($exists) {
                DB::table('class')->where('classid',$classid)->update([
                    'classname' => $classname ?: $exists->classname,
                    'gradeid' => $gradeid ?? $exists->gradeid,
                    'majorid' => $majorid ?? $exists->majorid
                ]);
                $imported++; return;
            }
        }
        $match = DB::table('class')->where('classname',$classname);
        if ($gradeid) $match->where('gradeid',$gradeid);
        if ($majorid) $match->where('majorid',$majorid);
        $found = $match->first();
        if ($found) {
            DB::table('class')->where('classid',$found->classid)->update([
                'classname' => $classname,
                'gradeid' => $gradeid ?? $found->gradeid,
                'majorid' => $majorid ?? $found->majorid
            ]);
            $imported++; return;
        }
        DB::table('class')->insert([
            'classname' => $classname ?: 'Class',
            'gradeid' => $gradeid ?? 1,
            'majorid' => $majorid
        ]);
        $imported++; return;
    }
    public function permissionPage(Request $request) {
        DB::statement('CREATE TABLE IF NOT EXISTS menu_permissions (
            permissionid INT AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(50) NOT NULL,
            menu_key VARCHAR(50) NOT NULL,
            allowed TINYINT(1) NOT NULL DEFAULT 0
        )');
        $system = DB::table('system')->first();
        $menus = ['teacherlist','followups','chat','classdata','gradedata','majordata','userdata','activity_logs','database','notifications','permission','setting'];
        $subjects = ['superadmin','admin','counselling_teacher','homeroom_teacher','student'];
        $rows = DB::table('menu_permissions')->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));
        echo view ('superadmin.permission', compact('menus','subjects','rows'));
        echo view ('all.footer');
    }

    public function savePermissions(Request $request) {
        $menus = explode(',', $request->input('menus', ''));
        $subjects = explode(',', $request->input('subjects', ''));
        $matrix = $request->input('matrix', []);
        DB::table('menu_permissions')->truncate();
        foreach ($subjects as $s) {
            if (empty($s)) continue;
            foreach ($menus as $m) {
                if (empty($m)) continue;
                $allowed = isset($matrix[$s][$m]) ? 1 : 0;
                DB::table('menu_permissions')->insert([
                    'subject' => $s,
                    'menu_key' => $m,
                    'allowed' => $allowed
                ]);
            }
        }
        return redirect('/permission')->with('success','Permissions updated');
    }
    public function activityLogsPage(Request $request) {
        DB::statement('CREATE TABLE IF NOT EXISTS activity_logs (
            logid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NULL,
            username VARCHAR(255) NULL,
            actor_label VARCHAR(255) NULL,
            action VARCHAR(255) NOT NULL,
            ip_address VARCHAR(64) NULL,
            latitude DOUBLE NULL,
            longitude DOUBLE NULL,
            created_at DATETIME NOT NULL
        )');
        $system = DB::table('system')->first();
        $roleName = strtolower(str_replace(' ', '', session('role') ?? ''));
        $q = DB::table('activity_logs');
        if ($roleName !== 'superadmin') {
            $q->where(function($w){
                $w->whereNull('actor_label')->orWhere('actor_label','!=','superadmin');
            });
        }
        $logs = $q->orderBy('created_at','desc')->limit(500)->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));
        echo view ('admin.activitylogs', compact('logs'));
        echo view ('all.footer');
    }
    public function listActivityLogs(Request $request) {
        DB::statement('CREATE TABLE IF NOT EXISTS activity_logs (
            logid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NULL,
            username VARCHAR(255) NULL,
            actor_label VARCHAR(255) NULL,
            action VARCHAR(255) NOT NULL,
            ip_address VARCHAR(64) NULL,
            latitude DOUBLE NULL,
            longitude DOUBLE NULL,
            details TEXT NULL,
            created_at DATETIME NOT NULL
        )');
        $roleName = strtolower(str_replace(' ', '', session('role') ?? ''));
        $query = DB::table('activity_logs');
        if ($roleName !== 'superadmin') {
            $query->where(function($w){
                $w->whereNull('actor_label')->orWhere('actor_label','!=','superadmin');
            });
        }
        $role = $request->input('role'); // actor_label filter
        $q = trim($request->input('q',''));
        if ($role) $query->where('actor_label', $role);
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function($w) use ($like) {
                $w->where('username','like',$like)
                  ->orWhere('actor_label','like',$like)
                  ->orWhere('action','like',$like)
                  ->orWhere('details','like',$like)
                  ->orWhere('ip_address','like',$like);
            });
        }
        $rows = $query->orderBy('created_at','desc')->limit(500)->get();
        return response()->json(['success'=>true,'rows'=>$rows]);
    }
    public function setGeo(Request $request) {
        $lat = $request->latitude;
        $lng = $request->longitude;
        if ($lat !== null && $lng !== null) {
            session(['latitude' => $lat, 'longitude' => $lng]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 400);
    }
    public function notificationsPage(Request $request) {
        $uid = session('userid');
        if (!$uid) {
            return redirect('/login');
        }
        DB::statement('CREATE TABLE IF NOT EXISTS notifications (
            notificationid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            body TEXT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )');
        $system = DB::table('system')->first();
        $rows = DB::table('notifications')->where('userid',$uid)->orderBy('created_at','desc')->limit(200)->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));
        echo view ('all.notifications', compact('rows'));
        echo view ('all.footer');
    }
    public function deleteNotifications(Request $request) {
        $uid = session('userid');
        if (!$uid) {
            return response()->json(['success'=>false,'message'=>'Unauthorized'],403);
        }
        DB::statement('CREATE TABLE IF NOT EXISTS notifications (
            notificationid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            body TEXT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )');
        $ids = $request->input('ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return response()->json(['success'=>false,'message'=>'No IDs provided'],400);
        }
        DB::table('notifications')->whereIn('notificationid',$ids)->where('userid',$uid)->delete();
        return response()->json(['success'=>true]);
    }
    public function markNotificationRead(Request $request) {
        $uid = session('userid');
        if (!$uid) return response()->json(['success'=>false,'message'=>'Unauthorized'],403);
        $id = $request->input('id');
        if (!$id) return response()->json(['success'=>false,'message'=>'No ID'],400);
        DB::statement('CREATE TABLE IF NOT EXISTS notifications (
            notificationid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            body TEXT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )');
        DB::table('notifications')->where('notificationid',$id)->where('userid',$uid)->update(['is_read'=>1]);
        return response()->json(['success'=>true]);
    }
    public function markAllNotificationsRead(Request $request) {
        $uid = session('userid');
        if (!$uid) return response()->json(['success'=>false,'message'=>'Unauthorized'],403);
        DB::statement('CREATE TABLE IF NOT EXISTS notifications (
            notificationid INT AUTO_INCREMENT PRIMARY KEY,
            userid INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            body TEXT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )');
        DB::table('notifications')->where('userid',$uid)->update(['is_read'=>1]);
        return response()->json(['success'=>true]);
    }
    public function trashPage(Request $request) {
        $system = DB::table('system')->first();
        DB::statement('CREATE TABLE IF NOT EXISTS trash_bin (
            trashid INT AUTO_INCREMENT PRIMARY KEY,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            action VARCHAR(20) NOT NULL,
            snapshot LONGTEXT NOT NULL,
            created_by INT NULL,
            actor_username VARCHAR(255) NULL,
            actor_label VARCHAR(255) NULL,
            actor_level INT NULL,
            ip_address VARCHAR(64) NULL,
            details TEXT NULL,
            created_at DATETIME NOT NULL
        )');
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_username VARCHAR(255) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_label VARCHAR(255) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_level INT NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN ip_address VARCHAR(64) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN details TEXT NULL'); } catch (\Exception $e) {}
        $rows = DB::table('trash_bin')->orderBy('created_at','desc')->limit(500)->get();
        echo view ('all.header',compact('system'));
        echo view ('all.menu',compact('system'));
        echo view ('admin.trash', compact('rows'));
        echo view ('all.footer');
    }
    public function restoreTrash(Request $request) {
        $trashid = $request->input('trashid');
        if (!$trashid) return response()->json(['success'=>false,'message'=>'No trash id'],400);
        $row = DB::table('trash_bin')->where('trashid',$trashid)->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Trash not found'],404);
        $snap = json_decode($row->snapshot, true);
        $type = $row->entity_type;
        $id = $row->entity_id;
        try {
            if ($type === 'class' && $snap) {
                $exists = DB::table('class')->where('classid',$id)->first();
                if ($exists) {
                    DB::table('class')->where('classid',$id)->update([
                        'classname' => $snap['classname'] ?? $exists->classname,
                        'gradeid' => $snap['gradeid'] ?? $exists->gradeid,
                        'majorid' => $snap['majorid'] ?? $exists->majorid
                    ]);
                } else {
                    DB::table('class')->insert([
                        'classid' => $id,
                        'classname' => $snap['classname'] ?? 'Class',
                        'gradeid' => $snap['gradeid'] ?? null,
                        'majorid' => $snap['majorid'] ?? null
                    ]);
                }
            } else if ($type === 'grade' && $snap) {
                $exists = DB::table('grade')->where('gradeid',$id)->first();
                if ($exists) {
                    DB::table('grade')->where('gradeid',$id)->update([
                        'gradename' => $snap['gradename'] ?? $exists->gradename
                    ]);
                } else {
                    DB::table('grade')->insert([
                        'gradeid' => $id,
                        'gradename' => $snap['gradename'] ?? 'Grade'
                    ]);
                }
            } else if ($type === 'major' && $snap) {
                $exists = DB::table('major')->where('majorid',$id)->first();
                if ($exists) {
                    DB::table('major')->where('majorid',$id)->update([
                        'majorname' => $snap['majorname'] ?? $exists->majorname
                    ]);
                } else {
                    DB::table('major')->insert([
                        'majorid' => $id,
                        'majorname' => $snap['majorname'] ?? 'Major'
                    ]);
                }
            } else if ($type === 'user' && $snap) {
                $u = isset($snap['user']) ? $snap['user'] : null;
                $s = isset($snap['student']) ? $snap['student'] : null;
                $e = isset($snap['employer']) ? $snap['employer'] : null;
                $t = isset($snap['teacher']) ? $snap['teacher'] : null;
                if ($u) {
                    $exists = DB::table('user')->where('userid', $id)->first();
                    $userData = [
                        'username' => $u['username'] ?? ($exists->username ?? 'user'),
                        'password' => $u['password'] ?? ($exists->password ?? Hash::make('password')),
                        'levelid' => $u['levelid'] ?? ($exists->levelid ?? 3),
                    ];
                    if (isset($u['verified_at'])) $userData['verified_at'] = $u['verified_at'];
                    if ($exists) {
                        DB::table('user')->where('userid', $id)->update($userData);
                    } else {
                        $userData['userid'] = $id;
                        DB::table('user')->insert($userData);
                    }
                    $lvl = $userData['levelid'];
                    if ($lvl == 3 && $s) {
                        $stuExists = DB::table('student')->where('userid', $id)->first();
                        $stuData = [
                            'name' => $s['name'] ?? ($stuExists->name ?? ''),
                            'email' => $s['email'] ?? ($stuExists->email ?? ''),
                            'phonenumber' => $s['phonenumber'] ?? ($stuExists->phonenumber ?? ''),
                            'classid' => $s['classid'] ?? ($stuExists->classid ?? null),
                            'userid' => $id
                        ];
                        if ($stuExists) {
                            DB::table('student')->where('userid',$id)->update($stuData);
                        } else {
                            DB::table('student')->insert($stuData);
                        }
                    } else if ($lvl == 1 && $e) {
                        $empExists = DB::table('employer')->where('userid', $id)->first();
                        $empData = [
                            'name' => $e['name'] ?? ($empExists->name ?? ''),
                            'email' => $e['email'] ?? ($empExists->email ?? ''),
                            'phonenumber' => $e['phonenumber'] ?? ($empExists->phonenumber ?? ''),
                            'roleid' => $e['roleid'] ?? ($empExists->roleid ?? null),
                            'userid' => $id
                        ];
                        if ($empExists) {
                            DB::table('employer')->where('userid',$id)->update($empData);
                        } else {
                            DB::table('employer')->insert($empData);
                        }
                    } else if ($lvl == 2 && $t) {
                        $teaExists = DB::table('teacher')->where('userid', $id)->first();
                        $teaData = [
                            'name' => $t['name'] ?? ($teaExists->name ?? ''),
                            'email' => $t['email'] ?? ($teaExists->email ?? ''),
                            'phonenumber' => $t['phonenumber'] ?? ($teaExists->phonenumber ?? ''),
                            'roleid' => $t['roleid'] ?? ($teaExists->roleid ?? null),
                            'userid' => $id
                        ];
                        if ($teaExists) {
                            DB::table('teacher')->where('userid',$id)->update($teaData);
                        } else {
                            DB::table('teacher')->insert($teaData);
                        }
                    }
                }
            }
            DB::table('trash_bin')->where('trashid',$trashid)->delete();
            return response()->json(['success'=>true]);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
        }
    }
    public function deleteTrashPermanent(Request $request) {
        $trashid = $request->input('trashid');
        if (!$trashid) return response()->json(['success'=>false,'message'=>'No trash id'],400);
        $row = DB::table('trash_bin')->where('trashid',$trashid)->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Trash not found'],404);
        try {
            DB::table('trash_bin')->where('trashid',$trashid)->delete();
            return response()->json(['success'=>true]);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
        }
    }
    public function listTrash(Request $request) {
        DB::statement('CREATE TABLE IF NOT EXISTS trash_bin (
            trashid INT AUTO_INCREMENT PRIMARY KEY,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            action VARCHAR(20) NOT NULL,
            snapshot LONGTEXT NOT NULL,
            created_by INT NULL,
            actor_username VARCHAR(255) NULL,
            actor_label VARCHAR(255) NULL,
            actor_level INT NULL,
            ip_address VARCHAR(64) NULL,
            details TEXT NULL,
            created_at DATETIME NOT NULL
        )');
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_username VARCHAR(255) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_label VARCHAR(255) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN actor_level INT NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN ip_address VARCHAR(64) NULL'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE trash_bin ADD COLUMN details TEXT NULL'); } catch (\Exception $e) {}
        $query = DB::table('trash_bin');
        $action = $request->input('action');
        $entity = $request->input('entity');
        $role = $request->input('role');
        $q = trim($request->input('q', ''));
        if ($action) $query->where('action', $action);
        if ($entity) $query->where('entity_type', $entity);
        if ($role) $query->where('actor_label', $role);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('actor_username', 'like', $like)
                  ->orWhere('actor_label', 'like', $like)
                  ->orWhere('details', 'like', $like)
                  ->orWhere('entity_type', 'like', $like)
                  ->orWhere('action', 'like', $like);
            });
        }
        $rows = $query->orderBy('created_at', 'desc')->limit(500)->get();
        return response()->json(['success'=>true, 'rows'=>$rows]);
    }
//=================================================================================================
    public function profile(Request $request){
        $system = DB::table('system')->first();
        $userid = $request->session()->get('userid');

        $data = DB::table('user')
            ->leftJoin('student', 'student.userid', '=', 'user.userid')
            ->leftJoin('employer', 'employer.userid', '=', 'user.userid')
            ->leftJoin('teacher', 'teacher.userid', '=', 'user.userid')
            ->leftJoin('class', 'class.classid', '=', 'student.studentid')
            ->leftJoin('major', 'major.majorid', '=', 'class.classid')
            ->leftJoin('grade', 'grade.gradeid', '=', 'class.classid')
            ->leftJoin('level', 'level.levelid', '=', 'user.levelid')

            ->select(
                'user.userid',
                'user.username',
                'class.classname',
                'major.majorname',
                'grade.gradename',
                'level.levelid',


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
        $level = session('level');

        DB::table('student')
            ->where('userid', $userid)
            ->update([
                'name' => $request->name,
                // email handled by verification flow
            ]);

        DB::table('employer')
            ->where('userid', $userid)
            ->update([
                'name' => $request->name,
                // email handled by verification flow
            ]);

        DB::table('teacher')
            ->where('userid', $userid)
            ->update([
                'name' => $request->name,
                // email handled by verification flow
            ]);

        $newEmail = $request->email;
        if ($newEmail) {
            $table = $level == 3 ? 'student' : ($level == 2 ? 'teacher' : 'employer');
            $currentEmail = DB::table($table)->where('userid', $userid)->value('email');
            if ($currentEmail !== $newEmail) {
                $exists = DB::table('student')->where('email', $newEmail)->exists()
                    || DB::table('teacher')->where('email', $newEmail)->exists()
                    || DB::table('employer')->where('email', $newEmail)->exists();
                if ($exists) {
                    return back()->with('error', 'Email already in use');
                }
                $token = Str::random(40);
                DB::table('email_changes')->insert([
                    'userid' => $userid,
                    'new_email' => $newEmail,
                    'token' => $token,
                    'expires_at' => now()->addDay(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $verifyUrl = url('/myprofile/verify-email') . '?token=' . $token;
                try {
                    Mail::raw("Klik tautan ini untuk verifikasi perubahan email: {$verifyUrl}", function($m) use ($newEmail) {
                        $m->to($newEmail)->subject('Verifikasi Perubahan Email');
                    });
                } catch (\Exception $e) {
                    // ignore send error
                }
                return back()->with('success', 'Verification email sent to the new address. Please check your inbox.');
            }
        }

        $this->logActivity($request, 'update_profile', $userid, null, null, 'name=' . ($request->name ?? ''));
        return back()->with('success', 'Profile updated (no email change)');
    }

    public function changepw(Request $request){
        $userid = session('userid');
        $request->validate([
            'cp' => 'required',
            'np' => 'required|min:6',
            'rp' => 'required'
        ]);
        $user = DB::table('user')->where('userid', $userid)->first();
        if (!Hash::check($request->cp, $user->password)) {
            return back()->with('error', 'Current password wrong');
        }

        if ($request->np !== $request->rp) {
            return back()->with('error', 'New password confirmation does not match');
        }

        if (Hash::check($request->np, $user->password)) {
            return back()->with('error', 'New password must be different from current password');
        }

        DB::table('user')
            ->where('userid', $userid)
            ->update([
                'password' => Hash::make($request->np)
            ]);

        return back()->with('success', 'Password changed successfully');
    }

    public function requestPhoneOtp(Request $request) {
        $userid = session('userid');
        $level = session('level');
        $newPhone = $request->new_phone;
        if (!$newPhone) {
            return response()->json(['success' => false, 'message' => 'New phone is required'], 422);
        }
        $currentPhone = DB::table($level == 3 ? 'student' : ($level == 2 ? 'teacher' : 'employer'))
            ->where('userid', $userid)->value('phonenumber');
        if ($currentPhone == $newPhone) {
            return response()->json(['success' => false, 'message' => 'New phone must be different'], 422);
        }
        $exists = DB::table('student')->where('phonenumber', $newPhone)->exists()
            || DB::table('teacher')->where('phonenumber', $newPhone)->exists()
            || DB::table('employer')->where('phonenumber', $newPhone)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Phone already in use'], 422);
        }
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Session::put('phone_change_pending', [
            'new_phone' => $newPhone,
            'otp' => $otp,
            'expires_at' => time() + 10 * 60
        ]);
        $token = env('FONNTE_TOKEN');
        if ($token) {
            try {
                Http::withHeaders(['Authorization' => $token])
                    ->asForm()
                    ->post('https://api.fonnte.com/send', [
                        'target' => $newPhone,
                        'message' => "Kode OTP perubahan nomor: {$otp}. Berlaku 10 menit.",
                        'countryCode' => '62'
                    ]);
            } catch (\Exception $e) {
                // Ignore send error; still allow OTP flow
            }
        }
        return response()->json(['success' => true, 'message' => 'OTP sent to WhatsApp']);
    }

    public function confirmPhoneOtp(Request $request) {
        $userid = session('userid');
        $level = session('level');
        $inputOtp = $request->otp;
        $pending = Session::get('phone_change_pending');
        if (!$pending) {
            return response()->json(['success' => false, 'message' => 'No pending OTP'], 400);
        }
        if (time() > $pending['expires_at']) {
            Session::forget('phone_change_pending');
            return response()->json(['success' => false, 'message' => 'OTP expired'], 400);
        }
        if ($inputOtp !== $pending['otp']) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP'], 422);
        }
        $table = $level == 3 ? 'student' : ($level == 2 ? 'teacher' : 'employer');
        DB::table($table)->where('userid', $userid)->update(['phonenumber' => $pending['new_phone']]);
        Session::forget('phone_change_pending');
        return response()->json(['success' => true, 'message' => 'Phone number updated']);
    }

    public function verifyEmailChange(Request $request) {
        $token = $request->token;
        if (!$token) {
            return redirect('/myprofile')->with('error', 'Invalid token');
        }
        $record = DB::table('email_changes')->where('token', $token)->first();
        if (!$record) {
            return redirect('/myprofile')->with('error', 'Token not found');
        }
        if ($record->expires_at && strtotime($record->expires_at) < time()) {
            return redirect('/myprofile')->with('error', 'Token expired');
        }
        $userid = $record->userid;
        $level = DB::table('user')->where('userid', $userid)->value('levelid');
        $table = $level == 3 ? 'student' : ($level == 2 ? 'teacher' : 'employer');
        DB::table($table)->where('userid', $userid)->update(['email' => $record->new_email]);
        DB::table('email_changes')->where('email_change_id', $record->email_change_id)->delete();
        return redirect('/myprofile')->with('success', 'Email has been updated');
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
            } else {
                $schedule = DB::table('schedule')
                    ->where('teacherid', $teacherid)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('status', 1)
                    ->first();

                if ($schedule) {
                    $startTime = strtotime($schedule->start_time);
                    $endTime = strtotime($schedule->end_time);

                    $existingMap = [];
                    foreach ($existingSlots as $slot) {
                        $existingMap[$slot->start_time . '-' . $slot->end_time] = true;
                    }

                    $cursor = $startTime;
                    $inserted = 0;
                    while ($cursor < $endTime) {
                        $slotStart = date('H:i:s', $cursor);
                        $nextSlot = $cursor + 1800;
                        if ($nextSlot > $endTime) break;
                        $slotEnd = date('H:i:s', $nextSlot);
                        $key = $slotStart . '-' . $slotEnd;

                        if (!isset($existingMap[$key])) {
                            DB::table('time_slots')->insert([
                                'teacherid' => $teacherid,
                                'date' => $date,
                                'start_time' => $slotStart,
                                'end_time' => $slotEnd,
                                'is_booked' => 0,
                                'created_at' => now()
                            ]);
                            $inserted++;
                        }

                        $cursor = $nextSlot;
                    }

                    if ($inserted > 0) {
                        $existingSlots = DB::table('time_slots')
                            ->where('teacherid', $teacherid)
                            ->where('date', $date)
                            ->get();
                    }
                }
            }

            $slots = [];
            $currentTimestamp = time();
            $isToday = ($date == date('Y-m-d'));

            foreach ($existingSlots as $slot) {
                // Gunakan format jam yang sama untuk perbandingan (H:i:s)
                $currentTimeStr = date('H:i:s');
                $isPast = $isToday && ($slot->start_time < $currentTimeStr);

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
            $teacher = DB::table('teacher')->where('teacherid', $slot->teacherid)->first();
            if ($teacher) {
                $this->pushNotification($teacher->userid, 'New Consultation Request', 'Student ' . ($student->name ?? '') . ' requested a chat');
            }
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
        $consultInfo = DB::table('consult')
            ->join('time_slots', 'time_slots.slotid', '=', 'consult.slotid')
            ->where('consult.consultid', $id)
            ->select('consult.status', 'time_slots.date', 'time_slots.end_time')
            ->first();

        if ($consultInfo) {
            $endDateTime = strtotime($consultInfo->date . ' ' . $consultInfo->end_time);
            if ($consultInfo->status === 'active' && time() > $endDateTime) {
                DB::table('consult')->where('consultid', $id)->update([
                    'status' => 'completed',
                    'updated_at' => now()
                ]);
            }
        }

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

        $statusData = DB::table('consult')->where('consultid', $id)->select('status', 'report_outcome', 'need_follow_up')->first();

        return response()->json([
            'messages' => $messages,
            'status' => $statusData ? $statusData->status : null,
            'has_report' => $statusData ? !empty($statusData->report_outcome) : false,
            'need_follow_up' => $statusData ? (bool)$statusData->need_follow_up : false
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

        $det = 'consultid=' . $request->consultid . '; file=' . ($file_path ? 'yes' : 'no') . '; msg_len=' . (strlen($request->message ?? '') );
        $this->logActivity($request, 'send_message', $userid, null, null, $det);

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
            $student = DB::table('student')->where('studentid', $consult->studentid)->first();
            if ($student) {
                $this->pushNotification($student->userid, 'Chat Rejected', 'Your consultation request was rejected');
            }
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
        $consult = DB::table('consult')->where('consultid', $request->consultid)->first();
        if ($consult) {
            $student = DB::table('student')->where('studentid', $consult->studentid)->first();
            if ($student) {
                $title = 'Chat Approved';
                $body = 'Your consultation request has been approved. Slot ID: ' . $consult->slotid;
                $this->pushNotification($student->userid, $title, $body);
            }
        }
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

    public function submitConsultReport(Request $request) {
        $userid = session('userid');
        $level = session('level');
        if ($level != 2) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $consultid = $request->consultid;
        $consult = DB::table('consult')
            ->join('time_slots', 'time_slots.slotid', '=', 'consult.slotid')
            ->join('student', 'student.studentid', '=', 'consult.studentid')
            ->where('consult.consultid', $consultid)
            ->select('consult.*', 'time_slots.teacherid as slot_teacherid', 'student.classid')
            ->first();
        if (!$consult) {
            return response()->json(['success' => false, 'message' => 'Consult not found'], 404);
        }
        $teacher = DB::table('teacher')->where('userid', $userid)->first();
        if (!$teacher || $teacher->teacherid != $consult->slot_teacherid) {
            return response()->json(['success' => false, 'message' => 'Not your consultation'], 403);
        }
        $needFollow = (bool)$request->need_follow_up;
        $followNotes = $request->follow_up_notes;
        $reportOutcome = $request->report_outcome;
        $assignHomeroomId = null;
        if ($needFollow) {
            $homeroom = DB::table('homeroomtc')->where('classid', $consult->classid)->first();
            if ($homeroom) {
                $assignHomeroomId = $homeroom->teacherid;
            }
        }
        DB::table('consult')->where('consultid', $consultid)->update([
            'report_outcome' => $reportOutcome,
            'need_follow_up' => $needFollow,
            'follow_up_notes' => $followNotes,
            'follow_up_assigned_teacherid' => $assignHomeroomId,
            'report_submitted_at' => now(),
            'updated_at' => now()
        ]);
        if ($assignHomeroomId) {
            $homeroomTeacher = DB::table('teacher')->where('teacherid', $assignHomeroomId)->first();
            if ($homeroomTeacher) {
                $this->pushNotification($homeroomTeacher->userid, 'Follow-up Assigned', 'A follow-up has been assigned to you');
            }
        }
        return response()->json(['success' => true, 'assigned_homeroom_teacherid' => $assignHomeroomId]);
    }

    public function addTeacher(Request $request) {
        if (session('level') != 1) {
            return back()->with('error', 'Unauthorized');
        }
        $request->validate([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'phonenumber' => 'required'
        ]);
        DB::beginTransaction();
        try {
            $existingUser = DB::table('user')->where('username', $request->username)->first();
            if ($existingUser) {
                DB::rollBack();
                return back()->with('error', 'Username already exists');
            }
            $userId = DB::table('user')->insertGetId([
                'username' => $request->username,
                'password' => Hash::make($request->username),
                'levelid' => 2,
                'verified_at' => now()
            ]);
            DB::table('teacher')->insert([
                'name' => $request->name,
                'phonenumber' => $request->phonenumber,
                'email' => $request->email,
                'roleid' => 3,
                'userid' => $userId
            ]);
            $teacherId = DB::table('teacher')->where('userid', $userId)->value('teacherid');
            $days = [
                ['monday', '08:00:00', '15:00:00'],
                ['tuesday', '08:00:00', '15:00:00'],
                ['wednesday', '08:00:00', '15:00:00'],
                ['thursday', '08:00:00', '15:00:00'],
                ['friday', '08:00:00', '15:00:00'],
                ['saturday', '08:00:00', '12:00:00'],
                ['sunday', '08:00:00', '12:00:00']
            ];
            foreach ($days as $d) {
                DB::table('schedule')->insert([
                    'teacherid' => $teacherId,
                    'day_of_week' => $d[0],
                    'start_time' => $d[1],
                    'end_time' => $d[2],
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            DB::commit();
            return back()->with('success', 'Teacher added successfully with default schedule');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function followups(Request $request) {
        $system = DB::table('system')->first();
        $userid = session('userid');
        $teacher = DB::table('teacher')->where('userid', $userid)->first();
        if (!$teacher) {
            return redirect('/home');
        }
        $items = DB::table('consult')
            ->join('student', 'student.studentid', '=', 'consult.studentid')
            ->leftJoin('time_slots', 'time_slots.slotid', '=', 'consult.slotid')
            ->leftJoin('teacher as counselor', 'counselor.teacherid', '=', 'time_slots.teacherid')
            ->leftJoin('class', 'class.classid', '=', 'student.classid')
            ->leftJoin('grade', 'grade.gradeid', '=', 'class.gradeid')
            ->leftJoin('major', 'major.majorid', '=', 'class.majorid')
            ->where('consult.need_follow_up', 1)
            ->where('consult.follow_up_assigned_teacherid', $teacher->teacherid)
            ->select(
                'consult.consultid',
                'consult.report_outcome',
                'consult.follow_up_notes',
                'consult.report_submitted_at',
                'student.name as student_name',
                'student.phonenumber as student_phone',
                'grade.gradename',
                'class.classname',
                'major.majorname',
                'counselor.name as counselor_name',
                'time_slots.date',
                'time_slots.start_time',
                'time_slots.end_time'
            )
            ->orderBy('consult.report_submitted_at', 'desc')
            ->get();
        echo view('all.header', compact('system'));
        echo view('all.menu', compact('system'));
        echo view('teacher.followups', compact('items'));
        echo view('all.footer');
    }

}
