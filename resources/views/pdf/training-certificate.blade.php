<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
            size: {{ $pageWidth }}pt {{ $pageHeight }}pt;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: DejaVu Sans, sans-serif;
        }

        .certificate {
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
        }

        .template {
            display: block;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1;
        }

        .field {
            color: #0a3b7a;
            position: absolute;
            z-index: 2;
        }

        .student-name {
            font-size: 18pt;
            font-weight: bold;
            left: 10.5%;
            line-height: 1.1;
            text-align: center;
            top: 43.1%;
            width: 79%;
        }

        .training-title {
            font-size: 11pt;
            font-weight: bold;
            left: 8%;
            line-height: 1.25;
            text-align: center;
            top: 54.6%;
            width: 84%;
        }

        .approval-date {
            font-size: 11pt;
            left: 12.5%;
            top: 59.6%;
        }
    </style>
</head>
<body>
    <main class="certificate">
        <img class="template" src="{{ $backgroundDataUri }}" alt="Plantilla de diploma">
        <div class="field student-name">{{ $studentName }}</div>
        <div class="field training-title">{{ $trainingTitle }}</div>
        <div class="field approval-date">{{ $approvalDate->translatedFormat('d \d\e F \d\e Y') }}</div>
    </main>
</body>
</html>
