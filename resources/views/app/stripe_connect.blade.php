<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bank Account</title>
    @vite('resources/css/app.css')
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    {{-- center of the screen using tailwind --}}
    <div class="flex justify-center ">
        <div class="bg-white shadow-md rounded p-4">
                <a href="javascript:closeWindow();" class=" font-bold py-2 px-4 rounded">
                    Account is connected
                </a>
        </div>
    </div>
    <script>
        // write a function to close the window after 3 seconds
        function closeWindow() {
            window.close();
        }
    </script>
</body>
</html>