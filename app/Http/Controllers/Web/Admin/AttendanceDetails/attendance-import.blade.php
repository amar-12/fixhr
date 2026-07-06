<form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Import Attendance</button>
</form>
