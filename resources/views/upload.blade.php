<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>

<body>
    <div class="navbar bg-base-200">
        <a class="btn btn-ghost text-xl">Night Differential Compute Based on CSV</a>
    </div>

    <div class="hero min-h-screen bg-base-">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data"
                    class="flex items-center justify-center">
                    @csrf
                    <label for="csv_file"
                        class="mr-2 py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold cursor-pointer border border-gray-400 rounded-lg">
                        Upload CSV
                    </label>
                    <input type="file" id="csv_file" name="csv_file" class="hidden">
                    <button type="submit"
                        class="py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white font-bold ml-2 rounded-lg">
                        Submit
                    </button>
                </form>

            </div>
        </div>
    </div>
</body>

</html>
