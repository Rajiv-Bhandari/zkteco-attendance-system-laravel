<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rats\Zkteco\Lib\ZKTeco;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function attendance()
    {
        set_time_limit(0); 
    
        $devices = DB::select("SELECT device_id, ipv4_addr, port, branch_id, device_title FROM ct_hr_attendance_device_master WHERE is_deleted_flag = 'n' AND device_id = 3");
     
        if (!$devices) 
        {
            return response()->json(['error' => 'No devices found'], 404);
        }
    
        $results = []; 
    
        foreach ($devices as $device) 
        {
            $inserted_count = 0;
    
            $device_title = $device->device_title;
            $device_ip = $device->ipv4_addr;
            $port = $device->port;
            $device_id = $device->device_id;
            $branch_id = $device->branch_id;
    
            $pingStatus = $this->pingDevice($device_ip);
    
            $connection_status = 0;
            $ping_status = 0;
            $message = '';
    
            if ($pingStatus) 
            {
                $zk_device_connection = new ZKTeco($device_ip, $port);
    
                if ($zk_device_connection->connect()) 
                {
                    $attendance_logs = $zk_device_connection->getAttendance();
                    echo "<pre>";
                    print_r($attendance_logs);
                    die();
                    foreach ($attendance_logs as $attendance) 
                    {
                        $uId = $attendance['uid'];
                        $attendance_id_no = $attendance['id'];
                        $log_date_time = $attendance['timestamp'];
                        $log_date_only = substr($log_date_time, 0, 10);
                        $verify_mode = ($attendance['state'] == 15) ? 'face' : 'fp';
                        $in_out_mode = $attendance['type'];
                        $created_by = 'system';
                        $created_date = now();
    
                        DB::table('device_log')->insert([
                            'attendance_id_no' => $attendance_id_no,
                            'log_date_time' => $log_date_time,
                            'log_date_only' => $log_date_only,
                            'verify_mode' => $verify_mode,
                            'in_out_mode' => $in_out_mode,
                            'device_id' => $device_id,
                            'branch_id' => $branch_id,
                            'created_by' => $created_by,
                            'created_date' => $created_date,
                        ]);

                        $inserted_count += 1;
                    }

                    // $zk_device_connection->clearAttendance();
                    $zk_device_connection->disconnect();
    
                    $results[] = [
                        'message' => 'Attendance logs inserted successfully!',
                        'device' => $device_title,
                        'inserted_count' => $inserted_count,
                    ];
                } 
                else 
                {
                    $ping_status = 1;
                    $message = 'Unable to connect to device!';
    
                    $this->insertDeviceLog($device_id, $device_ip, $ping_status, $branch_id, $connection_status, $message);
    
                    $results[] = [
                        'device' => $device_title,
                        'message' => 'Unable to connect to device! Log inserted.',
                    ];
                }
            } 
            else 
            {
                $message = "Unable to ping!";

                $this->insertDeviceLog($device_id, $device_ip, $ping_status, $branch_id, $connection_status, $message);
    
                $results[] = [
                    'device' => $device_title,
                    'message' => 'Ping failed! Log inserted.',
                ];
            }
        }
    
        return response()->json($results);
    }

    private function pingDevice($ipAddress) 
    {
		$pingResult = shell_exec("ping -c 3 " . $ipAddress);
	
		if (preg_match("/(0%|100%) packet loss/", $pingResult, $matches)) 
        {
			$packetLoss = $matches[1];
	
			if ($packetLoss === "0%") 
			{
				return true; 
			} 
			else 
			{
				return false;
			}
		} 
		else 
		{
			return false; 
		}
	}

    private function insertDeviceLog($device_id, $device_ip, $ping_status, $branch_id, $connection_status, $message)
    {
        DB::table('ct_hr_attendance_device_log')->insert([
            'device_id' => $device_id,
            'device_ip' => $device_ip,
            'branch_id' => $branch_id,
            'device_status' => $ping_status,
            'connection_status' => $connection_status,
            'total_logs' => 0,
            'log_date_time' => now(),
            'message' => $message,
        ]);
    }

    public function testDB()
    {
        try 
        {
            $device_log = DB::select("SELECT * FROM ct_hr_attendance_device_log LIMIT 20");
            return response()->json($device_log);
        } 
        catch (\Exception $e) 
        {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
