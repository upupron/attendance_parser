<!-- resources/views/display.blade.php -->
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>

<body>
    <div class="navbar bg-base-100">
        <a class="btn btn-ghost text-xl">Night Differential Compute Based on CSV</a>
    </div>

    <div class="table-wrp block max-h-96 text-center">
        <table class="table-auto min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10 text">
                <tr>
                    <th class=" px-6 py-3  text-blue-700">ID</th>
                    <th class=" px-6 py-3  text-blue-700">Date</th>
                    <th class=" px-6 py-3 text-blue-700">Punch In</th>
                    <th class=" px-6 py-3 text-blue-700">Punch Out</th>
                    <th class=" px-6 py-3 text-blue-700">Hours Worked</th>
                    <th class=" px-6 py-3 text-green-700">OT</th>
                    <th class=" px-6 py-3 text-red-700">ND</th>
                    <th class=" px-6 py-3 text-blue-700">ND/OT</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if (isset($jsonData) && !empty($jsonData))
                    @foreach ($jsonData as $entry)
                        <tr>
                            <td>{{ $entry['emp_id'] }}</td>
                            {{-- <td>
                                </?php $employeeID = (int) $entry['emp_id'];
                                $employee = \DB::table('tbl_dtr_attendance')->where('idno', $employeeID)->first();
                                $employeeName = $employee ? $employee->employee : 'Unknown Employee';
                                echo $employeeName;
                                ?>
                            </td> --}}
                            <td>{{ $entry['date'] }}</td>
                            <td>{{ $entry['punch_in'] }}</td>
                            <td>{{ $entry['punch_out'] }}</td>
                            <td>{{ $entry['hours_worked'] }}</td>
                            <td class="text-green-700">{{ $entry['overtime_hours'] }}</td>
                            <td class="text-red-700">{{ $entry['night_diff_hours'] }}</td>
                            <td>{{ $entry['night_diff_overtime'] }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8">No data available.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>


</body>

</html>
