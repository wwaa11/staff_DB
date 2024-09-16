<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/tableToExcel.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <title>WEB</title>
</head>

<body>
    <div class="container">
        <button onclick="ex()">Export</button>
        <table class="table table-bordered" id="tabel">
            <tr>
                <td>ลำดับ</td>
                <td>Date</td>
                <td>VN</td>
                <td>HN</td>
                <td>ชื่อ - นามสกุล</td>
                <td>Clinic</td>
                <td>แพทย์</td>
            </tr>
            @foreach ($output as $key => $icd)
                <tr>
                    <td colspan="7">{{ $key }}</td>
                </tr>
                @foreach ($icd as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->VN }}</td>
                        <td>{{ $item->HN }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->Clinic_Name }}</td>
                        <td>{{ $item->Doctor_Name }}</td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    </div>
</body>
<script>
    function ex() {
        TableToExcel.convert(document.getElementById("tabel"));
    }
</script>

</html>
