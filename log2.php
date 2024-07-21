<?php

class StudentLogger
{
    private static $studentsFile = 'studenti.json';

    private static function loadStudents()
    {
        return file_exists(self::$studentsFile) ? json_decode(file_get_contents(self::$studentsFile), true) : [];
    }

    private static function saveStudents($students)
    {
        file_put_contents(self::$studentsFile, json_encode($students, JSON_PRETTY_PRINT));
    }

    public static function logStudent($name)
    {
        $students = self::loadStudents();
        if (!isset($students[$name])) {
            $students[$name] = ['name' => $name, 'visits' => 0];
        }
        $students[$name]['visits'] += 1;
        self::saveStudents($students);
    }
}

class AttendanceLogger
{
    private $attendancesFile = 'prichody.json';

    private function checkIfLate($arrivalTime)
    {
        $arrivalHour = (new DateTime($arrivalTime))->format('H');
        return $arrivalHour >= 8;
    }

    private function loadAttendances()
    {
        return file_exists($this->attendancesFile) ? json_decode(file_get_contents($this->attendancesFile), true) : [];
    }

    private function saveAttendances($attendances)
    {
        file_put_contents($this->attendancesFile, json_encode($attendances, JSON_PRETTY_PRINT));
    }

    public function logAttendance($name, $isLate)
    {
        $attendances = $this->loadAttendances();
        $attendance = [
            'name' => $name,
            'time' => date('Y-m-d H:i:s'),
            'delay' => $isLate ? 'meskanie' : ''
        ];
        $attendances[] = $attendance;
        $this->saveAttendances($attendances);
    }

    public function updateDelays()
    {
        $attendances = $this->loadAttendances();
        foreach ($attendances as &$attendance) {
            if ($this->checkIfLate($attendance['time'])) {
                $attendance['delay'] = 'meskanie';
            }
        }
        $this->saveAttendances($attendances);
    }
}

function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

function appendToLogFile($message) {
    file_put_contents("log.txt", $message . "\n", FILE_APPEND);
}

function getLogs() {
    return file_exists("log.txt") ? file_get_contents("log.txt") : '';
}

function isRestrictedTime() {
    $hour = (int)date("H");
    return $hour >= 20 && $hour < 24;
}

function isLateArrival() {
    $hour = (int)date("H");
    return $hour >= 8;
}

$studentName = $_POST['meno'] ?? $_GET['meno'] ?? '';

if ($studentName) {
    if (isRestrictedTime()) {
        die("Cannot log arrivals between 20:00 and 24:00");
    }

    $isLate = isLateArrival();
    
    StudentLogger::logStudent($studentName);
    $attendanceLogger = new AttendanceLogger();
    $attendanceLogger->logAttendance($studentName, $isLate);

    echo "Log Contents:\n";
    echo getLogs();
    
    echo "\nStudent Data:\n";
    print_r(StudentLogger::loadStudents());
} else {
    echo "No student name provided.";
}
?>
