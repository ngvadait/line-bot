<!DOCTYPE html>
<html>
<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<h2>HTML Forms</h2>

<form action="{{ route('test.rich.menu') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="size">RichMenuSize</label>
    <select name="size">
        <option value="full">getFull: 1686-2500</option>
        <option value="half">getHalf: 843-2500</option>
    </select>
    <br>

    <label for="display">Display</label>
    <input type="checkbox" name="checked" value="yes">
    <br>

    <label for="name">Name</label>
    <input type="text" name="name">
    <br>

    <label for="chatbar">Chat Bar</label>
    <input type="text" name="chatbar">
    <br>

    <br>
    <input type="submit" value="Submit">
</form>

<br>
<br>

<a href="{{ route('get.list.rich.menu') }}">Get list richmenu</a>

</body>
</html>