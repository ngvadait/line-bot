<!DOCTYPE html>
<html>
<body>

<h2>HTML Forms</h2>

<form action="{{ route('test.send.line') }}" method="POST">
    Content message:<br>
    <input type="text" name="message">
    <br>
    <input type="submit" value="Submit">
</form>

</body>
</html>