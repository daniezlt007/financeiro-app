<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title inertia>Financeiro</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"><!-- 👈 ESSENCIAL -->
  @routes
  @vite(['resources/js/app.js'])
  @inertiaHead
</head>
<body class="bg-gray-50 text-gray-900">
  @inertia
</body>
</html>
