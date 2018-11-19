<!DOCTYPE html>
<html>
<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<h2>HTML Forms</h2>

<form action="{{ route('test.send.line') }}" method="POST">
    @csrf
    Content message:<br>
    <input type="text" name="message">
    <br>
    <input type="submit" value="Submit">
</form>

</body>
</html>