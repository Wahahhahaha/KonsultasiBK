<?php foreach($data as $key) { ?>
    <div class="col-lg-3 col-md-6">
        <!-- Card -->
        <div class="card">

            <div class="card-body">
                <h4 class="card-title"><?= $key->name ?></h4>
                <div class="card-text mb-2">
                    <span class="badge bg-light-primary text-primary">
                        Class of <?= $key->gradename ?>
                    </span>
                </div>
                <div class="card-text mb-3">
                    <small class="text-muted font-weight-bold d-block mb-1">Schedule:</small>
                    <div class="schedule-list">
                        <?php if (count($key->schedules) > 0) { 
                            $weekdaySchedules = [];
                            $weekendSchedules = [];
                            
                            foreach ($key->schedules as $s) {
                                $day = strtolower($s->day_of_week);
                                $timeStr = substr($s->start_time, 0, 5) . ' - ' . substr($s->end_time, 0, 5);
                                
                                if (in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
                                    $weekdaySchedules[$timeStr][] = $day;
                                } else {
                                    $weekendSchedules[$timeStr][] = $day;
                                }
                            }

                            $dayOrder = ['monday'=>1, 'tuesday'=>2, 'wednesday'=>3, 'thursday'=>4, 'friday'=>5, 'saturday'=>6, 'sunday'=>7];

                            // Helper function to format rows
                            $processRows = function($schedules) use ($dayOrder) {
                                $rows = [];
                                foreach ($schedules as $time => $days) {
                                    usort($days, function($a, $b) use ($dayOrder) {
                                        return ($dayOrder[$a] ?? 0) - ($dayOrder[$b] ?? 0);
                                    });
                                    
                                    $label = '';
                                    $count = count($days);
                                    $isConsecutive = true;
                                    if ($count > 1) {
                                        for($i=0; $i<$count-1; $i++) {
                                            if (($dayOrder[$days[$i+1]] ?? 0) - ($dayOrder[$days[$i]] ?? 0) !== 1) {
                                                $isConsecutive = false;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if ($count >= 3 && $isConsecutive) {
                                        $label = ucfirst($days[0]) . ' - ' . ucfirst($days[$count-1]);
                                    } else {
                                        $label = implode(', ', array_map('ucfirst', $days));
                                    }
                                    
                                    $rows[] = ['label' => $label, 'time' => $time, 'first_day' => $days[0]];
                                }
                                
                                usort($rows, function($a, $b) use ($dayOrder) {
                                    return ($dayOrder[$a['first_day']] ?? 0) - ($dayOrder[$b['first_day']] ?? 0);
                                });
                                
                                return $rows;
                            };

                            $wkRows = $processRows($weekdaySchedules);
                            foreach ($wkRows as $r) {
                                echo '<div class="d-flex justify-content-between small">
                                        <span>'.$r['label'].'</span>
                                        <span class="text-dark font-weight-medium">'.$r['time'].'</span>
                                      </div>';
                            }

                            $weRows = $processRows($weekendSchedules);
                            foreach ($weRows as $r) {
                                echo '<div class="d-flex justify-content-between small">
                                        <span>'.$r['label'].'</span>
                                        <span class="text-dark font-weight-medium">'.$r['time'].'</span>
                                      </div>';
                            }

                        } else { ?>
                            <span class="text-danger small">No schedule available</span>
                        <?php } ?>
                    </div>
                </div>
                <?php if(session('level') == 3) { ?>
                    <?php if(isset($hasActiveBooking) && $hasActiveBooking) { ?>
                        <button class="btn btn-secondary w-100" disabled>Book Consult</button>
                        <div class="text-muted small">You have an active consultation</div>
                    <?php } else { ?>
                        <?php
                            $studentGradeIdLocal = isset($studentGradeId) ? (int)$studentGradeId : null;
                            $teacherGradeIdLocal = isset($key->gradeid) ? (int)$key->gradeid : null;
                            $canBookLocal = true;
                            if ($studentGradeIdLocal && (!$teacherGradeIdLocal || $teacherGradeIdLocal !== $studentGradeIdLocal)) {
                                $canBookLocal = false;
                            }
                        ?>
                        <?php if($canBookLocal) { ?>
                            <button class="btn btn-primary btn-book w-100" 
                                data-bs-toggle="modal" 
                                data-bs-target="#bookConsult"
                                data-teacherid="<?= $key->teacherid ?>"
                                data-teachername="<?= $key->name ?>">
                                Book Consult
                            </button>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>
