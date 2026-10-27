<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: A4 landscape; }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            color: #102a43;
            font-family: DejaVu Sans, sans-serif;
        }

        .certificate {
            align-items: center;
            background-image: url('{{ $backgroundDataUri }}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            display: flex;
            height: 100%;
            justify-content: center;
            padding: 25mm 32mm;
            position: fixed;
            text-align: center;
            top: 0;
            left: 0;
            width: 100%;
        }

        .certificate::before {
            background: rgba(255, 255, 255, .72);
            content: '';
            inset: 20mm 25mm;
            position: absolute;
        }

        .content { position: relative; width: 100%; }
        .eyebrow { color: #b7791f; font-size: 14pt; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; }
        h1 { font-family: DejaVu Serif, serif; font-size: 34pt; margin: 13mm 0 6mm; }
        .lead { font-size: 14pt; margin: 0 0 5mm; }
        .student { color: #1a365d; font-family: DejaVu Serif, serif; font-size: 26pt; font-weight: bold; margin: 0 0 7mm; }
        .training { font-size: 18pt; font-weight: bold; margin: 0 auto 10mm; max-width: 210mm; }
        .date { font-size: 12pt; margin: 0; }
    </style>
</head>
<body>
    <main class="certificate">
        <section class="content">
            <p class="eyebrow">Sistema de Gestión de Capacitación Interna</p>
            <h1>Diploma de participación</h1>
            <p class="lead">Se otorga el presente diploma a</p>
            <p class="student">{{ $studentName }}</p>
            <p class="lead">por haber aprobado la capacitación</p>
            <p class="training">{{ $trainingTitle }}</p>
            <p class="date">Fecha de aprobación: {{ $approvalDate->translatedFormat('d \d\e F \d\e Y') }}</p>
        </section>
    </main>
</body>
</html>
