<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use League\Csv\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function showForm()
    {
        return view('upload');
    }



    public function processJsonData($jsonData)
    {
        // Decode JSON data
        $data = json_decode($jsonData, true);

        // Initialize an array to store the processed data
        $processedData = [];

        // Iterate through the data to group punch in and out events for each employee
        foreach ($data as $row) {
            $empId = $row['emp_id'];
            $dateTime = $row['date'] . ' ' . $row['time'];

            // Initialize the employee entry if not already present
            if (!isset($processedData[$empId])) {
                $processedData[$empId] = [
                    'emp_id' => $empId,
                    'punch_events' => [],
                ];
            }

            // Add punch event to the employee's data
            $processedData[$empId]['punch_events'][] = [
                'time' => $dateTime,
                'action' => $row['action']
            ];
        }

        // Remove incomplete entries
        $processedData = array_filter($processedData, function ($entry) {
            return !empty ($entry['punch_events']);
        });

        // Reindex the array
        $processedData = array_values($processedData);

        return $processedData;
    }


    public function createPairs($processedData)
    {
        // Initialize an array to store punch in and out pairs
        $pairs = [];

        // Iterate through the processed data for each employee
        foreach ($processedData as $employee) {
            $punchEvents = $employee['punch_events'];
            $punchCount = count($punchEvents);

            // Skip if there are less than two punch events (not enough for a pair)
            if ($punchCount < 2) {
                continue;
            }

            // Initialize variables to track punch in and punch out events
            $punchIn = null;
            $punchOut = null;

            // Iterate through the punch events for the employee
            for ($i = 0; $i < $punchCount; $i++) {
                $event = $punchEvents[$i];
                $action = $event['action'];

                // If punch in action, set punch in time
                if ($action == 'punch in') {
                    $punchIn = $event['time'];
                }

                // If punch out action and punch in time is set, create a pair
                if ($action == 'punch out' && $punchIn !== null) {
                    $punchOut = $event['time'];

                    // Get date from punch in time
                    $date = date('Y-m-d', strtotime($punchIn));

                    // Calculate hours worked and round to two decimal places
                    $punchInTimestamp = strtotime($punchIn);
                    $punchOutTimestamp = strtotime($punchOut);
                    $hoursWorked = round(($punchOutTimestamp - $punchInTimestamp) / (60 * 60), 2); // Convert seconds to hours and round to 2 decimal places

                    // Calculate overtime hours and round to two decimal places
                    $overtimeHours = round(max($hoursWorked - 9, 0), 2); // Subtract regular hours (9) and take maximum with 0, then round to 2 decimal places

                    // Calculate night differential hours
                    $nightDiffHours = 0;
                    $nightStart = strtotime(date('Y-m-d 22:00:00', $punchInTimestamp));
                    $nightEnd = strtotime(date('Y-m-d 06:00:00', strtotime('+1 day', $punchInTimestamp)));

                    // Check if punch in and punch out are both within the night time range
                    if ($punchInTimestamp >= $nightStart && $punchOutTimestamp <= $nightEnd) {
                        $nightDiffHours = $hoursWorked;
                    } else {
                        // Calculate night differential hours within the night time range
                        $nightDiffHours = max(min($punchOutTimestamp, $nightEnd) - max($punchInTimestamp, $nightStart), 0) / (60 * 60);
                    }

                    // Round night differential hours to two decimal places
                    $nightDiffHours = round($nightDiffHours, 2);

                    // Calculate night differential hours that occurred during overtime hours
                    $nightDiffOvertime = min($nightDiffHours, $overtimeHours);

                    $nightDiffHoursCorrect = $nightDiffHours - $nightDiffOvertime;

                    // Add punch in, punch out, hours worked, overtime hours, night differential hours, and night differential hours during overtime to pairs array
                    $pairs[] = [
                        'emp_id' => $employee['emp_id'],
                        'date' => $date,
                        'punch_in' => $punchIn,
                        'punch_out' => $punchOut,
                        'hours_worked' => $hoursWorked,
                        'overtime_hours' => $overtimeHours,
                        'night_diff_hours' => $nightDiffHoursCorrect,
                        'night_diff_overtime' => $nightDiffOvertime
                    ];

                    // Reset punch in time
                    $punchIn = null;
                }
            }
        }

        return $pairs;
    }







    public function upload(Request $request)
    {
        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $fileName = 'converted_data.json';

            // Read CSV file
            $csvData = array_map('str_getcsv', file($file));

            // Define keys for the columns
            $keys = ['emp_id', 'date', 'time', 'action'];

            // Extract only the required columns (emp_id, date, time, action) with keys
            $filteredData = array_map(function ($row) use ($keys) {
                // Split date and time
                $dateTime = explode(' ', $row[1]);
                $date = isset ($dateTime[0]) ? $dateTime[0] : '';
                $time = isset ($dateTime[1]) ? $dateTime[1] : '';

                // Determine action label based on value
                $actionLabel = '';
                if ($row[3] == '0') {
                    $actionLabel = 'punch in';
                } elseif ($row[3] == '1') {
                    $actionLabel = 'punch out';
                }

                // Combine keys with respective values
                $entry = array_combine($keys, [$row[0], $date, $time, $actionLabel]);

                // Add a new entry for "time" based on the extracted time component
                $entry['time'] = $time;

                return $entry;
            }, $csvData);

            // Sort the data by emp_id
            usort($filteredData, function ($a, $b) {
                return $a['emp_id'] <=> $b['emp_id'];
            });

            // Convert to JSON
            $jsonData = json_encode($filteredData, JSON_PRETTY_PRINT);

            // Process JSON data to identify punch in and out sequence for each employee
            $processedData = $this->processJsonData($jsonData);

            // Create punch in and punch out pairs
            $pairs = $this->createPairs($processedData);

            // Convert pairs to JSON
            $pairsJson = json_encode($pairs, JSON_PRETTY_PRINT);

            // Save JSON to project folder
            $filePath = storage_path('app/public/' . $fileName);
            file_put_contents($filePath, $pairsJson);

            // Return the file path for later access
            return redirect()->route('display', ['filePath' => $filePath]);
        }

        return back()->with('error', 'Please upload a CSV file.');
    }

}
